<?php

namespace App\Console\Commands;

use App\Authentication\KeyUser;
use App\Events\KeyChanged;
use App\PrometheusExporter;
use App\Support\RedisFailover;
use Arr;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Predis\PredisException;

/**
 * Pay the keyserver what the searches owe it.
 *
 * A search used to end with `POST /key/<key>/discharge` while the user was
 * waiting: a network round trip to the keyserver, and behind the keyserver the
 * only Postgres either of MetaGer's two hot paths still depended on. That call
 * is the last thing standing between a CNPG switchover and a result page. It
 * is also the call that lost the operator the fee whenever the keyserver was
 * briefly unreachable, because there was nowhere to put a charge that could
 * not be made right now.
 *
 * So the search writes the charge to Redis and this settles it — the same
 * shape {@see \App\QueryLogger} already uses for search logs, which are
 * written to a Redis list and drained into Postgres by `logs:gather` a minute
 * later. Redis was already load-bearing for both paths; Postgres and the
 * keyserver no longer are.
 *
 * ## What holds the money in the meantime
 *
 * The claim. {@see KeyUser::authorize()} reserves the amount on
 * `keyserver:claims:<key>` before the search runs, so the tokens are already
 * spoken for while the discharge is queued, and every other request for that
 * key sees a charge with the reservation subtracted. Settling is what releases
 * it — this command, not the request. Queueing extends the claim's expiry from
 * the 30 seconds a search needs to the {@see KeyUser::SETTLEMENT_WINDOW_SECONDS}
 * this command needs, so a key can never look topped up again while a charge
 * against it is still in the queue.
 *
 * ## Retrying, and where it stops
 *
 * Only when the request provably never arrived — a refused connection, an
 * unresolvable host. Those are the failures a keyserver rollout or a Postgres
 * switchover behind it actually produces, and retrying them is free of the one
 * thing a payment must not do twice.
 *
 * Anything else is dropped: a timeout cannot be told apart from a discharge
 * that was applied and whose reply was lost, and a 4xx/5xx means the keyserver
 * has already seen the request and answered. Retrying either would risk
 * charging a user twice, which is worse than the operator losing the fee. If
 * the keyserver ever accepts an idempotency key on this endpoint, that
 * calculation changes and this can retry everything.
 *
 * A retry ends the run rather than moving to the next item: the failure is a
 * statement about the keyserver, not about this discharge, so the rest of the
 * queue would only fail the same way and burn its attempts doing it. The
 * schedule brings the next attempt a minute later, which is the backoff.
 *
 * @see \App\Authentication\KeyUser::makePayment()
 */
class SettleKeyDischarges extends Command
{
    /**
     * The queue itself. Written with `lpush` and read with `rpop`, so it is
     * first-in-first-out and a rescheduled item keeps its place at the head.
     *
     * Lives on the cache connection, with the claims it settles — one
     * connection, and the pipeline in makePayment() that queues a charge and
     * extends its claim is one round trip.
     */
    public const REDIS_KEY = "keyserver:discharges";

    /**
     * Five runs, one a minute: five minutes of an unreachable keyserver before
     * a charge is given up on. Comfortably inside
     * {@see KeyUser::SETTLEMENT_WINDOW_SECONDS}, so the claim is never released
     * by an expiry while this is still trying.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Nobody is waiting for this, so it can wait longer than
     * {@see KeyUser::TIMEOUT_SECONDS} does. Still bounded: the run has a
     * minute before the next one is due.
     */
    private const TIMEOUT_SECONDS = 5;
    private const CONNECT_TIMEOUT_SECONDS = 2;

