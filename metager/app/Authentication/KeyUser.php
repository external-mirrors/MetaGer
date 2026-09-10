<?php

namespace App\Authentication;

use App\Events\KeyChanged;
use App\PrometheusExporter;
use App\Support\RedisFailover;
use Arr;
use Cache;
use Http;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
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
        Cache::forget("keyserver:key:" . $this->key);
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
            $this->claims = $this->claimsConnection()->hgetall($this->claimsKey());
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
        try {
            $key_response = Http::timeout(self::TIMEOUT_SECONDS)
                ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                ->withHeaders([
                    "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
                    "Content-Type" => "application/json",
                    "X-Forwarded-For" => Request::ip(),
                ])->post($this->keyserver . "/key/" . urlencode($this->key) . "/discharge", [
                        "amount" => $token_cost,
                    ]);
        } catch (ConnectionException $e) {
            // The charge did not happen, so this says so. `false` is safe on
            // both callers: MetaGerSearch discharges once for the whole search
            // *after* it has already been answered and ignores the result, and
            // AuthenticationValidation only reaches this for a non-zero
            // suggestion debt (makePayment(0) returns above without a request).
            // So an unreachable keyserver costs the operator the fee for that
            // search, not the user their page.
            //
            // Losing the fee is the part that wants fixing, and the fix is not
            // "return true" — it is to stop making this call in the foreground
            // at all, queue the discharge the way QueryLogger queues a search
            // log and settle it from a worker. Until then this is at least
            // honest about what happened.
            Log::warning("keyserver discharge unreachable: " . $e->getMessage());

            return false;
        }

        if ($key_response->successful()) {
            $key_response = $key_response->json();
            $current_charge = Arr::get($key_response, "charge");
            if ($current_charge === null) {
                return false;
            }
            /** @var array $uniMainzKeys */
            $uniMainzKeys = config('metager.metager.keys.uni_mainz', []);
            if (in_array($this->key, $uniMainzKeys)) {
                PrometheusExporter::UpdateKeyStatus(key: $this->key, tokens: $current_charge, owner: "mainz");
            }
            Cache::put("keyserver:key:" . $this->key, $key_response, now()->addMinutes(30)); // Cache for 30 minutes
            $this->rememberKeyData($key_response);
            $this->key_data = $key_response; // Store the key data for future use
            $new_claim_amount = Arr::get($this->claims ?? [], $this->id, 0) - $token_cost;
            $this->claims[$this->id] = $new_claim_amount;
            $this->claimsConnection()->hincrbyfloat($this->claimsKey(), $this->id, -$token_cost);

            return true;
        }

        return false;
    }

    /**
     * Where this key's claims live. One hash per key, one field per request.
     */
    private function claimsKey(): string
    {
        return "keyserver:claims:" . $this->key;
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
        if (!$key_response = Cache::get("keyserver:key:" . $this->key)) {
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
                Cache::put("keyserver:key:" . $this->key, $key_response, now()->addSeconds(10)); // Cache for 10 seconds
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
        return "keyserver:key:" . $this->key . ":last";
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
