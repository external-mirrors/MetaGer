<?php

namespace App\Authentication;

use App\Console\Commands\SettleKeyDischarges;
use App\Events\KeyChanged;
use App\Support\RedisFailover;
use Arr;
use Cache;
use Http;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Predis\PredisException;
use Request;

class KeyUser implements Authenticatable
{
    /**
     * How long this class is willing to wait for the keyserver.
     *
     * Both calls below run while a user is waiting for the start page or a
     * result page, and both used to carry Laravel's defaults — 10s to connect,
     * 30s to read (Illuminate\Http\Client\PendingRequest::__construct) — with
     * no catch. Every other caller of this keyserver already sets a timeout of
     * its own (KeyIssuer, LoginCodeIssuer, ChargeOrderIssuer, KeyPrice and the
     * rest, 2-10s); these two were simply missed, and they are the two on the
     * hot paths.
     *
     * Two seconds because that is roughly the search's own tolerance for a
     * single upstream (EngineOrchestrator::WAIT_SECONDS is 6 for *all* the
     * engines together), and because the answer is cached for ten seconds
     * afterwards — a slow keyserver is paid for once per key per ten seconds,
     * not once per request.
     *
     * The connect timeout matters more than the read timeout here and is the
     * one the default got most wrong: a keyserver pod that is being rescheduled
     * does not answer slowly, it does not answer at all, and ten seconds of
     * that on the start page is indistinguishable from the site being down.
     */
    private const TIMEOUT_SECONDS = 2;
    private const CONNECT_TIMEOUT_SECONDS = 1;

    /**
     * How long the last answer the keyserver gave stays usable as a fallback.
     *
     * Separate from the ten-second hot cache, and read only when the keyserver
     * cannot be reached at all. Without it an unreachable keyserver means
     * getKeyData() returns null, which reads as "no charge" everywhere
     * downstream: the account is rendered logged out, every paid engine is
     * disabled, and the search redirects to the start page. A user whose key is
     * perfectly good is told it is not, because something they do not own is
     * being restarted.
     *
     * An hour, not a day: while this is being used nothing is being charged
     * either (the discharge POST is failing for the same reason), so the window
     * is also how long a key can overspend. An hour covers every planned drain
     * and failover by a wide margin and bounds the giveaway to something
     * comparable to a single top-up.
     */
    private const STALE_FALLBACK_SECONDS = 3600;

    /**
     * How long a claim stands while its discharge waits to be settled.
     *
     * A claim normally lives thirty seconds, which is how long a search takes
     * to decide what it owes. {@see makePayment()} no longer pays in the
     * foreground — it queues the charge for `keys:settle-discharges`, which
     * runs once a minute and gives an unreachable keyserver five runs before
     * giving up. So the reservation has to outlive the search by that much:
     * while a charge is queued the tokens are gone, and a claim that expired
     * first would put them back on the key for anyone to spend a second time.
     *
     * Ten minutes covers five attempts a minute apart with room to spare, and
     * bounds the other direction too — a claim whose settler died is at worst
     * ten minutes of a key looking poorer than it is.
     */
    public const SETTLEMENT_WINDOW_SECONDS = 600;

    public string $id;

    /**
     * The key associated with the user.
     *
     * @var string
     */
    public string $key;

    public array|null $key_data = null;
    /**
     * Existing claims for upcoming charges on the key
     * @var array|null
     */
    public array|null $claims = null;

    public bool $temporary = false;

    private KeyState|null $state = null;

    /**
     * The keyserver URL.
     *
     * @var string
     */
    private string $keyserver;

