<?php

namespace Tests\Unit;

use App\Console\Commands\RequestFetcher;
use App\Support\RedisFailover;
use Tests\TestCase;

/**
 * The fetch worker's Redis connection, and why it is not the default one.
 *
 * On 2026-09-10 draining the mailu pod triggered a CephFS kernel panic and the
 * node rebooted instantly. The Valkey master went with it — no preStop hook, no
 * socket close, no RST. `requests:fetcher` was sitting in a blpop on that
 * socket, and `read_write_timeout => -1` means Predis blocks in the read for
 * ever. No exception is raised, so App\Support\RedisFailover never sees a
 * failover to retry and the loop never comes back round; the process stays
 * alive, so pgrep called it healthy. Sentinel promoted a new master within
 * seconds and the worker never noticed. Search served empty result pages for
 * fifteen minutes.
 *
 * An instant node reboot is not survivable without *some* outage. Repairing
 * itself once Sentinel has promoted a new master is, and that is what these
 * invariants are for. Node drains happen several times a week, so this is the
 * normal case, not an exceptional one.
 *
 * @see \App\Support\FetcherHeartbeat  the backstop, for a wedge nothing here anticipates
 */
class FetcherRedisConnectionTest extends TestCase
{
    private function fetcherConnection(): array
    {
        return config("database.redis." . RequestFetcher::REDIS_CONNECTION);
    }

    /**
     * The one property that makes the failure detectable at all.
     */
    public function testTheFetcherReadTimeoutIsBounded(): void
    {
        $timeout = $this->fetcherConnection()["read_write_timeout"];

        $this->assertGreaterThan(
            0,
            $timeout,
            "an unbounded read timeout is the wedge: a dead peer never raises anything"
        );
        $this->assertLessThan(
            \App\Search\EngineOrchestrator::WAIT_SECONDS,
            $timeout,
            "a hiccup should resolve inside the time a search is already waiting"
        );
    }

    /**
     * Characterization, not aspiration: this one has to stay -1.
     *
     * Callers on the default connection make genuinely long blocking calls —
     * AnonymousToken blpops for 30s, AnonymousTokenPayment for a
     * caller-supplied duration, EngineOrchestrator brpops for WAIT_SECONDS — so
     * bounding it here would abort them mid-wait. That is the whole reason the
     * worker needed a connection of its own rather than a change to this one.
     */
    public function testTheDefaultConnectionStaysUnbounded(): void
    {
        $this->assertSame(
            -1,
            config("database.redis.default.read_write_timeout"),
            "bounding the default connection would cut off the 30s blpop callers"
        );
    }

    /**
     * The read timeout has to fit *inside* the retry budget, or the budget is
     * spent by the first timeout and the retry loop rethrows before ever making
     * an attempt on the connection it just reconnected — which is the attempt
     * that recovers. The default budget is 3s and the read timeout 3s, so the
     * worker cannot use the default: this is the pairing, and it is easy to
     * break by tuning either number alone.
     */
    public function testTheRetryBudgetOutlastsAReadTimeout(): void
    {
        $this->assertGreaterThan(
            $this->fetcherConnection()["read_write_timeout"],
            RequestFetcher::RETRY_BUDGET_SECONDS,
            "the budget must survive one timeout with room for a fresh attempt"
        );
        $this->assertGreaterThan(
            RedisFailover::BUDGET_SECONDS,
            RequestFetcher::RETRY_BUDGET_SECONDS,
            "the worker retries more patiently than a request does; nobody is waiting on it"
        );
    }

    /**
     * A reconnect is only useful if it is fast. This is paid exactly when the
     * far side may have vanished, so Predis' 5s default would make recovering
     * from a dead master slower than noticing it was dead.
     */
    public function testTheFetcherConnectTimeoutIsBounded(): void
    {
        $connect = $this->fetcherConnection()["timeout"];

        $this->assertGreaterThan(0, $connect);
        $this->assertLessThanOrEqual(
            $this->fetcherConnection()["read_write_timeout"],
            $connect,
            "opening a socket should not cost more than waiting on one"
        );
    }

    /**
     * Same server, same database — only the socket options differ. The worker
     * pushes onto the same lists fpm brpops from and caches into the same keys
     * the result page reads, so a connection pointing somewhere else would be a
     * search that silently never finds anything.
     */
    public function testTheFetcherTalksToTheSameRedisAsEveryoneElse(): void
    {
        $fetcher = $this->fetcherConnection();
        $default = config("database.redis.default");

        foreach (["host", "port", "database", "password"] as $key) {
            $this->assertSame(
                $default[$key] ?? null,
                $fetcher[$key] ?? null,
                "the fetcher must reach the same Redis as everything else ($key)"
            );
        }
    }

    /**
     * readMultiCurl caches each engine's body through the cache store, not the
     * Redis facade, so pointing the worker's own calls at the bounded
     * connection would have left this one call on the unbounded default — and
     * one call in the loop that can block for ever is all it takes to wedge the
     * loop. Its try/catch does nothing for a hang.
     */
    public function testTheFetcherCacheStoreUsesTheFetcherConnection(): void
    {
        $this->assertSame(
            RequestFetcher::REDIS_CONNECTION,
            config("cache.stores." . RequestFetcher::CACHE_STORE . ".connection"),
            "the worker's cached bodies would still be written over an unbounded connection"
        );
    }
}
