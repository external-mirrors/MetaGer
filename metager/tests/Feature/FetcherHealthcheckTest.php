<?php

namespace Tests\Feature;

use App\Console\Commands\RequestFetcher;
use App\Support\FetcherHeartbeat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Predis\ClientException;
use Tests\TestCase;

/**
 * The fetch worker's liveness signal.
 *
 * Regression test for the outage of 2026-09-10. `requests:fetcher` had stamped
 * a heartbeat into Redis since it was written, and nothing read it: the probe
 * was `pgrep -f requests:fetcher`. When Sentinel promoted a new master after a
 * node's container runtime restarted, the worker kept a half-open socket, hung
 * inside a blocking read that `read_write_timeout => -1` lets block for ever,
 * and stopped consuming. The process was alive, so pgrep passed; the queue grew
 * to ~1200 missions and every search rendered empty for about fifteen minutes.
 *
 * `master-worker` is a single replica, so that one wedged process was all of
 * search — there was no second worker to carry it.
 *
 * @see \App\Support\FetcherHeartbeat
 * @see \Tests\Feature\Search\RequestFetcherFailoverTest
 */
class FetcherHealthcheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Redis::del(RequestFetcher::HEALTHCHECK_KEY);

        parent::tearDown();
    }

    private function heartbeatAt(?Carbon $moment): void
    {
        if ($moment === null) {
            Redis::del(RequestFetcher::HEALTHCHECK_KEY);

            return;
        }

        Redis::set(
            RequestFetcher::HEALTHCHECK_KEY,
            $moment->format(RequestFetcher::HEALTHCHECK_FORMAT)
        );
    }

    public function testAFreshHeartbeatIsHealthy(): void
    {
        $this->heartbeatAt(Carbon::now()->subSeconds(5));

        $this->assertTrue(FetcherHeartbeat::isHealthy());
        $this->artisan("fetcher:healthcheck")->assertSuccessful();
    }

    /**
     * The outage itself: a stamp that has stopped advancing while the process
     * is still very much alive. This is the case `pgrep` answered "healthy".
     */
    public function testAStaleHeartbeatIsUnhealthy(): void
    {
        $this->heartbeatAt(Carbon::now()->subMinutes(15));

        [$healthy, $reason] = FetcherHeartbeat::check();

        $this->assertFalse($healthy);
        $this->assertStringContainsString("the fetch loop has stopped", $reason);
        $this->artisan("fetcher:healthcheck")->assertFailed();
    }

    public function testAMissingHeartbeatIsUnhealthy(): void
    {
        $this->heartbeatAt(null);

        [$healthy, $reason] = FetcherHeartbeat::check();

        $this->assertFalse($healthy);
        $this->assertSame("No fetcher heartbeat yet", $reason);
        $this->artisan("fetcher:healthcheck")->assertFailed();
    }

    /**
     * The stamp is written by another process; a partial or corrupted write
     * must not turn the probe into an uncaught ParseException, which the
     * kubelet would report as a probe failure for entirely the wrong reason.
     */
    public function testAMalformedHeartbeatIsUnhealthyRatherThanFatal(): void
    {
        Redis::set(RequestFetcher::HEALTHCHECK_KEY, "not-a-timestamp");

        $this->assertNull(FetcherHeartbeat::lastLoopAt());
        $this->artisan("fetcher:healthcheck")->assertFailed();
    }

    /**
     * A probe that cannot reach Redis has not found the worker healthy — this
     * worker is a Redis loop and nothing else. It must say so with an exit
     * status, though, not with a stack trace out of the facade.
     *
     * The exception is the one production actually raises when the sentinels
     * are unreachable; see the note on it in config/database.php.
     */
    public function testAnUnreachableRedisIsUnhealthyRatherThanFatal(): void
    {
        // A delegating wrapper rather than a mock, the same way
        // Tests\Support\FailingOverRedis does it: a full mock replaces the
        // manager outright, and then everything else that touches Redis in this
        // test — tearDown's own del() included — has no method to call.
        Redis::swap(new class (Redis::getFacadeRoot()) {
            public function __construct(private object $inner) {}

            public function get(string $key): never
            {
                throw new ClientException("No sentinel server available for autodiscovery");
            }

            /** @param array<int, mixed> $arguments */
            public function __call(string $method, array $arguments): mixed
            {
                return $this->inner->{$method}(...$arguments);
            }
        });

        [$healthy, $reason] = FetcherHeartbeat::check();

        $this->assertFalse($healthy);
        $this->assertStringContainsString("Could not reach Redis", $reason);
        $this->artisan("fetcher:healthcheck")->assertFailed();
    }

    /**
     * Writer and reader have to agree on the key *and* the format, and they are
     * the only two things that touch it. Driving the command's own stamp is what
     * makes this a contract rather than two copies of a string constant: change
     * HEALTHCHECK_FORMAT on one side and this fails, instead of the probe
     * quietly reporting every healthy worker as dead.
     */
    public function testTheWorkersOwnStampIsReadableByTheProbe(): void
    {
        $this->heartbeatAt(null);

        $fetcher = new class extends RequestFetcher {
            public function stamp(): void
            {
                $this->stampHealthcheck();
            }
        };
        $fetcher->stamp();

        $this->assertNotNull(
            Redis::get(RequestFetcher::HEALTHCHECK_KEY),
            "the worker did not stamp anything, so this proves nothing"
        );
        $this->assertTrue(
            FetcherHeartbeat::isHealthy(),
            "the probe cannot read the stamp the worker just wrote"
        );
        $this->artisan("fetcher:healthcheck")->assertSuccessful();
    }

    /**
     * Pinned because the number is a judgement, not a detail. It is far wider
     * than the scheduler's one minute on purpose: during a failover every Redis
     * call in the loop may spend RedisFailover::BUDGET_SECONDS retrying, and
     * readMultiCurl pays that per answer, so a healthy iteration can run late
     * by tens of seconds. Restarting then discards every engine response in
     * flight, which is the thing the retry layer exists to avoid.
     *
     * Tightening this trades that risk for a faster catch; widening it leaves
     * search down for longer. The probe's failureThreshold is the knob for
     * absorbing a single late reading.
     */
    public function testTheToleranceIsOneMinute(): void
    {
        $this->assertSame(60, FetcherHeartbeat::MAX_AGE_IN_SECONDS);

        $this->heartbeatAt(Carbon::now()->subSeconds(59));
        $this->assertTrue(FetcherHeartbeat::isHealthy());

        $this->heartbeatAt(Carbon::now()->subSeconds(61));
        $this->assertFalse(FetcherHeartbeat::isHealthy());
    }
}