    /**
     * Create a new KeyUser instance.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->id = uniqid('key_user', true);
        $this->key = $key;

        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    public function getAuthIdentifierName(): string
    {
        return 'key';
    }

    public function getAuthIdentifier(): string
    {
        return $this->key;
    }

    public function getAuthPasswordName(): string
    {
        return 'key';
    }

    public function getAuthPassword(): string
    {
        return $this->key;
    }

    public function getRememberToken(): string
    {
        return ''; // KeyUser does not use remember tokens
    }

    public function setRememberToken($value): void
    {
        // KeyUser does not use remember tokens
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function getKeyState(): KeyState
    {
        if ($this->state === null) {
            if ($this->temporary) {
                $this->state = match (Request::header("tokenauthorization")) {
                    "empty" => KeyState::EMPTY ,
                    "low" => KeyState::LOW,
                    "full" => KeyState::FULL,
                    default => KeyState::NO_KEY, // Default to NO_KEY if no valid state is provided
                };
            } else {
                $key_data = $this->getKeyData();
                $current_charge = Arr::get($key_data, "charge", null);
                $this->state = match (true) {
                    $current_charge > 30 => KeyState::FULL,
                    $current_charge > 3 && $current_charge <= 30 => KeyState::LOW,
                    $current_charge <= 3 => KeyState::EMPTY ,
                    default => KeyState::NO_KEY,
                };

            }
        }
        return $this->state;
    }

    /**
     * The key's current token balance, or null when it cannot be determined
     * (a temporary webextension user, or the keyserver did not answer).
     *
     * Reads the same 10s-cached {@see getKeyData()} the guard already touched
     * this request, so calling it from a blade costs nothing extra.
     */
    public function getCharge(): ?float
    {
        if ($this->temporary) {
            return null;
        }
        $charge = Arr::get($this->getKeyData(), "charge");

        return $charge === null ? null : (float) $charge;
    }

    /**
     * Wirft den gemerkten Kontostand weg, damit der nächste Zugriff wieder
     * beim Keyserver nachfragt.
     *
     * Für den einen Moment, in dem zehn Sekunden Cache falsch sind: direkt
     * nach einer Gutschrift. Der Stand steht dann nicht nur da, wo man ihn
     * gerade geändert hat, sondern auf jeder Seite gleichzeitig — im Kontochip
     * oben (parts/account-pill), in der Seitenleiste (parts/sidebar) und groß
     * in der Kachel. „Aufladen abgeschlossen" über „0 Token" ist die eine
     * Kombination, die dieser Vorgang nicht zeigen darf.
     *
     * Verworfen wird beides, und keins von beidem reicht allein: nur
     * $this->key_data zu leeren liest denselben veralteten Eintrag wieder ein,
     * nur den Cache zu leeren geht an dem Stand vorbei, den der Guard schon in
     * dieses Objekt gelegt hat.
     *
     * Der abgeleitete {@see getKeyState()} fällt mit weg — er wäre sonst der
     * letzte Rest des alten Standes, und zwar ausgerechnet der, der die Farbe
     * des Chips bestimmt.
     *
     * Geleert, nicht geladen: die Anfrage passiert beim nächsten Lesen, also
     * gar nicht, wenn niemand mehr fragt.
     *
     * Der Rückfall-Eintrag (`…:last`, {@see rememberedKeyDataKey}) wird
     * absichtlich *nicht* mitgelöscht. Er wird nur gelesen, wenn der Keyserver
     * gar nicht antwortet, und dann ist der Stand von vorhin die beste Antwort,
     * die es gibt — besser jedenfalls als „kein Schlüssel". Antwortet der
     * Keyserver, wird er ohnehin sofort überschrieben.
     */
    public function refresh(): void
    {
        Cache::forget(self::keyDataCacheKey($this->key));
        $this->key_data = null;
        $this->state = null;
    }

