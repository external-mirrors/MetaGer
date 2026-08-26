<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Predis\ClientException;
use Prometheus\Exception\StorageException;
use Tests\TestCase;

/**
 * Redis backs almost everything a search does, so app/Exceptions/Handler.php
 * maps both Predis's exception hierarchy and Prometheus's storage exception
 * (its metrics backend is the same Redis) to a 503 with a short Retry-After —
 * see the class docblock there for the two production incidents
 * (GlitchTip METAGER-I/L, METAGER-K/H/E) this replaces an uncaught 500 for.
 *
 * Routes a throwing closure directly rather than reusing a real controller:
 * the point here is the exception-to-response mapping in the handler, not
 * any particular call site, and a real Redis-touching controller would only
 * add an unrelated way for the test to fail.
 */
class RedisOutageResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/__test/predis-outage', function () {
            throw new ClientException('No sentinel server available for autodiscovery.');
        });
        Route::get('/__test/prometheus-outage', function () {
            throw new StorageException("Can't connect to Redis server. Connection timed out");
        });
    }

    public function testAPredisExceptionBecomesA503(): void
    {
        $response = $this->get('/__test/predis-outage');

        $response->assertStatus(503);
    }

    public function testAPrometheusStorageExceptionBecomesA503(): void
    {
        $response = $this->get('/__test/prometheus-outage');

        $response->assertStatus(503);
    }

    public function testTheResponseCarriesAShortRetryAfter(): void
    {
        $response = $this->get('/__test/predis-outage');

        $response->assertHeader('Retry-After');
        $this->assertLessThanOrEqual(30, (int) $response->headers->get('Retry-After'));
    }

    /**
     * The no-JS fallback: errors/503.blade.php's meta-refresh has to work
     * with client JS disabled, so it has to be a real meta tag in the
     * markup, not something a script injects.
     */
    public function testTheHtmlResponseContainsAMetaRefresh(): void
    {
        $response = $this->get('/__test/predis-outage');

        $response->assertSee('http-equiv="refresh"', false);
    }

    /**
     * out=json / API clients get a JSON 503, not an HTML page — this is
     * Laravel's own request/exception content negotiation, exercised here
     * to confirm the mapping doesn't fight it (e.g. by rendering a view
     * unconditionally from inside the renderable() callback).
     */
    public function testAJsonRequestGetsAJsonBody(): void
    {
        $response = $this->getJson('/__test/predis-outage');

        $response->assertStatus(503);
        $response->assertJsonStructure(['message']);
    }
}
