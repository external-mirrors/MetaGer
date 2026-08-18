<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The Redis connections the suite is about to run against are usable.
 *
 * This exists because of how badly the alternative reads. CI copies a
 * production .env and runs `php artisan optimize`, which bakes it into
 * bootstrap/cache/config.php — and cached config is never re-read through
 * env(), so the values phpunit.xml declares are silently ignored. When that
 * .env pointed the cache connection at `sentinel`, nothing said so. Every
 * search instead threw "No sentinel server available for autodiscovery" deep
 * inside Predis, logged a full stack trace, and the accumulated traces
 * exhausted the 256M memory_limit partway through the suite. The reported
 * failure was "Allowed memory size exhausted" inside a YAML parser, in a test
 * that has nothing to do with Redis.
 *
 * So these tests do not check behaviour — they check that the environment can
 * support the behaviour the rest of the suite assumes, and fail by name when it
 * cannot. A misconfigured pipeline should say which connection is wrong.
 *
 * Note that CACHE_STORE=array does not make the suite Redis-free. It only
 * redirects Cache::; the authorization path, the anonymous-token payments and
 * the suggestion debt all reach Redis directly through
 * `Redis::connection(config("cache.stores.redis.connection"))`, and the search
 * path uses the bare facade. Both connections have to work.
 */
class RedisConnectionsTest extends TestCase
{
    /**
     * Every connection the application actually opens, and what uses it.
     *
     * @return array<string, array{0: ?string, 1: string}>
     */
    public static function connectionProvider(): array
    {
        return [
            "default" => [null, "the search path: rpush/brpop/lpush/expire in EngineOrchestrator and Quicktips"],
            "cache store" => ["cache.stores.redis.connection", "KeyUser claims, anonymous-token payments, suggestion debt"],
        ];
    }

    #[DataProvider("connectionProvider")]
    public function testTheConnectionAnswers(?string $configKey, string $usedBy): void
    {
        $name = $configKey === null ? null : config($configKey);

        $connection = Redis::connection($name);

        $this->assertSame(
            "PONG",
            (string) $connection->ping(),
            sprintf(
                "The Redis connection %s did not answer. It is used by %s.",
                $name === null ? "[default]" : "[" . $name . "]",
                $usedBy
            )
        );
    }

    /**
     * Sentinel is a deployment topology, not a test one.
     *
     * The app's sentinel-aware connection exists for production HA, where
     * Sentinel monitors are actually running. A test environment has a single
     * Redis, so a connection configured for sentinel replication cannot resolve
     * a master and every call through it throws. Catching that here names the
     * variable to set — REDIS_CACHE_CONNECTION — instead of leaving a stack
     * trace in Predis to be interpreted.
     *
     * `default` is also what every deployed environment sets this to; see the
     * master-proxy reasoning in chart/values.yaml.
     */
    public function testTheCacheConnectionIsNotSentinelReplicated(): void
    {
        $name = config("cache.stores.redis.connection");
        $replication = config("database.redis." . $name . ".options.replication");

        $this->assertNotSame(
            "sentinel",
            $replication,
            "cache.stores.redis.connection points at [" . $name . "], which is configured for sentinel "
                . "replication. Nothing in the test environment runs a Sentinel monitor, so every call the "
                . "authorization path makes through this connection will throw. Set REDIS_CACHE_CONNECTION=default."
        );
    }
}
