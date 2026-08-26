<?php

namespace Tests\Unit;

use App\PrometheusExporter;
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
 * every single request, before routing. GlitchTip METAGER-K/H/E show its
 * counter increment throwing `Prometheus\Exception\StorageException` when the
 * Redis backing Prometheus metrics has a connectivity blip — which, unguarded,
 * turns a metrics-storage hiccup into a 500 for every visitor on the site.
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

    private function installFailingRegistry(): void
    {
        $adapter = new class implements Adapter {
            /** @return MetricFamilySamples[] */
            public function collect(): array
            {
                return [];
            }

            public function updateSummary(array $data): void
            {
                throw new StorageException("simulated storage outage");
            }

            public function updateHistogram(array $data): void
            {
                throw new StorageException("simulated storage outage");
            }

            public function updateGauge(array $data): void
            {
                throw new StorageException("simulated storage outage");
            }

            public function updateCounter(array $data): void
            {
                throw new StorageException("simulated storage outage");
            }

            public function wipeStorage(): void
            {
                throw new StorageException("simulated storage outage");
            }
        };

        $property = new ReflectionProperty(CollectorRegistry::class, 'defaultRegistry');
        $property->setAccessible(true);
        $property->setValue(null, new CollectorRegistry($adapter, false));
    }

    public function testLocaleDecisionSurvivesAStorageOutageInsteadOf500ingTheRequest(): void
    {
        $this->installFailingRegistry();

        // No exception means the fix held; PHPUnit would report an uncaught
        // StorageException as an error on its own.
        PrometheusExporter::LocaleDecision("no_redirect");
        $this->assertTrue(true);
    }
}
