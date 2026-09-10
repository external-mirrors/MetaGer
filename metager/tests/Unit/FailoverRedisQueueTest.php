<?php

namespace Tests\Unit;

use App\Queue\FailoverRedisQueue;
use Illuminate\Contracts\Redis\Factory;
use Illuminate\Support\Facades\Redis;
use Predis\Connection\NodeConnectionInterface;
use Predis\Response\ServerException;
use Predis\TimeoutException;
use Tests\TestCase;
use Throwable;

/**
 * What a Valkey failover does to `queue:work`, and what it should do instead.
 *
 * Unwrapped, a failing pop() is caught by Worker::getNextJob, reported, slept
 * on for a second and made again — on the same pooled socket, because `redis`
 * is a container singleton and a daemon holds its connection for the life of
 * the pod. Nothing in that loop drops the socket, and Predis'
 * "Operation has timed out" is not in Laravel's lost-connection list (that list
 * is entirely PDO messages), so the worker does not quit to be replaced either.
 * Recovery waited on the --max-time recycle: up to five minutes of balance
 * updates pushed and never delivered.
 *
 * Nothing here talks to Redis. The queue is handed a stubbed connection that
 * throws what a failover throws, so the driver's behaviour can be pinned
 * without a cluster to fail over; the Redis facade is swapped separately,
 * because that is what RedisFailover reconnects through.
 *
 * @see \Tests\Unit\QueueRedisConnectionTest  the timeouts that make this reachable
 * @see \Tests\Unit\RedisFailoverTest         the retry semantics themselves
 */
class FailoverRedisQueueTest extends TestCase
{
    /**
     * The name the driver is configured with, and so the one it must reconnect.
     * Reconnecting any other would leave the poisoned socket in the pool.
     */
    private const CONNECTION = "queue";

    private function timeoutException(): TimeoutException
    {
        return new TimeoutException($this->createMock(NodeConnectionInterface::class));
    }

    /**
     * A connection that throws $failure on its first $failures eval() calls and
     * then behaves like an empty queue.
     *
     * eval() is every Redis call RedisQueue::pop() makes: two
     * migrateExpiredJobs scripts and then the pop itself. isCluster() is asked
     * once, before any of them.
     */
    private function connectionFailing(int $failures, Throwable $failure): object
    {
        return new class ($failures, $failure) {
            public int $evals = 0;

            public function __construct(private int $failures, private Throwable $failure) {}

            public function isCluster(): bool
            {
                return false;
            }

            public function eval(...$arguments): mixed
            {
                $this->evals++;

                if ($this->evals <= $this->failures) {
                    throw $this->failure;
                }

                // Empty reply: nothing to migrate, no job to reserve.
                return null;
            }
        };
    }

    private function queueOver(object $connection): FailoverRedisQueue
    {
        $factory = new class ($connection) implements Factory {
            public function __construct(private object $connection) {}

            public function connection($name = null)
            {
                return $this->connection;
            }
        };

        return new FailoverRedisQueue(
            $factory,
            "broadcasts",
            self::CONNECTION,
            retryAfter: 90,
            blockFor: null,
            dispatchAfterCommit: false,
            migrationBatchSize: -1
        );
    }

    /**
     * Records every connection RedisFailover drops, in order.
     *
     * @param array<int, string|null> $disconnects
     */
    private function recordDisconnectsInto(array &$disconnects): void
    {
        Redis::swap(new class ($disconnects) {
            /** @param array<int, string|null> $disconnects */
            public function __construct(private array &$disconnects) {}

            public function connection(?string $name = null): mixed
            {
                return new class ($this->disconnects, $name) {
                    /** @param array<int, string|null> $disconnects */
                    public function __construct(private array &$disconnects, private ?string $name) {}

                    public function client(): mixed
                    {
                        return new class ($this->disconnects, $this->name) {
                            /** @param array<int, string|null> $disconnects */
                            public function __construct(private array &$disconnects, private ?string $name) {}

                            public function disconnect(): void
                            {
                                $this->disconnects[] = $this->name;
                            }
                        };
                    }
                };
            }
        });
    }

