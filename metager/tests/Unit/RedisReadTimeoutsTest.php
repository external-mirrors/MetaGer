<?php

namespace Tests\Unit;

use App\Http\Controllers\AnonymousToken;
use App\Search\EngineOrchestrator;
use Tests\TestCase;

/**
 * No Redis connection may block in a read for ever.
 *
 * `read_write_timeout` governs waiting for a *reply*, so it applies to every
 * command and not only the blocking ones: on a socket whose peer has vanished —
 * a node rebooted by a kernel panic, taking the Valkey master with it and
 * closing nothing — even a plain GET never returns. There is no exception, so
 * nothing retries, reconnects or reports; the caller simply stops.
 *
 * For web traffic that was survivable, but only by accident of php-fpm's
 * `request_terminate_timeout = 30` killing the child. Two things people reach
 * for do not help and are worth naming: `max_execution_time` explicitly does
 * not count time spent in stream operations, and nginx's `fastcgi_read_timeout`
 * only stops nginx waiting — the fpm child keeps its pool slot. The development
 * pool sets `request_terminate_timeout = 0`, so there nothing collected them at
 * all.
 *
 * Nothing bounded the daemons: the fetch worker hung for fifteen minutes on
 * 2026-09-10 and had to be restarted by hand.
 *
 * @see \Tests\Unit\FetcherRedisConnectionTest  the tight connection, for the worker that can afford one
 */
class RedisReadTimeoutsTest extends TestCase
{
    /**
     * Looped rather than a data provider: a provider is static and runs before
     * the application boots, so config() is empty there and the check would
     * silently cover nothing.
     */
    public function testEveryConnectionBoundsItsReads(): void
    {
        $checked = [];

        foreach (config("database.redis") as $name => $config) {
            // 'client' is a driver name, not a connection.
            if (!is_array($config)) {
                continue;
            }

            // The sentinel connection is a list of sentinel hosts plus an
            // 'options' block, and its per-node parameters are nested; it has
            // no top-level read timeout to check. It is also reached by
            // nothing today (REDIS_CACHE_CONNECTION is 'default' in every
            // deployed environment).
            if (array_key_exists("options", $config)) {
                continue;
            }

            $this->assertArrayHasKey(
                "read_write_timeout",
                $config,
                "connection '$name' sets no read timeout, so it inherits "
                    . "default_socket_timeout and a dead peer hangs it for a minute"
            );
            $this->assertGreaterThan(
                0,
                $config["read_write_timeout"],
                "connection '$name' can block in a read for ever; "
                    . "a vanished peer raises nothing at all"
            );
            $checked[] = $name;
        }

        // Guards the loop itself: a config reshuffle that skipped every
        // connection would otherwise pass without asserting anything.
        $this->assertContains("default", $checked);
        $this->assertContains("fetcher", $checked);
    }

    /**
     * The shared connection's timeout is not a free choice: it has to clear the
     * longest blocking read made on it, or that caller starts failing with a
     * Predis\TimeoutException while waiting exactly as designed.
     *
     * This is the pairing that makes the number 35 rather than something
     * tighter, and the reason the fetch worker needed a connection of its own.
     */
    public function testTheDefaultConnectionClearsItsLongestBlockingRead(): void
    {
        $timeout = config("database.redis.default.read_write_timeout");

        foreach (
            [
                "AnonymousToken::GET_PAYMENT" => AnonymousToken::PAYMENT_WAIT_SECONDS,
                "the 10s brpops in Suggestions/KeyAuthorization/PayPal/CiviCrm" => 10,
                "EngineOrchestrator::waitForMainResults" => EngineOrchestrator::WAIT_SECONDS,
            ] as $caller => $blockingWait
        ) {
            $this->assertGreaterThan(
                $blockingWait,
                $timeout,
                "$caller blocks for {$blockingWait}s, longer than the read timeout allows"
            );
        }
    }

    /**
     * php-fpm bounds a production request at 30s, so the shared timeout is not
     * what protects web traffic — it protects everything that is not a request.
     * Pinned so that lowering the fpm limit does not silently make the read
     * timeout the thing that fires first on the payment path.
     */
    public function testTheDefaultTimeoutIsNotRelyingOnFpmToCollectIt(): void
    {
        $this->assertGreaterThan(
            AnonymousToken::PAYMENT_WAIT_SECONDS,
            config("database.redis.default.read_write_timeout"),
            "the payment wait and the read timeout must not collide"
        );
    }
}
