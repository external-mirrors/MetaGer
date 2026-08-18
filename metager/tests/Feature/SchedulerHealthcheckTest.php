<?php

namespace Tests\Feature;

use App\Console\Commands\Heartbeat;
use App\Support\SchedulerHeartbeat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * The scheduler's liveness signal, over both channels that read it.
 *
 * It moved out of the app pod into its own single-replica Deployment, which took
 * its HTTP probe away: /health-check/liveness-scheduler used to be reachable
 * because nginx sat in the same network namespace on port 8080. The scheduler now
 * probes itself with `artisan schedule:healthcheck`, and both paths go through
 * App\Support\SchedulerHeartbeat so a change to one cannot leave the other behind.
 *
 * The single replica also makes this check matter more than it did. With the
 * scheduler in every app pod, the heartbeat was global — one live scheduler kept
 * every replica's probe green, including replicas whose own scheduler was wedged.
 * Now there is exactly one, so the probe finally means what it says.
 */
class SchedulerHealthcheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Redis::del(Heartbeat::REDIS_KEY);

        parent::tearDown();
    }

    private function heartbeatAt(?Carbon $moment): void
    {
        if ($moment === null) {
            Redis::del(Heartbeat::REDIS_KEY);

            return;
        }

        Redis::set(Heartbeat::REDIS_KEY, $moment->format("Y-m-d H:i:s"));
    }

    public function testAFreshHeartbeatIsHealthy(): void
    {
        $this->heartbeatAt(Carbon::now()->subSeconds(30));

        $this->assertTrue(SchedulerHeartbeat::isHealthy());
        $this->artisan("schedule:healthcheck")->assertSuccessful();
        $this->get("/health-check/liveness-scheduler")->assertOk();
    }

    public function testAStaleHeartbeatIsUnhealthy(): void
    {
        $this->heartbeatAt(Carbon::now()->subMinutes(5));

        [$healthy, $reason] = SchedulerHeartbeat::check();

        $this->assertFalse($healthy);
        $this->assertSame("Last heartbeat too long ago", $reason);
        $this->artisan("schedule:healthcheck")->assertFailed();
        $this->get("/health-check/liveness-scheduler")->assertStatus(500);
    }

    public function testAMissingHeartbeatIsUnhealthy(): void
    {
        $this->heartbeatAt(null);

        [$healthy, $reason] = SchedulerHeartbeat::check();

        $this->assertFalse($healthy);
        $this->assertSame("No heartbeat yet", $reason);
        $this->artisan("schedule:healthcheck")->assertFailed();
        $this->get("/health-check/liveness-scheduler")->assertStatus(500);
    }

    /**
     * The stamp is written by another process; a partial or corrupted write must
     * not turn a liveness probe into a 500 from an uncaught ParseException.
     */
    public function testAMalformedHeartbeatIsUnhealthyRatherThanFatal(): void
    {
        Redis::set(Heartbeat::REDIS_KEY, "not-a-timestamp");

        $this->assertNull(SchedulerHeartbeat::lastRunAt());
        $this->artisan("schedule:healthcheck")->assertFailed();
    }

    /**
     * The heartbeat is scheduled every minute and tolerated for one, so the
     * window is only as wide as the interval. Pinned because the probe's
     * failureThreshold is what absorbs a single late run — widening this instead
     * would mean a wedged scheduler goes unnoticed for that much longer.
     */
    public function testTheToleranceIsOneMinute(): void
    {
        $this->assertSame(1, SchedulerHeartbeat::MAX_AGE_IN_MINUTES);

        $this->heartbeatAt(Carbon::now()->subSeconds(59));
        $this->assertTrue(SchedulerHeartbeat::isHealthy());

        $this->heartbeatAt(Carbon::now()->subSeconds(61));
        $this->assertFalse(SchedulerHeartbeat::isHealthy());
    }
}
