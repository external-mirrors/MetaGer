<?php

namespace App\Support;

use App\Console\Commands\RequestFetcher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Freshness of the fetch worker's heartbeat.
 *
 * `requests:fetcher` stamps the current time into Redis at the top of every
 * loop iteration (RequestFetcher::stampHealthcheck). That stamp has been
 * written since the command existed and, until this class, nothing read it —
 * the docblock on it says "it is read by the liveness probe", and the probe was
 * `pgrep -f requests:fetcher`, which only ever proved a process existed.
 *
 * That gap was the outage of 2026-09-10. Sentinel promoted a new Valkey master
 * after a node's container runtime restarted; the fetcher stayed alive holding
 * a half-open socket and stopped consuming. `read_write_timeout` is -1 on the
 * default connection (config/database.php) — it has to be, because callers on
 * that connection block for up to 30s on a `blpop` — so a read from a peer that
 * will never answer blocks in PHP for ever rather than raising a
 * CommunicationException. No exception means App\Support\RedisFailover never
 * sees a failover to retry, the loop never comes back round to the top, and the
 * stamp stops advancing. `pgrep` passed the whole time. `fetcher.queue` grew to
 * ~1200 missions and every search rendered empty for about fifteen minutes.
 *
 * Keyed off the stamp rather than off queue depth, because a deep queue is
 * ambiguous — a traffic spike looks the same as a wedge — while a stamp that
 * has stopped advancing means the loop itself has stopped, whichever call
 * inside it is hanging. It is the one signal that catches a wedge this class
 * has not been taught to recognise.
 *
 * Read by an exec probe only (`artisan fetcher:healthcheck`). Unlike the
 * scheduler there is no HTTP twin to keep in step: the worker Deployment runs
 * no HTTP server, and no Service selects its pods.
 */
class FetcherHeartbeat
{
    /**
     * How stale the stamp may get before the worker counts as wedged.
     *
     * Deliberately far wider than the scheduler's one minute, which covers a
     * heartbeat on a fixed once-a-minute schedule. This stamp is written many
     * times a second when all is well, so the tolerance is not sized against
     * the interval — it is sized against how late a *healthy* iteration can
     * legitimately be. During a failover every Redis call in the loop may spend
     * RedisFailover::BUDGET_SECONDS retrying, and readMultiCurl pays that
     * budget per answer it delivers, so a bad promotion can hold one iteration
     * open for tens of seconds. Restarting then is the worst possible moment:
     * the multicurl handle goes with the process, so every engine response in
     * flight is discarded and every search waiting on one renders without it.
     *
     * Sixty seconds clears that with roughly double the margin, and still turns
     * a wedge that ran for fifteen minutes into one caught inside two.
     */
    public const MAX_AGE_IN_SECONDS = 60;

    public static function lastLoopAt(): ?Carbon
    {
        $stamp = Redis::get(RequestFetcher::HEALTHCHECK_KEY);

        if (empty($stamp)) {
            return null;
        }

        try {
            return Carbon::createFromFormat(RequestFetcher::HEALTHCHECK_FORMAT, $stamp);
        } catch (\Exception $e) {
            // A malformed stamp is no more trustworthy than a missing one.
            return null;
        }
    }

    /**
     * @return array{0: bool, 1: string} [healthy, reason]
     */
    public static function check(): array
    {
        try {
            $lastLoop = self::lastLoopAt();
        } catch (Throwable $e) {
            // Reaching Redis is part of the check, not a precondition for it:
            // this worker's whole job is a Redis loop, so a probe that cannot
            // ask has not found the worker healthy. Reported as its own reason
            // rather than thrown, so the probe log says which of the two
            // failures this was.
            //
            // Restarting on this is safe and occasionally the cure. handle()
            // retries Redis on boot before entering the loop, and a promotion
            // is over in a second or two — far too short to produce the
            // consecutive failures a failureThreshold demands.
            return [false, "Could not reach Redis to read the heartbeat: " . $e->getMessage()];
        }

        if ($lastLoop === null) {
            return [false, "No fetcher heartbeat yet"];
        }

        // Rounded: diffInSeconds answers a float, and this string is what the
        // kubelet records as the probe's failure message — "83s old" reads as a
        // measurement, "83.35165s old" reads as a bug.
        $age = (int) round(Carbon::now()->diffInSeconds($lastLoop, true));

        if ($age > self::MAX_AGE_IN_SECONDS) {
            return [false, "Fetcher heartbeat is {$age}s old; the fetch loop has stopped"];
        }

        return [true, "ok"];
    }

    public static function isHealthy(): bool
    {
        return self::check()[0];
    }
}
