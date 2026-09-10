<?php

namespace App\Queue;

use App\Support\RedisFailover;
use Illuminate\Queue\RedisQueue;

/**
 * The Redis queue, minus the part where a failover ends the worker.
 *
 * `queue:work` is a daemon. It resolves one queue connection at startup and
 * keeps it: `redis` is a container singleton, so the Predis client — and its
 * socket — lives as long as the pod. Every iteration of Worker::daemon calls
 * pop() on it, and pop() is the only Redis call left in that loop
 * (App\Providers\AppServiceProvider turns off the interruption polling that
 * added two more).
 *
 * That single call is enough to stop the worker for good. When a Valkey
 * failover leaves the socket pointing at a node that is gone, pop() throws;
 * Worker::getNextJob catches it, reports it, sleeps a second and pops again —
 * on the same pooled socket, which is not going to start working. Predis'
 * "Operation has timed out" is not in Laravel's lost-connection list either
 * (that list is entirely PDO messages), so stopWorkerIfLostConnection does not
 * fire and the worker never quits to be replaced. Before the --max-time
 * recycle existed the loop would spin like that until someone noticed; with it,
 * recovery took up to five minutes of undelivered balance updates.
 *
 * RedisFailover::retry closes both halves: it drops the socket and tries again
 * within the budget, and — since App\Support\RedisFailover reconnects before
 * rethrowing — even giving up leaves a fresh connection for the next iteration.
 * So the worst case is one report and a one-second sleep, not a dead worker.
 *
 * Retrying a pop cannot lose a job. migrateExpiredJobs is idempotent, and the
 * pop itself is one atomic Lua script: if the reply is lost after the server
 * ran it, the job is sitting in the `:reserved` set and comes back after
 * `retry_after`. That is the queue's existing at-least-once contract — the same
 * thing that happens whenever a worker dies holding a job — not something this
 * subclass introduces.
 *
 * Only pop() is wrapped. deleteReserved() and deleteAndRelease() run after a
 * job has been handled, so a failure there costs one duplicate delivery rather
 * than the worker's loop, and a duplicate is what this queue is already built
 * to tolerate: see config/broadcasting.php on why a missed broadcast costs a
 * refresh. Retrying deleteAndRelease would not even be safe — its script
 * removes and re-pushes, so a lost reply would double-release.
 *
 * @see \App\Console\Commands\RequestFetcher  the same fix, for the fetch loop
 */
class FailoverRedisQueue extends RedisQueue
{
    /**
     * Longer than a request's budget on purpose: nobody is blocked on a job
     * pop, so it can afford to sit through a promotion rather than report and
     * sleep. It has to exceed the read timeout on the connection or the first
     * timeout spends the whole budget and the retry rethrows before it ever
     * attempts the reconnected socket — the pairing that
     * Tests\Unit\QueueRedisConnectionTest pins.
     */
    public const RETRY_BUDGET_SECONDS = 12.0;

    /**
     * @param  \UnitEnum|string|null  $queue
     * @param  int  $index
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null, $index = 0)
    {
        return RedisFailover::retry(
            fn() => parent::pop($queue, $index),
            self::RETRY_BUDGET_SECONDS,
            // Null means "the default connection" to both this class and
            // RedisFailover, so an unset queue connection still reconnects the
            // one it actually used.
            $this->connection
        );
    }
}
