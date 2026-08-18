<?php

namespace Tests\Feature;

use App\Console\Commands\ScheduleWorker;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * Regression test: the scheduler must not hold a Redis connection across the
 * minute it spends asleep.
 *
 * The bug this pins was a crash loop, not a slowdown. `schedule:run` asks the
 * cache whether the schedule is paused before it does anything else, so the
 * first thing every run touches is a connection that has been idle for the
 * whole sleep. The chart's HAProxy master proxy closes idle connections at
 * thirty seconds and the scheduler sleeps sixty, so the connection was always
 * gone — Predis reported "Stream is already at the end", `schedule:run` aborted,
 * the process exited, and Kubernetes restarted it a minute later, for ever.
 *
 * It was invisible until the scheduler became a Deployment of its own: as a
 * sidecar it was a restart count on an otherwise healthy pod, and afterwards it
 * was a deployment that never goes Ready, which fails `helm --wait` and so
 * fails the deploy.
 *
 * Nothing here can talk to an HAProxy, so what is asserted is the mechanism the
 * fix relies on: after releaseIdleConnections() the manager holds nothing, so
 * the next command has to open a connection rather than reuse one that a proxy
 * may already have hung up on.
 */
class ScheduleWorkerTest extends TestCase
{
    public function testTheWorkerLetsGoOfEveryRedisConnection(): void
    {
        // Resolving one is what puts it in the manager's cache; until then
        // there is nothing to let go of and the test would pass vacuously.
        Redis::connection();
        Redis::connection(config("cache.stores.redis.connection"));

        $this->assertNotEmpty(
            $this->app->make("redis")->connections(),
            "No connection was open, so this test cannot tell a fix from a no-op."
        );

        $this->app->make(ScheduleWorker::class)->releaseIdleConnections();

        $this->assertSame(
            [],
            $this->app->make("redis")->connections(),
            "The scheduler went to sleep still holding a connection. HAProxy closes it after 30s and the sleep is 60s, so the next run will crash the process."
        );
    }

    /**
     * And having let go, it can still reach Redis — a fix that left the manager
     * unable to reconnect would pass the test above and break the scheduler
     * completely.
     */
    public function testAConnectionCanStillBeOpenedAfterwards(): void
    {
        Redis::connection();
        $this->app->make(ScheduleWorker::class)->releaseIdleConnections();

        $this->assertSame(
            "PONG",
            (string) Redis::connection()->ping(),
            "The scheduler cannot reconnect after releasing its connections."
        );
    }
}
