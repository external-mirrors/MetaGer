<?php

namespace Tests\Unit;

use App\Support\SentryEventScrubber;
use PHPUnit\Framework\TestCase;

/**
 * config/sentry.php is regenerated wholesale by `php artisan sentry:publish` --
 * an SDK upgrade that reruns it, or a careless manual edit, would silently drop
 * the scrubber wiring and the request-body cutoff with nothing to fail. This
 * cross-checks the published config the way tests/Feature/AssetPipelineTest
 * cross-checks the Vite manifest.
 */
class SentryConfigWiringTest extends TestCase
{
    public function testTheScrubberIsWiredIntoErrorAndTransactionEvents(): void
    {
        $config = require __DIR__ . '/../../config/sentry.php';

        $this->assertSame([SentryEventScrubber::class, 'scrub'], $config['before_send']);
        $this->assertSame([SentryEventScrubber::class, 'scrub'], $config['before_send_transaction']);
    }

    public function testRequestBodyCaptureStaysOff(): void
    {
        $config = require __DIR__ . '/../../config/sentry.php';

        $this->assertSame('none', $config['max_request_body_size']);
    }

    public function testCacheAndHttpClientBreadcrumbsAndSpansStayOffByDefault(): void
    {
        $config = require __DIR__ . '/../../config/sentry.php';

        $this->assertFalse($config['breadcrumbs']['cache']);
        $this->assertFalse($config['breadcrumbs']['http_client_requests']);
        $this->assertFalse($config['tracing']['cache']);
        $this->assertFalse($config['tracing']['http_client_requests']);
    }
}
