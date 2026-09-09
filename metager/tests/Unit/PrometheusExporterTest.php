<?php

namespace Tests\Unit;

use App\PrometheusExporter;
use PHPUnit\Framework\Attributes\DataProvider;
use Predis\Connection\Resource\Exception\StreamInitException;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\StorageException;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\Adapter;
use ReflectionProperty;
use Tests\TestCase;

/**
 * `CollectorRegistry::getDefault()` memoises a single instance behind a
 * private static property, wired in AppServiceProvider to a raw
 * `Prometheus\Storage\Redis` adapter — not the Laravel Redis facade, so
 * nothing here can be faked with `Redis::shouldReceive()`. Reflection is the
 * only way to swap in a storage adapter that fails on demand.
 *
 * `LocalizationRedirect` calls `PrometheusExporter::LocaleDecision()` on
 * every single request, before routing, so an unguarded metrics failure here
 * is a broken page for every visitor on the site.
 *
 * This suite used to prove that with a fake adapter throwing
 * `Prometheus\Exception\StorageException`, and it passed for months while
 * production did exactly what it was supposed to prevent: the 2026-09-08 node
 * drains produced 118 production errors, 109 of them from this one class.
 * The adapter does not throw `StorageException`. It documents it —
 * `@throws StorageException` throughout `Storage\AbstractRedis` — and then
 * lets the driver's own exception through untouched, because
 * `Storage\RedisClients\PHPRedis::eval()` calls `\Redis::eval()` bare. So the
 * real shapes are ext-redis's `RedisException` (a `RuntimeException`) and
 * predis's `StreamInitException`, neither of which is a `StorageException`,
 * and the catch never fired once.
 *
 * Hence every failure mode below is driven with a *real* driver exception,
 * and StorageException is kept only as the third case rather than the only
 * one. A test that picks the exception class it wishes the library threw
 * proves nothing about the library.
 */
class PrometheusExporterTest extends TestCase
{
    private static ?CollectorRegistry $originalDefault = null;

    protected function setUp(): void
    {
        parent::setUp();
        $property = new ReflectionProperty(CollectorRegistry::class, 'defaultRegistry');
        $property->setAccessible(true);
        self::$originalDefault = $property->getValue();
    }

    protected function tearDown(): void
    {
        $property = new ReflectionProperty(CollectorRegistry::class, 'defaultRegistry');
        $property->setAccessible(true);
        $property->setValue(null, self::$originalDefault);

        parent::tearDown();
    }

    /**
     * @return list<array{0: string, 1: \Throwable}>
     */
    public static function driverExceptions(): array
    {
        return [
            // What ext-redis actually threw on 2026-09-08, straight out of
            // GlitchTip: the metrics adapter uses \Redis, not the Laravel
            // facade, so this is the shape that reaches PrometheusExporter.
            'ext-redis, master demoted mid-failover' => [
                'RedisException',
                new \RedisException("READONLY You can't write against a read only replica. script: on @user_script:1."),
            ],
            'ext-redis, connection cut' => [
                'RedisException',
                new \RedisException('socket error on read socket'),
            ],
            // The other shape from the same day, from the predis side: a
            // socket that could not be opened at all while the pod was going
            // down. Note it extends PredisException directly rather than
            // CommunicationException — see App\Support\RedisFailover.
            'predis, socket never opened' => [
                'StreamInitException',
                new StreamInitException('Connection refused [tcp://master-valkey-master:6379]'),
            ],
            // The one the guard used to catch, kept so the widened catch is
            // still proven to cover what the narrow one did.
            'the documented-but-unthrown one' => [
                'StorageException',
                new StorageException('simulated storage outage'),
            ],
        ];
    }

    private function installFailingRegistry(\Throwable $failure): void
    {
        $adapter = new class ($failure) implements Adapter {
            public function __construct(private \Throwable $failure)
            {
            }

            /** @return MetricFamilySamples[] */
            public function collect(): array
            {
                return [];
            }

            public function updateSummary(array $data): void
            {
                throw $this->failure;
            }

            public function updateHistogram(array $data): void
            {
                throw $this->failure;
            }

            public function updateGauge(array $data): void
            {
                throw $this->failure;
            }

            public function updateCounter(array $data): void
            {
                throw $this->failure;
            }

            public function wipeStorage(): void
            {
                throw $this->failure;
            }
        };

        $property = new ReflectionProperty(CollectorRegistry::class, 'defaultRegistry');
        $property->setAccessible(true);
        $property->setValue(null, new CollectorRegistry($adapter, false));
    }

    /**
     * The regression test for the drains. Driven with each real driver
     * exception rather than the one the library's docblocks promise.
     */
    #[DataProvider('driverExceptions')]
    public function testLocaleDecisionSurvivesEveryShapeOfStorageFailure(string $label, \Throwable $failure): void
    {
        $this->installFailingRegistry($failure);

        // No exception means the guard held; PHPUnit reports an uncaught one
        // as an error on its own.
        PrometheusExporter::LocaleDecision("no_redirect");

        $this->assertTrue(true, "LocaleDecision must not propagate a {$label}");
    }

    /**
     * `LocaleDecision` was the only method that had ever been guarded, which
     * was itself the bug: `Duration` and `PreferredLanguage` are on the search
     * path, `KeyUsed` and `SuggestionResult` on the key and suggestion paths,
     * and all of them were one blip away from being the next incident. Every
     * public method has to hold, so every public method is exercised.
     */
    #[DataProvider('driverExceptions')]
    public function testNoExporterMethodPropagatesAStorageFailure(string $label, \Throwable $failure): void
    {
        $this->installFailingRegistry($failure);

        PrometheusExporter::Duration(0.5, "Search_Total");
        PrometheusExporter::PreferredLanguage("de", ["browser"]);
        PrometheusExporter::OvertureFail();
        PrometheusExporter::KeyUsed(1.0, "suggestions", true);
        PrometheusExporter::UpdateKeyStatus("key", 10, "owner");
        PrometheusExporter::CreditcardDonation("started");
        PrometheusExporter::SuggestionResult("200");
        PrometheusExporter::LocaleDecision("no_redirect");
        PrometheusExporter::SuggestionSessionCounter();

        $this->assertTrue(true, "no exporter method may propagate a {$label}");
    }
}
