<?php

namespace Tests\Support;

use Predis\Response\ServerException;

/**
 * A Redis manager that answers the first attempt at a command the way a node
 * being drained does, and behaves normally from the second attempt on.
 *
 * This is the drain reproduced at the only point a test can reach it. During
 * the 2026-09-08 windows, HAProxy kept routing writes to a master that
 * `CLIENT PAUSE 22000 WRITE` was holding, and when Sentinel demoted it the
 * queued writes came back `-READONLY`. Predis surfaces that as a
 * `ServerException`, which its own retry layer does not catch — so without
 * App\Support\RedisFailover it reaches the user.
 *
 * Wrapped the same way as {@see RecordingRedis} and for the same reason: the
 * fetch-queue push is the command the fake fetcher intercepts, and it is one
 * of the commands that has to survive.
 */
class FailingOverRedis
{
    /** @var array<string, int> */
    private array $failuresLeft = [];

    /**
     * @param array<string, int> $failures Command name => how many times it
     *        should fail before succeeding.
     */
    public function __construct(private object $inner, array $failures)
    {
        foreach ($failures as $command => $times) {
            $this->failuresLeft[strtolower($command)] = $times;
        }
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        $this->failIfDue($method);

        return $this->inner->{$method}(...$arguments);
    }

    public function connection(?string $name = null): mixed
    {
        return new class ($this->inner->connection($name), $this) {
            public function __construct(private object $inner, private FailingOverRedis $failer) {}

            /**
             * @param array<int, mixed> $arguments
             */
            public function __call(string $method, array $arguments): mixed
            {
                $this->failer->failIfDue($method);

                return $this->inner->{$method}(...$arguments);
            }
        };
    }

    /**
     * Public because the per-connection wrapper above has to reach it; the
     * authorization path takes a connection out of the manager rather than
     * going through the facade.
     */
    public function failIfDue(string $method): void
    {
        $method = strtolower($method);

        if (($this->failuresLeft[$method] ?? 0) <= 0) {
            return;
        }

        $this->failuresLeft[$method]--;

        throw new ServerException(
            "READONLY You can't write against a read only replica."
        );
    }

    /**
     * Whether every failure asked for actually happened. A test that thinks it
     * simulated a failover and did not would otherwise pass for the wrong
     * reason — the command it named might simply never be issued.
     */
    public function allFailuresDelivered(): bool
    {
        foreach ($this->failuresLeft as $left) {
            if ($left > 0) {
                return false;
            }
        }

        return true;
    }
}
