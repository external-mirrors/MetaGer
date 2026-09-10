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
 * That gap was the outage of 2026-09-10, when draining the mailu pod hit a
 * CephFS kernel panic and rebooted the node instantly: the Valkey master went
 * without a preStop hook, so nothing closed its sockets, and the worker's read
 * went into a socket that would never answer. `pgrep` passed the whole time.
 * `fetcher.queue` grew to ~1200 missions and every search rendered empty for
 * about fifteen minutes.
 *
 * **This is the backstop, not the fix.** The worker now repairs itself: its
 * connection has a bounded read timeout (RequestFetcher::REDIS_CONNECTION), so
 * a peer that stops answering raises Predis\TimeoutException, RedisFailover
 * drops the socket and reconnects through the master proxy, and the loop
 * carries on — measured at 3.6s from black-holing the master's socket to the
 * heartbeat advancing again, with no restart. A node drain happens several
 * times a week; being restarted by a probe every time is not an answer.
 *
 * What is left for this class is the wedge nobody has anticipated — a hang
 * somewhere the bounded timeout does not reach. So it stays, and its tolerance
 * is set for that role rather than for fast detection: if this probe is what
 * recovers the worker, the mechanism above has already failed.
 *
 * Keyed off the stamp rather than off queue depth, because a deep queue is
 * ambiguous — a traffic spike looks the same as a wedge — while a stamp that
 * has stopped advancing means the loop itself has stopped, whichever call
 * inside it is hanging.
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
     * Sixty seconds clears that with roughly double the margin. It is not the
     * number that decides how fast a failover is survived — that is ~3.6s, and
     * it happens without a restart — only how long an *unanticipated* hang can
     * last. Tightening it trades a rarely-used backstop against restarting a
     * healthy worker mid-failover, which is the expensive mistake: the
     * multicurl handle goes with the process.
     */
    public const MAX_AGE_IN_SECONDS = 60;

    public static function lastLoopAt(): ?Carbon
    {
        // The worker's own connection, not `default`: this is a liveness probe,
        // and a probe that can block for ever in a socket read is no better
        // than the pgrep it replaced. RequestFetcher::REDIS_CONNECTION bounds
        // the read, so an unreachable Valkey becomes an exception the caller
        // reports rather than a probe the kubelet has to time out.
        $stamp = Redis::connection(RequestFetcher::REDIS_CONNECTION)
            ->get(RequestFetcher::HEALTHCHECK_KEY);

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
