<?php

namespace Tests\Unit;

use App\Support\SentryEventScrubber;
use PHPUnit\Framework\TestCase;
use Sentry\Event;

/**
 * Unit tests for SentryEventScrubber::scrub().
 *
 * Deliberately extends PHPUnit's TestCase rather than Tests\TestCase: the method
 * under test is static and pure, so booting the framework would only make the
 * Unit suite slow for no gain.
 */
class SentryEventScrubberTest extends TestCase
{
    public function testItStripsTheQueryStringFromTheRequestUrl(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'url' => 'https://metager.org/de-DE/meta/meta.ger3?eingabe=my+private+search&ff=2024-01-01',
            'method' => 'GET',
        ]);

        $scrubbed = SentryEventScrubber::scrub($event)->getRequest();

        $this->assertSame('https://metager.org/de-DE/meta/meta.ger3', $scrubbed['url']);
    }

    public function testItRemovesTheQueryStringField(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'url' => 'https://metager.org/de-DE/meta/meta.ger3?eingabe=my+private+search',
            'query_string' => 'eingabe=my+private+search',
        ]);

        $scrubbed = SentryEventScrubber::scrub($event)->getRequest();

        $this->assertArrayNotHasKey('query_string', $scrubbed);
    }

    public function testItRemovesCapturedRequestBodyData(): void
    {
        $event = Event::createEvent();
        $event->setRequest([
            'url' => 'https://metager.org/de-DE/meta/settings',
            'data' => ['blacklist' => 'example.com'],
        ]);

        $scrubbed = SentryEventScrubber::scrub($event)->getRequest();

        $this->assertArrayNotHasKey('data', $scrubbed);
    }

    public function testItToleratesAnEventWithNoRequestAttached(): void
    {
        $event = Event::createEvent();

        $scrubbed = SentryEventScrubber::scrub($event)->getRequest();

        $this->assertSame([], $scrubbed);
    }
}