    /**
     * The failures that mean the discharge never happened.
     *
     * Matched on the message because that is the only place the distinction
     * survives: Guzzle raises the same ConnectException for a refused
     * connection and for a request that timed out mid-flight, and Laravel wraps
     * both in one ConnectionException. cURL 6 is an unresolvable host, cURL 7 a
     * refused connection — a keyserver pod that is not there. cURL 28, a
     * timeout, is deliberately absent: it is the case where the charge may
     * already have been applied.
     */
    private const NEVER_ARRIVED = [
        "cURL error 6",
        "cURL error 7",
        "Could not resolve host",
        "Connection refused",
        "Failed to connect",
    ];

    protected $signature = 'keys:settle-discharges'
        . ' {--limit=5000 : How many discharges to settle before returning}'
        . ' {--max-seconds=50 : How long to keep settling before leaving the rest to the next run}';

    protected $description = 'Discharge the keyserver charges a search queued instead of paying in the foreground';

    private const SETTLED = "settled";
    private const DROPPED = "dropped";
    private const RESCHEDULED = "rescheduled";

    public function handle(): int
    {
        $settled = 0;
        $dropped = 0;
        $limit = max(1, (int) $this->option("limit"));
        // Bounded by the clock as well as the count, because the count is a
        // guess about how long a discharge takes and this is not: the schedule
        // brings another run a minute from now, so a run that is still going
        // then is one that should have stopped.
        $deadline = microtime(true) + max(1, (int) $this->option("max-seconds"));

        for ($i = 0; $i < $limit && microtime(true) < $deadline; $i++) {
            $discharge = $this->next();

            if ($discharge === null) {
                break;
            }

            $result = $this->settle($discharge);

            if ($result === self::RESCHEDULED) {
                // The keyserver is not answering. Everything behind this one
                // would fail the same way, so stop and let the next run try.
                break;
            }

            $result === self::SETTLED ? $settled++ : $dropped++;
        }

        // What is left, every minute, whether or not this run did anything.
        // Discharges are settled one at a time over one connection, so there is
        // a rate above which this cannot keep up — and the symptom of that is
        // not an error anywhere, it is a number that climbs. This is the number.
        PrometheusExporter::KeyDischargeQueueDepth($this->depth());

        if ($settled > 0 || $dropped > 0) {
            $this->info("Settled {$settled} discharge(s), dropped {$dropped}.");
        }

        return self::SUCCESS;
    }

    private function depth(): int
    {
        try {
            return (int) RedisFailover::retry(
                fn() => $this->connection()->llen(self::REDIS_KEY),
                connection: config("cache.stores.redis.connection")
            );
        } catch (PredisException $e) {
            return 0;
        }
    }

    /**
     * The next queued discharge, or null when there is none or Redis cannot be
     * asked.
     *
     * A failover here costs this run, not the queue: the entries stay where
     * they are and the next run reads them.
     *
     * @return array<string, mixed>|null
     */
    private function next(): array|null
    {
        try {
            $payload = RedisFailover::retry(
                fn() => $this->connection()->rpop(self::REDIS_KEY),
                connection: config("cache.stores.redis.connection")
            );
        } catch (PredisException $e) {
            Log::warning("Could not read the discharge queue: " . $e->getMessage());

            return null;
        }

        if (!is_string($payload)) {
            return null;
        }

        $discharge = json_decode($payload, true);

        if (!is_array($discharge) || !is_string(Arr::get($discharge, "key")) || !is_numeric(Arr::get($discharge, "amount"))) {
            Log::error("Discarding an unreadable queued discharge: " . $payload);

            return null;
        }

        return $discharge;
    }