    /**
     * The last six characters of the key — enough for a user to tell two of
     * their own keys apart, and the input {@see KeyIdenticon} derives the
     * account's mark from, without ever putting the full secret on a page that
     * is not /keys or the settings page.
     *
     * The keyserver canonicalises non-UUID keys (a legacy string or a short code
     * is MD5-folded into a UUID), and getKeyData() writes that canonical form
     * back onto $this->key. So this forces getKeyData() first: a UUID key is
     * already canonical and this changes nothing, but for a legacy key it means
     * the fingerprint is the *account's* fingerprint — the same six characters
     * the /keys dashboard shows — and not "whatever was in the cookie", which
     * would differ between a page that has talked to the keyserver and one that
     * has not.
     *
     * Null is the answer whenever we cannot name an account we would still be
     * naming next request: an unreachable keyserver, a non-UUID key, and — the
     * one that matters — a temporary user. See below.
     */
    public function getKeyFingerprint(): ?string
    {
        // A temporary user is the webextension, and $this->key is then the
        // *anonymous token key* it sent as a header, not the user's key. That
        // token is short-lived by design: KeyAuthGuard mints a KeyUser from it,
        // the extension rotates it on expiry or when its charge is spent, and
        // the whole point of the arrangement is that we never learn the real
        // key behind it.
        //
        // So there is no account here we can name. Returning six characters of
        // the token would look like an identity and behave like a session id —
        // the user would watch their "account" change several times a day, and
        // the mark drawn from it would change with it. The extension is the
        // only party that can answer this question, and it answers it in its
        // own UI.
        if ($this->temporary) {
            return null;
        }

        // The canonical key off the key data, not $this->key: getKeyData() only
        // writes the canonical form back onto $this->key on a cache *miss*, so
        // reading $this->key would still flip between the raw cookie and the
        // UUID across requests. The cached payload always carries the canonical
        // "key", so this is stable whether the data came from the cache or the
        // keyserver.
        $canonical = Arr::get($this->getKeyData(), "key", $this->key);

        if (!is_string($canonical) || !\Illuminate\Support\Str::isUuid($canonical)) {
            return null;
        }

        return substr($canonical, -6);
    }

    /**
     * Der Schlüssel in der Form, unter der der Keyserver ihn führt.
     *
     * Für eine UUID ist das der Schlüssel selbst. Für einen alten
     * Nicht-UUID-Schlüssel ist es die UUID, in die `Key.GET_KEY` ihn faltet —
     * und die ist es, die auf das Konto gehört: sie ist das, was auf einem
     * zweiten Gerät eingegeben funktioniert.
     *
     * Aus den Schlüsseldaten und nicht aus `$this->key`, aus demselben Grund
     * wie in {@see getKeyFingerprint()}: `$this->key` wird nur bei einem
     * Cache-*Miss* zurückgeschrieben und schwankte sonst zwischen Cookie-Wert
     * und kanonischer Form.
     */
    public function getCanonicalKey(): ?string
    {
        if ($this->temporary) {
            return null;
        }

        $canonical = Arr::get($this->getKeyData(), "key");

        return is_string($canonical) ? $canonical : null;
    }

    /**
     * Wann die letzte Ladung dieses Schlüssels verfällt, oder null, wenn es
     * dazu nichts zu sagen gibt.
     *
     * Der Keyserver rechnet das aus (`Key.get_expiration_date()`) und es ist
     * mehr als „die späteste Bestellung“: ein leerer Schlüssel hängt an einem
     * festen Anker statt an *jetzt*, sonst schöbe jede Berührung sein Verfallen
     * weiter hinaus, und eine laufende Mitgliedschaft hält ihn ohnehin am
     * Leben. Deshalb wird gefragt und nicht selbst gerechnet.
     */
    public function getExpiration(): ?\Illuminate\Support\Carbon
    {
        $expiration = Arr::get($this->getKeyData(), "expiration");

        if (!is_string($expiration) || $expiration === "") {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($expiration);
        } catch (\Throwable $e) {
            // Ein Datum, das wir nicht lesen können, ist kein Grund, die
            // Kontoseite scheitern zu lassen — sie zeigt dann eben keines.
            return null;
        }
    }

