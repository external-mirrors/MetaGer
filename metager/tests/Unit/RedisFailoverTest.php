<?php

namespace Tests\Unit;

use App\Support\RedisFailover;
use Predis\ClientException;
use Predis\Connection\ConnectionException;
use Predis\Connection\NodeConnectionInterface;
use Predis\Connection\Resource\Exception\StreamInitException;
use Predis\Response\ServerException;
use Predis\TimeoutException;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * The window RedisFailover exists to cover is the ~1-2s of a Sentinel
 * promotion in which no node in the cluster will accept a write. See the
 * class docblock for why nothing on the server side can close it.
 *
 * Nothing here talks to Redis: the operation is a closure that throws what a
 * failover throws, so the retry semantics can be pinned without a cluster to
 * fail over. The one Redis-shaped thing the helper does — dropping the pooled
 * connection between attempts — is best-effort and swallows its own errors,
 * which is what lets these run under `artisan test` with no server at all.
 */
class RedisFailoverTest extends TestCase
{
    private function connectionException(): ConnectionException
    {
        return new ConnectionException(
            $this->createMock(NodeConnectionInterface::class),
            'Error while reading line from the server.'
        );
    }

    private function timeoutException(): TimeoutException
    {
        return new TimeoutException($this->createMock(NodeConnectionInterface::class));
    }

    /**
     * The exact shape of the 2026-09-08 drains: a write lands on a node
     * Sentinel demoted while the pause was holding it.
     */
    public function testARedisReadonlyReplyIsRetriedRatherThanAnswered(): void
    {
        $attempts = 0;

        $result = RedisFailover::retry(function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                throw new ServerException("READONLY You can't write against a read only replica.");
            }

            return 'written';
        });

        $this->assertSame('written', $result);
        $this->assertSame(2, $attempts, 'the operation should have been retried exactly once');
    }

    /**
     * HAProxy closes client connections to a node it has seen demoted, so the
     * first symptom of a failover is often the socket dying rather than an
     * error reply.
     */
    public function testALostConnectionIsRetried(): void
    {
        $attempts = 0;

        $result = RedisFailover::retry(function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                throw $this->connectionException();
            }

            return 'written';
        });

        $this->assertSame('written', $result);
        $this->assertSame(3, $attempts);
    }

    /**
     * The shape a drain produces while the pod is actually going away:
     * "Connection refused", then "No route to host" once it has gone.
     *
     * Worth its own test because `StreamInitException` extends
     * `PredisException` *directly* — it is not a `CommunicationException`, so
     * the obvious retry check misses it and rethrows on the first attempt,
     * exactly where a retry was most warranted.
     */
    public function testASocketThatNeverOpenedIsRetried(): void
    {
        $attempts = 0;

        RedisFailover::retry(function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                throw new StreamInitException('Connection refused [tcp://master-valkey-master:6379]');
            }

            return null;
        });

        $this->assertSame(2, $attempts);
    }

    /**
     * The sibling exception Predis throws when its sentinel list is exhausted
     * — neither a CommunicationException nor something Predis retries itself
     * (GlitchTip METAGER-I/L).
     */
    public function testAnExhaustedSentinelListIsRetried(): void
    {
        $attempts = 0;

        RedisFailover::retry(function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                throw new ClientException('No sentinel server available for autodiscovery.');
            }

            return null;
        });

        $this->assertSame(2, $attempts);
    }

    /**
     * The shape a Valkey master produces when its node dies outright rather
     * than being drained: the pod goes without a preStop hook, so nothing
     * closes the socket and nothing sends an RST. Reads into it simply never
     * answer.
     *
     * There is no such exception on the `default` connection, whose
     * read_write_timeout is -1 — that read blocks for ever, which is how the
     * fetch worker wedged on 2026-09-10. It only becomes a retryable error at
     * all because RequestFetcher::REDIS_CONNECTION bounds the read; see
     * FetcherRedisConnectionTest.
     */
    public function testATimedOutReadIsRetried(): void
    {
        $attempts = 0;

        $result = RedisFailover::retry(function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                throw $this->timeoutException();
            }

            return 'written';
        });

        $this->assertSame('written', $result);
        $this->assertSame(2, $attempts, 'a read that timed out should be retried on a fresh connection');
    }

    /**
     * Giving up must still drop the socket.
     *
     * For a request this barely matters, since the process is about to end. For
     * a long-lived daemon it is the whole difference between recovering and
     * not: the manager pools the connection, so the next loop iteration would
     * make its calls on the same dead socket, time out again, give up again,
     * for ever. That is what turned one lost Valkey master into fifteen minutes
     * of empty result pages on 2026-09-10 — and it is reachable precisely when
     * a single timeout is longer than the whole budget, which is the normal
     * case for a bounded read timeout.
     */
    public function testGivingUpStillDropsTheConnection(): void
    {
        $disconnects = [];

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

        try {
            RedisFailover::retry(
                fn() => throw $this->timeoutException(),
                // Shorter than a single attempt would take in production, which
                // is the point: the budget is gone before any retry is possible.
                0.01,
                'fetcher'
            );
            $this->fail('a persistent timeout should still have been rethrown');
        } catch (TimeoutException $expected) {
            // The rethrow is covered elsewhere; what matters is the side effect.
        }

        $this->assertSame(
            ['fetcher'],
            $disconnects,
            'the connection must be dropped on the way out, and it must be the named one'
        );
    }

    /**
     * A retry is only ever worth making for something that will pass on its
     * own. A WRONGTYPE is a bug in the caller: retrying it would turn an
     * instant, obvious failure into a slow one.
     */
    public function testAnOrdinaryServerErrorIsNotRetried(): void
    {
        $attempts = 0;

        $this->expectException(ServerException::class);

        try {
            RedisFailover::retry(function () use (&$attempts) {
                $attempts++;
                throw new ServerException('WRONGTYPE Operation against a key holding the wrong kind of value');
            });
        } finally {
            $this->assertSame(1, $attempts, 'a non-failover error should fail on the first attempt');
        }
    }

    /**
     * When the failover really is not coming back, the caller has to get its
     * exception — bootstrap/app.php turns it into a 503 with a meta-refresh,
     * which is a far better answer than a request that never returns.
     */
    public function testAPersistentFailureGivesUpAndRethrows(): void
    {
        $this->expectException(ServerException::class);

        RedisFailover::retry(
            fn() => throw new ServerException('READONLY You can\'t write against a read only replica.'),
            0.3
        );
    }

    /**
     * The budget is what keeps this inside the promise the page has already
     * made the user (EngineOrchestrator::WAIT_SECONDS), so it has to bound the
     * total wall time, not merely the number of attempts.
     */
    public function testGivingUpHappensWithinTheBudget(): void
    {
        $budget = 0.3;
        $start = microtime(true);

        try {
            RedisFailover::retry(
                fn() => throw new ServerException('READONLY nope'),
                $budget
            );
        } catch (ServerException $expected) {
            // The point is how long it took, asserted below.
        }

        $elapsed = microtime(true) - $start;
        $this->assertLessThan(
            $budget + 0.2,
            $elapsed,
            'retrying must not overrun the budget it was given'
        );
    }

    /**
     * The overwhelmingly common case: nothing is failing over, and the helper
     * has to be free.
     */
    public function testASuccessfulOperationRunsExactlyOnce(): void
    {
        $attempts = 0;

        $result = RedisFailover::retry(function () use (&$attempts) {
            $attempts++;

            return 'first time';
        });

        $this->assertSame('first time', $result);
        $this->assertSame(1, $attempts);
    }
}