    /**
     * @param array<string, mixed> $discharge
     */
    private function settle(array $discharge): string
    {
        $key = (string) $discharge["key"];
        $amount = (float) $discharge["amount"];

        if (!$this->take($discharge)) {
            // Already paid. The only way the same id reaches here twice is a
            // retried `lpush` in KeyUser::queueDischarge() whose first attempt
            // did land, which is what makes that retry safe.
            Log::info("Skipping a keyserver discharge that was already settled.");

            return self::DROPPED;
        }

        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";

        // The keyserver rate-limits per client IP and sees one Bearer token for
        // all of MetaGer, so the address of the user this charge belongs to has
        // to be forwarded — the same header the foreground discharge sent. This
        // process has no request of its own; the address rides along in the
        // queued payload.
        $headers = [
            "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
            "Content-Type" => "application/json",
        ];
        if (is_string($ip = Arr::get($discharge, "ip")) && $ip !== "") {
            $headers["X-Forwarded-For"] = $ip;
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                ->withHeaders($headers)
                ->post($keyserver . "/api/json/key/" . urlencode($key) . "/discharge", [
                        "amount" => $amount,
                    ]);
        } catch (ConnectionException $e) {
            return $this->reschedule($discharge, $e->getMessage());
        }

        if (!$response->successful()) {
            // The keyserver has seen the request and refused it — an expired
            // key, a charge that is no longer there. Nothing was taken, so the
            // claim goes back.
            Log::warning("keyserver refused a discharge of {$amount} with HTTP " . $response->status());
            $this->releaseClaim($discharge, $amount);
            PrometheusExporter::KeyDischargeSettled("refused");

            return self::DROPPED;
        }

        $this->apply($key, $amount, $response->json());
        $this->releaseClaim($discharge, $amount);
        PrometheusExporter::KeyDischargeSettled("settled");

        return self::SETTLED;
    }

    /**
     * Put a discharge back at the head of the queue, unless it has been tried
     * often enough or failed in a way that might have charged the key.
     *
     * @param array<string, mixed> $discharge
     */
    private function reschedule(array $discharge, string $reason): string
    {
        $attempts = (int) Arr::get($discharge, "attempts", 0) + 1;

        if (!$this->neverArrived($reason)) {
            // Left taken. The charge may have been applied, so a duplicate of
            // it must not be paid either.
            Log::warning("Dropping a discharge whose outcome is unknown: " . $reason);
            PrometheusExporter::KeyDischargeSettled("unknown");

            // Pointedly not released: for all we know the key was charged, and
            // handing the tokens back would be the second half of a double
            // spend. The claim expires on its own.
            return self::DROPPED;
        }

        if ($attempts >= self::MAX_ATTEMPTS) {
            // Also left taken: nothing more will be paid for this id.
            Log::error("Giving up on a keyserver discharge after {$attempts} attempts: " . $reason);
            $this->releaseClaim($discharge, (float) $discharge["amount"]);
            PrometheusExporter::KeyDischargeSettled("abandoned");

            return self::DROPPED;
        }

        $discharge["attempts"] = $attempts;

        // Nothing reached the keyserver, so the id has to be payable again.
        $this->release($discharge);

        try {
            RedisFailover::retry(
                fn() => $this->connection()->lpush(self::REDIS_KEY, json_encode($discharge)),
                connection: config("cache.stores.redis.connection")
            );
        } catch (PredisException $e) {
            Log::error("Lost a keyserver discharge that could not be requeued: " . $e->getMessage());

            return self::DROPPED;
        }

        return self::RESCHEDULED;
    }

