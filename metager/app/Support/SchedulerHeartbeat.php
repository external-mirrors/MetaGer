<?php

namespace App\Support;

use App\Console\Commands\Heartbeat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

/**
 * Freshness of the scheduler's heartbeat.
 *
 * `schedule:work-mg` runs the `heartbeat` command every minute, which stamps the
 * current time into Redis. Anything asking "is the scheduler alive?" is asking
 * whether that stamp is recent.
 *
 * Extracted from HealthcheckController so the scheduler can answer the question
 * about itself. It used to be reachable only over HTTP, which was fine while the
 * scheduler shared a pod with nginx and fpm — its probe hit port 8080 in the same
 * network namespace. In its own Deployment there is no HTTP server to ask.
 */
class SchedulerHeartbeat
{
    /**
     * How stale the stamp may get before the scheduler counts as dead.
     *
     * The heartbeat is scheduled every minute, so this is deliberately tight. A
     * probe using it should absorb a single late run through failureThreshold
     * rather than by widening this.
     */
    public const MAX_AGE_IN_MINUTES = 1;

    public static function lastRunAt(): ?Carbon
    {
        $stamp = Redis::get(Heartbeat::REDIS_KEY);

        if (empty($stamp)) {
            return null;
        }

        try {
            return Carbon::createFromFormat("Y-m-d H:i:s", $stamp);
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
        $lastRun = self::lastRunAt();

        if ($lastRun === null) {
            return [false, "No heartbeat yet"];
        }

        $age = Carbon::now()->diffInMinutes($lastRun, true);

        if ($age > self::MAX_AGE_IN_MINUTES) {
            return [false, "Last heartbeat too long ago"];
        }

        return [true, "ok"];
    }

    public static function isHealthy(): bool
    {
        return self::check()[0];
    }
}
