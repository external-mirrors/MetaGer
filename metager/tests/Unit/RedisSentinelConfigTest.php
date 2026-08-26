<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * config/database.php's `redis.sentinel` list is what Predis tries hosts
 * from — one at a time, giving up with "No sentinel server available for
 * autodiscovery" the instant whichever one it is currently trying fails to
 * connect (see App\MetaGer's PredisException catch: Predis does not retry
 * that particular exception). With a single entry, any transient blip
 * reaching it is fatal for the whole request even though the other sentinel
 * replicas are healthy (GlitchTip METAGER-I/L). REDIS_SENTINEL_HOSTS lets
 * the chart list every replica's own stable per-pod DNS name so one bad
 * connection just moves on to the next entry instead.
 *
 * Booted (Tests\TestCase, not PHPUnit's) because config/database.php calls
 * database_path(), which needs the container.
 */
class RedisSentinelConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('REDIS_SENTINEL_HOSTS');
        putenv('REDIS_SENTINEL_HOST');
        parent::tearDown();
    }

    private function sentinelListFor(string $hostsEnv, ?string $singleHostEnv = null): array
    {
        putenv("REDIS_SENTINEL_HOSTS={$hostsEnv}");
        if ($singleHostEnv !== null) {
            putenv("REDIS_SENTINEL_HOST={$singleHostEnv}");
        }

        // config/database.php reads env() directly, so the file has to be
        // re-evaluated after putenv() rather than reading the cached
        // config() value from application boot.
        $config = require base_path('config/database.php');

        return $config['redis']['sentinel'];
    }

    public function testACommaSeparatedListBecomesOneEntryPerHost(): void
    {
        $sentinels = $this->sentinelListFor('sentinel-0.example,sentinel-1.example,sentinel-2.example');

        $hosts = array_column(
            array_filter($sentinels, fn($k) => $k !== 'options', ARRAY_FILTER_USE_KEY),
            'host'
        );

        $this->assertSame(
            ['sentinel-0.example', 'sentinel-1.example', 'sentinel-2.example'],
            $hosts
        );
    }

    public function testAPerHostPortOverridesTheDefault(): void
    {
        $sentinels = $this->sentinelListFor('sentinel-0.example:26380');

        $this->assertSame('sentinel-0.example', $sentinels[0]['host']);
        $this->assertSame(26380, $sentinels[0]['port']);
    }

    public function testAHostWithoutAPortUsesTheDefault(): void
    {
        $sentinels = $this->sentinelListFor('sentinel-0.example');

        $this->assertSame(26379, $sentinels[0]['port']);
    }

    /**
     * A fresh docker-compose checkout sets only REDIS_SENTINEL_HOST (see
     * .env.example) and never sets REDIS_SENTINEL_HOSTS at all — this must
     * keep working exactly as before.
     */
    public function testAnUnsetHostsListFallsBackToTheSingleHostVariable(): void
    {
        putenv('REDIS_SENTINEL_HOSTS');
        putenv('REDIS_SENTINEL_HOST=localhost');
        $config = require base_path('config/database.php');
        $sentinels = $config['redis']['sentinel'];

        $this->assertCount(1, array_filter($sentinels, fn($k) => $k !== 'options', ARRAY_FILTER_USE_KEY));
        $this->assertSame('localhost', $sentinels[0]['host']);
    }
}
