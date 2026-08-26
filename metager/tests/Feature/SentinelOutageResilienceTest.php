<?php

namespace Tests\Feature;

use App\Http\Controllers\Pictureproxy;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Predis\ClientException;
use Tests\TestCase;

/**
 * With only one sentinel configured (config/database.php lists the k8s
 * Service, not each replica — see chart/templates/_helpers.tpl's
 * REDIS_SENTINEL_HOST), a single failed connection attempt exhausts Predis's
 * sentinel list. `SentinelReplication::getSentinelConnection()` then throws
 * `Predis\ClientException` ("No sentinel server available for
 * autodiscovery"), a *sibling* of `Predis\Connection\ConnectionException`
 * under the shared `Predis\PredisException` base — and Predis's own retry
 * layer only retries `CommunicationException` (the connection one), so this
 * one is never retried either. It reached production twice (GlitchTip
 * METAGER-I/L) with the valkey/sentinel deployment itself perfectly healthy:
 * a transient blip in reaching the Service, not an outage.
 *
 * Two call sites read the cache without expecting that particular exception
 * shape. Both are simulated the same way: a real `ArrayStore` wrapped so one
 * specific key throws like a sentinel hiccup would, everything else behaving
 * like an ordinary cache. `Cache::partialMock()` was tried first and doesn't
 * work here — CacheManager's Mockery partial mock loses the container
 * reference some drivers need mid-resolution — so this drives it through the
 * real store/driver plumbing instead.
 */
class SentinelOutageResilienceTest extends TestCase
{
    private function throwingOn(string $key): ArrayStore
    {
        return new class ($key) extends ArrayStore {
            public function __construct(private string $failingKey)
            {
                parent::__construct();
            }

            public function get($key)
            {
                if ($key === $this->failingKey) {
                    throw new ClientException('No sentinel server available for autodiscovery.');
                }

                return parent::get($key);
            }
        };
    }

    /**
     * MetaGer::__construct() already tried to guard this ("Cachebarkeit
     * testen") but only caught Connection\ConnectionException, which this
     * exception does not extend.
     */
    public function testASentinelHiccupDoesNotFatalASearchRequest(): void
    {
        Cache::swap(new Repository($this->throwingOn('test')));

        // /meta/settings resolves Searchengines, which builds a Searchengine
        // per engine, and Searchengine::__construct reads
        // app(MetaGer::class)->canCache() — the same path event 213 recorded.
        $response = $this->get('/meta/settings');

        $response->assertOk();
    }

    /**
     * Pictureproxy::get() had no guard at all around its cache lookup.
     */
    public function testASentinelHiccupDoesNotFatalTheImageProxy(): void
    {
        $url = Pictureproxy::generateUrl('https://example.invalid/does-not-resolve.jpg');

        // Pictureproxy hashes url+thumbnail_width (no thumbnail_width here,
        // so it concatenates with null) into the cache key it reads first.
        $imageHash = md5('https://example.invalid/does-not-resolve.jpg' . null);
        Cache::swap(new Repository($this->throwingOn($imageHash)));

        $response = $this->get($url);

        // The upstream host doesn't resolve, so this still isn't a 200 — the
        // point is that it fails as an ordinary 404 (the controller's own
        // fetch-failure handling), not as an uncaught 500 from the cache
        // check that runs before any fetch is attempted.
        $this->assertNotEquals(500, $response->getStatusCode());
    }
}