    /**
     * Die einzelnen Ladungen, jede mit ihrem eigenen Verfallsdatum.
     *
     * Der Keyserver liefert sie nur an einen authentifizierten Aufrufer, und
     * das ist diese Anwendung immer ({@see getKeyData()}). Sie sind der Grund,
     * warum das Konto überhaupt mehr sagen kann als ein einzelnes Datum: wer
     * dreimal aufgeladen hat, hat drei Töpfe, die nacheinander ablaufen.
     *
     * Aufsteigend nach Verfallsdatum, weil das die Reihenfolge ist, in der sie
     * verbraucht werden — und damit die, in der eine Liste sie lesbar macht.
     *
     * @return list<array{amount: float, expiration: \Illuminate\Support\Carbon|null}>
     */
    public function getChargeOrders(): array
    {
        $orders = Arr::get($this->getKeyData(), "charge_orders");

        if (!is_array($orders)) {
            return [];
        }

        $parsed = [];
        foreach ($orders as $order) {
            if (!is_array($order) || !isset($order["amount"]) || !is_numeric($order["amount"])) {
                continue;
            }

            $expiration = Arr::get($order, "expiration");
            try {
                $expiration = is_string($expiration) && $expiration !== ""
                    ? \Illuminate\Support\Carbon::parse($expiration)
                    : null;
            } catch (\Throwable $e) {
                $expiration = null;
            }

            $parsed[] = [
                "amount" => (float) $order["amount"],
                "expiration" => $expiration,
            ];
        }

        usort($parsed, static function (array $a, array $b): int {
            if ($a["expiration"] === null || $b["expiration"] === null) {
                return $a["expiration"] === $b["expiration"] ? 0 : ($a["expiration"] === null ? 1 : -1);
            }
            return $a["expiration"] <=> $b["expiration"];
        });

        return $parsed;
    }