    /**
     * The shape of 2026-09-10: the master's node rebooted without closing a
     * socket, so the read timed out rather than erroring. One pop, one
     * reconnect, no exception reaching the worker at all.
     */
    public function testAPopSurvivesATimedOutRead(): void
    {
        $connection = $this->connectionFailing(1, $this->timeoutException());
        $disconnects = [];
        $this->recordDisconnectsInto($disconnects);

        $this->assertNull(
            $this->queueOver($connection)->pop("broadcasts"),
            "an empty queue after a recovered failover is still an empty queue"
        );

        $this->assertGreaterThan(
            1,
            $connection->evals,
            "the pop must be attempted again, or the worker only reports the failure"
        );
        $this->assertSame(
            [self::CONNECTION],
            $disconnects,
            "the retry must drop the poisoned socket, and it must be the queue's own"
        );
    }

    /**
     * The shape of the 2026-09-08 drains: the pop's Lua script is a write, so a
     * node Sentinel demoted underneath it answers -READONLY rather than dying.
     */
    public function testAPopSurvivesAReadonlyReply(): void
    {
        $connection = $this->connectionFailing(
            1,
            new ServerException("READONLY You can't write against a read only replica.")
        );
        $disconnects = [];
        $this->recordDisconnectsInto($disconnects);

        $this->assertNull($this->queueOver($connection)->pop("broadcasts"));
        $this->assertSame([self::CONNECTION], $disconnects);
    }

    /**
     * A retry is only worth making for something that passes on its own. A
     * WRONGTYPE is a bug in the caller, and retrying it for twelve seconds
     * would turn an instant, obvious failure into a slow one — the worker would
     * report it a fifth as often, which is worse, not better.
     */
    public function testAnOrdinaryServerErrorIsNotRetried(): void
    {
        $connection = $this->connectionFailing(
            PHP_INT_MAX,
            new ServerException("WRONGTYPE Operation against a key holding the wrong kind of value")
        );
        $disconnects = [];
        $this->recordDisconnectsInto($disconnects);

        try {
            $this->queueOver($connection)->pop("broadcasts");
            $this->fail("a WRONGTYPE should have reached the worker");
        } catch (ServerException $expected) {
            $this->assertStringContainsString("WRONGTYPE", $expected->getMessage());
        }

        $this->assertSame(1, $connection->evals, "a caller bug must fail on the first attempt");
        $this->assertSame([], $disconnects, "a healthy connection must not be dropped");
    }

    /**
     * The pop is retried, not re-derived: the queue name it was called with has
     * to survive, or a retry would silently start draining the default queue.
     */
    public function testTheRetryPopsTheSameQueue(): void
    {
        $keys = [];

        $connection = new class ($keys, $this->timeoutException()) {
            public int $evals = 0;

            /** @param array<int, string> $keys */
            public function __construct(private array &$keys, private Throwable $failure) {}

            public function isCluster(): bool
            {
                return false;
            }

            public function eval(...$arguments): mixed
            {
                $this->evals++;

                // eval(script, numKeys, ...keys, ...argv)
                foreach (array_slice($arguments, 2, (int) $arguments[1]) as $key) {
                    $this->keys[] = $key;
                }

                if ($this->evals === 1) {
                    throw $this->failure;
                }

                return null;
            }
        };

        $disconnects = [];
        $this->recordDisconnectsInto($disconnects);

        $this->queueOver($connection)->pop("broadcasts");

        $this->assertNotEmpty($keys);
        foreach ($keys as $key) {
            $this->assertStringStartsWith(
                "queues:broadcasts",
                $key,
                "the retry must pop the queue the worker asked for"
            );
        }
    }
}