    private function neverArrived(string $reason): bool
    {
        foreach (self::NEVER_ARRIVED as $needle) {
            if (str_contains($reason, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Write the charge the keyserver just confirmed where the next request
     * will read it.
     *
     * The same two cache entries {@see KeyUser::getKeyData()} reads: the short
     * one that makes the balance on the page current, and the hour-long
     * fallback that answers when the keyserver cannot. Without this the next
     * page would go and ask for a number this command was just told.
     *
     * @param array<string, mixed>|null $key_response
     */
    private function apply(string $key, float $amount, array|null $key_response): void
    {
        $charge = Arr::get($key_response, "charge");

        if ($charge === null) {
            return;
        }

        Cache::put(KeyUser::keyDataCacheKey($key), $key_response, now()->addSeconds(10));
        Cache::put(KeyUser::rememberedKeyDataCacheKey($key), $key_response, now()->addSeconds(3600));

        /** @var array $uniMainzKeys */
        $uniMainzKeys = config('metager.metager.keys.uni_mainz', []);
        if (in_array($key, $uniMainzKeys)) {
            PrometheusExporter::UpdateKeyStatus(key: $key, tokens: $charge, owner: "mainz");
        }

        // The balance in an open tab, without a reload. Broadcasting is
        // rescued and pinned to Redis (App\Events\KeyChanged), so this cannot
        // fail the run.
        KeyChanged::dispatch($key, -$amount, (float) $charge);
    }

    /**
     * Hand the reservation back.
     *
     * The claim stood in for tokens that had left the key but were not yet
     * recorded as gone. Once the keyserver's own number reflects the charge —
     * or once we know it never will — holding it aside as well would charge
     * the key twice over.
     *
     * The field is the request's own, so nobody else writes it, and it expires
     * regardless; the worst a failure here costs is that one reservation
     * stands until it does.
     *
     * @param array<string, mixed> $discharge
     */
    private function releaseClaim(array $discharge, float $amount): void
    {
        $claim = Arr::get($discharge, "claim");

        if (!is_string($claim) || $claim === "") {
            return;
        }

        try {
            RedisFailover::retry(
                fn() => $this->connection()->hincrbyfloat(KeyUser::claimsCacheKey((string) $discharge["key"]), $claim, -$amount),
                connection: config("cache.stores.redis.connection")
            );
        } catch (PredisException $e) {
            Log::warning("Could not release a settled key claim: " . $e->getMessage());
        }
    }

    /**
     * Claim the right to pay this charge, once.
     *
     * `SET <id> NX` is the whole mechanism: the first run to reach a given
     * discharge id gets the slot and everything after it is refused. It exists
     * because KeyUser::queueDischarge() writes to Redis through
     * App\Support\RedisFailover, which re-issues a write whose reply a
     * Sentinel promotion swallowed -- and `lpush` is not idempotent, so a
     * charge can legitimately be on the queue twice. One of those two is money
     * the user does not owe.
     *
     * Taken *before* the request rather than marked after it, so that a
     * discharge which may have been applied is never paid a second time: the
     * slot is only handed back when we know nothing reached the keyserver
     * ({@see reschedule()}).
     *
     * An hour is far longer than any charge takes to settle -- five attempts a
     * minute apart is the maximum -- and short enough that these do not
     * accumulate in a store with no persistence.
     *
     * A Redis failure here answers "taken": paying the charge is the
     * behaviour we would rather fall back to than skipping it, and a duplicate
     * requires the failover that produced it in the first place.
     *
     * @param array<string, mixed> $discharge
     */
    private function take(array $discharge): bool
    {
        $slot = $this->slotKey($discharge);

        if ($slot === null) {
            return true;
        }

        try {
            $taken = RedisFailover::retry(
                fn() => $this->connection()->set($slot, "1", "EX", 3600, "NX"),
                connection: config("cache.stores.redis.connection")
            );
        } catch (PredisException $e) {
            Log::warning("Could not check a discharge for duplicates: " . $e->getMessage());

            return true;
        }

        return (string) $taken === "OK";
    }

    /**
     * @param array<string, mixed> $discharge
     */
    private function release(array $discharge): void
    {
        $slot = $this->slotKey($discharge);

        if ($slot === null) {
            return;
        }

        try {
            RedisFailover::retry(
                fn() => $this->connection()->del($slot),
                connection: config("cache.stores.redis.connection")
            );
        } catch (PredisException $e) {
            Log::warning("Could not reopen a discharge for a retry: " . $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $discharge
     */
    private function slotKey(array $discharge): string|null
    {
        $id = Arr::get($discharge, "id");

        return is_string($id) && $id !== "" ? "keyserver:discharge:settled:" . $id : null;
    }

    private function connection(): mixed
    {
        return Redis::connection(config("cache.stores.redis.connection"));
    }
}