    /**
     * Ob an diesem Schlüssel eine laufende Mitgliedschaft im SUMA-EV hängt.
     *
     * Mitglieder suchen ohne weitere Kosten, und die Kontoseite bietet ihnen
     * deshalb kein Token-Paket an. Das Feld heißt in der Antwort des
     * Keyservers `key_config` — die API-Dokumentation nannte es jahrelang
     * `config`, was schlicht falsch war.
     *
     * Die Regel („Enddatum plus ein Monat liegt in der Zukunft“) ist die des
     * Keyservers, `KeyConfig.isMember()`, hier nachgebildet: er liefert das
     * Datum, nicht das Urteil.
     */
    public function isMember(): bool
    {
        $end = Arr::get($this->getKeyData(), "key_config.membershipEndDate");

        if (!is_string($end) || $end === "") {
            return false;
        }

        try {
            return \Illuminate\Support\Carbon::parse($end)->addMonth()->isFuture();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Authorize the user for a specific token cost. The amount will be claimed on the key for
     * this process for the specified duration and is not available for other processes
     * during that time.
     *
     * @param float $token_cost
     * @param int $claim_duration_seconds
     * @return bool
     */
    public function authorize(float $token_cost, $claim_duration_seconds = 30): bool
    {
        // Read once per request. Other processes can add claims while this one
        // runs, but re-reading would only narrow that window, not close it —
        // there is no lock here by design.
        if ($this->claims === null) {
            // Retried for the same reason the write below is: this runs from
            // AuthenticationValidation on every authenticated search, before
            // the search, so an unguarded failover here is a key holder's
            // search answered with an error page during a planned drain.
            //
            // Degrades to "no other claims": the charge is then judged on the
            // keyserver's number alone, which is the pre-claims behaviour and
            // errs towards letting the search through. Erring the other way
            // would refuse a search over bookkeeping we could not read.
            try {
                $this->claims = RedisFailover::retry(
                    fn() => $this->claimsConnection()->hgetall($this->claimsKey()),
                    connection: config("cache.stores.redis.connection")
                );
            } catch (PredisException $e) {
                Log::warning("Could not read the key claims: " . $e->getMessage());
                $this->claims = [];
            }
        }

        $key_data = $this->getKeyData();
        $current_charge = Arr::get($key_data, "charge", 0);

        foreach ($this->claims as $id => $amount) {
            if ($id !== $this->id)
                $current_charge -= max($amount, 0); // Ensure we don't subtract negative amounts
        }
        $current_charge -= $token_cost;

        if ($claim_duration_seconds > 0) {
            $new_claim_amount = Arr::get($this->claims, $this->id, 0) + $token_cost;
            $this->claims[$this->id] = $new_claim_amount;

            // The claim and the deadline it expires on are one statement about
            // this request, and nothing between them depends on the other.
            //
            // Retried across a Sentinel failover: this runs from
            // AuthenticationValidation on every authenticated search, so an
            // unguarded `-READONLY` here is a key holder's search answered
            // with an error page during a node drain. The claim is this
            // request's own field ($this->id is unique to this KeyUser), so a
            // retry after a lost reply can only re-apply our own increment —
            // and the field expires with the claim regardless.
            RedisFailover::retry(fn() => $this->claimsConnection()->pipeline(function ($pipe) use ($token_cost, $claim_duration_seconds) {
                $pipe->hincrbyfloat($this->claimsKey(), $this->id, $token_cost);
                $pipe->hexpireat($this->claimsKey(), now()->addSeconds($claim_duration_seconds)->timestamp, [$this->id]);
            }));
        }
        return $current_charge >= 0;
    }

    /**
     * Charge the key for what a search used.
     *
     * The charge is written down, not made: see {@see queueDischarge()}. What
     * this method still does in the foreground is decide whether the key can
     * cover the amount — topping the claim up if the search turned out to cost
     * more than was authorized for it — and that decision has not moved.
     */
    public function makePayment(float $token_cost): bool
    {
        // Our own claim, and only ever ours: $this->id is unique to this
        // KeyUser, so no other process writes this field and $this->claims
        // tracks every change we make to it. It used to be read back from Redis
        // on every payment — once per paid engine — which asked the network for
        // a number we had just written to it.
        $claim_amount = Arr::get($this->claims ?? [], $this->id, 0);

        if ($claim_amount > 0 && $claim_amount < $token_cost) {
            if ($this->authorize($token_cost - $claim_amount, 30)) {
                // If we have a claim that is less than the token cost, we cannot proceed
                $claim_amount = $token_cost;
            } else {
                return false;
            }
        }

        $token_cost = max($token_cost, 0); // Ensure we don't process negative costs
        if (abs($token_cost - 0) < PHP_FLOAT_EPSILON)
            return true;

        return $this->queueDischarge($token_cost);
    }

    /**
     * Write the charge down instead of paying it now.
     *
     * This used to be `POST /key/<key>/discharge`, made while the user waited.
     * It was the last thing on either hot path that needed the keyserver to
     * answer, and behind the keyserver sits the only Postgres those paths
     * depended on at all — so a CNPG switchover, a keyserver rollout or a
     * drained node reached the user here and nowhere else. It was also where
     * the fee was lost whenever that happened, because a charge that could not
     * be made had nowhere to go.
     *
     * Now it goes on a Redis list and `keys:settle-discharges` pays it, the
     * same shape App\QueryLogger uses for search logs. What the foreground
     * keeps is one `lpush`, on a connection the request has open already.
     *
     * The claim is what makes this safe, and it is why the claim is *not*
     * released here any more: the tokens have left the key but nothing has
     * recorded that yet, so the reservation has to stand until the settler
     * confirms the charge. Its expiry is pushed out from the thirty seconds a
     * search needs to {@see SETTLEMENT_WINDOW_SECONDS} in the same pipeline —
     * one round trip, and no window in which the queue holds a charge the
     * claim no longer covers.
     *
     * `false` only when Redis will not take it. That is not the old
     * `false` — it no longer means "the keyserver refused", because nobody has
     * asked it yet. Its one caller that reads the result,
     * App\Http\Middleware\AuthenticationValidation, gated the search on
     * `authorize() && makePayment()`, and authorize() is the half that decides
     * whether the key can afford this; the other half now only reports whether
     * the note was written down.
     */
    private function queueDischarge(float $token_cost): bool
    {
        $discharge = json_encode([
            // Unique to this charge, and the reason a retry cannot cost the
            // user twice. RedisFailover::retry re-issues the pipeline below
            // when a Sentinel promotion loses the reply, and `lpush` is not
            // idempotent: a lost reply for a write that did land queues the
            // same charge twice. The settler refuses to pay an id it has
            // already paid, which turns that into a duplicate entry it throws
            // away instead of a second discharge -- the same reasoning as the
            // note on hincrbyfloat in
            // App\Models\Authorization\SuggestionDebtAuthorization, with the
            // opposite conclusion, because here not retrying is the worse
            // failure: a -READONLY during a drain would drop every charge.
            "id" => (string) \Illuminate\Support\Str::uuid(),
            "key" => $this->key,
            "amount" => $token_cost,
            "claim" => $this->id,
            "attempts" => 0,
            "queued_at" => now()->toIso8601String(),
            // Carried, not dropped: the keyserver rate-limits per client IP,
            // and every MetaGer request otherwise reaches it as the same
            // Bearer token. The settler is a background process with no
            // request of its own, so the address has to travel with the
            // charge. See tests/Feature/Search/KeyUserClientIpForwardingTest.
            "ip" => Request::ip(),
        ]);

        try {
            RedisFailover::retry(
                fn() => $this->claimsConnection()->pipeline(function ($pipe) use ($discharge) {
                    $pipe->lpush(SettleKeyDischarges::REDIS_KEY, $discharge);
                    $pipe->hexpireat(
                        $this->claimsKey(),
                        now()->addSeconds(self::SETTLEMENT_WINDOW_SECONDS)->timestamp,
                        [$this->id]
                    );
                }),
                connection: config("cache.stores.redis.connection")
            );
        } catch (PredisException $e) {
            // Nothing was charged and nothing was written down, so this says
            // so. It takes a Valkey that is unreachable even to a retry, which
            // is the same condition that has already failed the search itself.
            Log::warning("Could not queue a keyserver discharge: " . $e->getMessage());

            return false;
        }

        $this->spendLocally($token_cost);

        return true;
    }

    /**
     * Take the tokens off the balance this request will render.
     *
     * The settler writes the keyserver's own number back into the same cache
     * entry, but that is up to a minute away, and in the meantime every page
     * this user loads would show a balance that has not moved — on the account
     * pill, in the sidebar and on the account tile at once. The old
     * foreground discharge got this for free, because the keyserver answered
     * with the new charge.
     *
     * Deliberately not written to the hour-long fallback entry
     * ({@see rememberKeyData}): that one exists to answer when the keyserver
     * cannot, and it should hold something the keyserver actually said.
     *
     * It has to outlive the ten seconds getKeyData() caches a keyserver answer
     * for, though, or the balance goes back *up* before it settles: the entry
     * expires, the next lookup asks the keyserver, and the keyserver has not
     * been told about this charge yet and truthfully answers the old number.
     * The account pill would count down, wait, count back up and only then
     * settle. So the estimate stands for as long as the charge can be in the
     * queue ({@see SETTLEMENT_WINDOW_SECONDS}), and the settler overwrites it
     * with the keyserver's own figure as soon as it has one — which is a minute
     * at most, and the reason this window is a ceiling rather than a duration.
     *
     * If the charge is dropped after all, this stands until it expires and the
     * key looks poorer than it is for that long. That is the safe direction to
     * be wrong in, and the old foreground discharge pinned its answer for
     * thirty minutes.
     *
     * Estimating low, never high: `max(0, …)`, and only when there is a charge
     * on the instance to estimate from.
     */
    private function spendLocally(float $token_cost): void
    {
        if (!is_array($this->key_data) || !isset($this->key_data["charge"])) {
            return;
        }

        $this->key_data["charge"] = max(0, (float) $this->key_data["charge"] - $token_cost);
        $this->state = null;

        Cache::put(
            self::keyDataCacheKey($this->key),
            $this->key_data,
            now()->addSeconds(self::SETTLEMENT_WINDOW_SECONDS)
        );
    }

    /**
     * Where this key's claims live. One hash per key, one field per request.
     */
    private function claimsKey(): string
    {
        return self::claimsCacheKey($this->key);
    }

    /**
     * The three cache keys a key's state lives under, named once.
     *
     * Static because App\Console\Commands\SettleKeyDischarges writes two of
     * them and clears a claim on the third, from a process that has no KeyUser
     * and no request. It settles the charge a search queued, so it is writing
     * the same entries the next request will read; a second spelling of these
     * strings is a bug that shows up as a balance that will not update.
     */
    public static function claimsCacheKey(string $key): string
    {
        return "keyserver:claims:" . $key;
    }

    public static function keyDataCacheKey(string $key): string
    {
        return "keyserver:key:" . $key;
    }

    public static function rememberedKeyDataCacheKey(string $key): string
    {
        return self::keyDataCacheKey($key) . ":last";
    }

    /**
     * Claims live on the cache connection, not the default one.
     *
     * Not memoized on the instance: the connection is a live socket and this
     * object is reachable from things that get serialized. The manager already
     * hands back the same connection each time.
     */
    private function claimsConnection(): mixed
    {
        return Redis::connection(config("cache.stores.redis.connection"));
    }

    private function getKeyData(): array|null
    {
        if ($this->key_data !== null) {
            return $this->key_data;
        }
        if (!$key_response = Cache::get(self::keyDataCacheKey($this->key))) {
            // Fetch key data from the keyserver
            try {
                $key_response = Http::timeout(self::TIMEOUT_SECONDS)
                    ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                    ->withHeaders([
                        "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
                        "X-Forwarded-For" => Request::ip(),
                    ])->get($this->keyserver . "/key/" . urlencode($this->key));
            } catch (ConnectionException $e) {
                // Unreachable, refused, or slower than the timeout above. Not
                // an error the visitor can do anything with, and not a reason
                // to tell them their key is gone — fall back to the last answer
                // this keyserver gave for it.
                Log::warning("keyserver key lookup unreachable: " . $e->getMessage());

                return $this->rememberedKeyData();
            }

            if ($key_response->successful()) {
                $key_response = $key_response->json();
                $this->key = Arr::get($key_response, "key", $this->key); // Update key if it has changed
                $current_charge = Arr::get($key_response, "charge");
                if ($current_charge === null) {
                    return null;
                }
                Cache::put(self::keyDataCacheKey($this->key), $key_response, now()->addSeconds(10)); // Cache for 10 seconds
                $this->rememberKeyData($key_response);
                KeyChanged::dispatch($this->key, 0, $current_charge);
                $this->key_data = $key_response; // Store the key data for future use
                return $key_response;
            } else {
                // An answer, just not a usable one — a 404 for a key that does
                // not exist, a 401 for a bad access token. Deliberately *not*
                // falling back: the keyserver is reachable and has told us
                // something about this key, so a remembered charge would be
                // contradicting it rather than covering for it.
                return null;
            }
        } else {
            $this->key_data = $key_response; // Store the key data for future use
            return $this->key_data;
        }
    }

    /**
     * Where the fallback lives.
     *
     * A second entry rather than a longer TTL on `keyserver:key:<key>`: that
     * one's ten seconds are what make the charge on the page current, and the
     * whole suite writes it directly to stand in for the keyserver
     * (tests/Concerns/FakesSearchEngines and friends). Widening it would change
     * what "cached charge" means everywhere to fix something that only happens
     * when the network does not work.
     */
    private function rememberedKeyDataKey(): string
    {
        return self::rememberedKeyDataCacheKey($this->key);
    }

    private function rememberKeyData(array $key_response): void
    {
        Cache::put(
            $this->rememberedKeyDataKey(),
            $key_response,
            now()->addSeconds(self::STALE_FALLBACK_SECONDS)
        );
    }

    /**
     * The last answer the keyserver gave for this key, or null if there is
     * none — a first visit during an outage genuinely cannot be answered.
     */
    private function rememberedKeyData(): array|null
    {
        $remembered = Cache::get($this->rememberedKeyDataKey());

        if (!is_array($remembered)) {
            return null;
        }

        // Onto the instance, but pointedly not into `keyserver:key:<key>`: this
        // request may use it, the next one should try the keyserver again
        // rather than find a stale entry someone else left behind.
        $this->key_data = $remembered;

        return $remembered;
    }
}
