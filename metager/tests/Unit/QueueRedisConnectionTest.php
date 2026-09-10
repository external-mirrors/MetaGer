<?php

namespace Tests\Unit;

use App\Queue\FailoverRedisQueue;
use App\Support\RedisFailover;
use Illuminate\Queue\Worker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The broadcast worker's Redis connection, and why it is not the shared one.
 *
 * `queue:work` is a daemon: it resolves one queue connection at startup and
 * holds its Predis socket for the life of the pod, because `redis` is a
 * container singleton. On the shared connection that socket carries a 35s read
 * timeout — a bound the default connection cannot go below, since the same one
 * serves AnonymousToken's 30s payment wait — so a Valkey failover cost the
 * broadcast worker 35s per pop, on a socket nothing dropped, until the
 * --max-time recycle five minutes later.
 *
 * Node drains happen several times a week. These are the pairings that keep the
 * tighter connection safe and the retry able to use it; each one is easy to
 * break by tuning a single number.
 *
 * @see \Tests\Unit\FailoverRedisQueueTest      what the driver does with the exception
 * @see \Tests\Unit\FetcherRedisConnectionTest  the same shape, for the fetch loop
 */
class QueueRedisConnectionTest extends TestCase
{
    private function queueConnection(): array
    {
        return config("database.redis." . config("queue.connections.redis.connection"));
    }

    /**
     * The property that makes the failure detectable at all: without it the
     * read never returns and never errors, so nothing retries or reports.
     */
    public function testTheQueueReadTimeoutIsBounded(): void
    {
        $this->assertGreaterThan(
            0,
            $this->queueConnection()["read_write_timeout"],
            "an unbounded read timeout is the wedge: a vanished peer raises nothing"
        );
    }

    /**
     * Why the worker needed a connection of its own rather than a change to the
     * shared one. A worker that tolerates 30s of silence is a worker that is
     * down for 30s, and the balance the user is watching stops updating.
     */
    public function testTheQueueIsTighterThanTheSharedConnection(): void
    {
        $this->assertLessThan(
            config("database.redis.default.read_write_timeout"),
            $this->queueConnection()["read_write_timeout"],
            "the point of a separate connection is a tighter timeout than the shared one allows"
        );
    }

    /**
     * A tight read timeout is only safe because the driver never blocks.
     *
     * With a non-null block_for, RedisQueue::retrieveNextJob() blpops for that
     * many seconds — so a read timeout at or below it would abort every poll
     * with a Predis\TimeoutException while the client waited exactly as
     * configured, turning a quiet queue into a stream of reported errors.
     */
    public function testTheQueueDriverMakesNoBlockingRead(): void
    {
        $blockFor = config("queue.connections.redis.block_for");

        if ($blockFor !== null) {
            $this->assertLessThan(
                $this->queueConnection()["read_write_timeout"],
                $blockFor,
                "a blocking pop longer than the read timeout fails on every single poll"
            );
        }

        $this->assertNull(
            $blockFor,
            "block_for is pinned to null; polling with an eval is what the tight timeout assumes"
        );
    }

    /**
     * The read timeout has to fit *inside* the retry budget. Otherwise the
     * first timeout spends the whole budget and the retry rethrows before it
     * ever attempts the socket it just reconnected — and that attempt is the
     * one that recovers. RedisFailover's default budget is 3s, the same as this
     * read timeout, which is exactly why the queue cannot use it.
     */
    public function testTheRetryBudgetOutlastsAReadTimeout(): void
    {
        $this->assertGreaterThan(
            $this->queueConnection()["read_write_timeout"],
            FailoverRedisQueue::RETRY_BUDGET_SECONDS,
            "the budget must survive one timeout with room for a fresh attempt"
        );
        $this->assertGreaterThan(
            RedisFailover::BUDGET_SECONDS,
            FailoverRedisQueue::RETRY_BUDGET_SECONDS,
            "a job pop can wait out a promotion; nobody is blocked on it"
        );
    }

    /**
     * Paid exactly when the far side may have vanished, so Predis' 5s default
     * would make recovering from a dead master slower than noticing it.
     */
    public function testTheQueueConnectTimeoutIsBounded(): void
    {
        $connect = $this->queueConnection()["timeout"];

        $this->assertGreaterThan(0, $connect);
        $this->assertLessThanOrEqual(
            $this->queueConnection()["read_write_timeout"],
            $connect,
            "opening a socket should not cost more than waiting on one"
        );
    }

    /**
     * Same server, same database — only the socket options differ. fpm pushes
     * the broadcast and the worker pops it, so a connection pointing somewhere
     * else would be a balance that silently never updates.
     */
    public function testTheQueueTalksToTheSameRedisAsEveryoneElse(): void
    {
        $queue = $this->queueConnection();
        $default = config("database.redis.default");

        foreach (["host", "port", "database", "password"] as $key) {
            $this->assertSame(
                $default[$key] ?? null,
                $queue[$key] ?? null,
                "the queue must reach the same Redis as everything else ($key)"
            );
        }
    }

    /**
     * The connection is only half of it: the driver has to be the one that
     * retries. Asserted on the `redis` connection by name rather than through
     * config("broadcasting.queue_connection"), which phpunit.xml forces to
     * `sync`; that the deployed broadcast worker serves redis/broadcasts is
     * pinned in chart/tests/assertions.sh.
     */
    public function testTheRedisQueueIsTheFailoverAwareOne(): void
    {
        $this->assertInstanceOf(
            FailoverRedisQueue::class,
            Queue::connection("redis"),
            "the framework's RedisQueue reports a failover once a second for ever "
                . "instead of reconnecting"
        );
    }

    /**
     * Wrapping pop() is the whole fix only if pop() is the whole loop.
     *
     * Worker::daemon otherwise reaches the *cache* connection twice per
     * iteration — getNextJob() asking which queues are paused, and
     * stopIfNecessary() reading `illuminate:queue:restart` — and those are on
     * the shared 35s connection, unwrapped. App\Providers\AppServiceProvider
     * turns both off; nothing in this repo, the chart or CI sends either
     * signal.
     */
    public function testTheWorkerLoopDoesNotPollTheApplicationCache(): void
    {
        $this->assertFalse(
            Worker::$restartable,
            "the restart poll is an unwrapped cache read in every loop iteration"
        );
        $this->assertFalse(
            Worker::$pausable,
            "the pause poll is an unwrapped cache read in every loop iteration"
        );
    }
}
