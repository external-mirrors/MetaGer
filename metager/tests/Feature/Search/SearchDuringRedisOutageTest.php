<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Facades\Redis;
use Predis\ClientException;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * The scenario tests/Feature/RedisOutageResponseTest.php pins against a
 * throwing closure, exercised here through a real authenticated search
 * request instead: EngineOrchestrator::rpush() (app/Search/EngineOrchestrator.php)
 * is the first Redis call a search makes, and in production it is exactly
 * where "No sentinel server available for autodiscovery" (GlitchTip
 * METAGER-I/L) was thrown — bootstrap/app.php's renderable() is what now
 * turns that into a 503 instead of an uncaught 500 reaching a real user.
 *
 * Authenticates with FakesSearchEngines::actingAsSearchUser(), which primes
 * the key-cache entry and fakes the keyserver's HTTP calls. It does not use a
 * real key against a real keymanager-express instance — that only exists on
 * a developer's machine (and isn't charged there either), so a test written
 * against one would pass locally and fail, or simply hang, in the GitLab CI
 * runners that have no such service to reach.
 */
class SearchDuringRedisOutageTest extends TestCase
{
    use FakesSearchEngines;

    public function testASearchDuringARedisOutageAnswers503NotAnUncaught500(): void
    {
        $this->actingAsSearchUser();

        $this->app->instance('redis', new class {
            public function __call(string $method, array $arguments): mixed
            {
                throw new ClientException('No sentinel server available for autodiscovery.');
            }
        });
        Redis::clearResolvedInstances();

        $response = $this->get('/meta/meta.ger3?eingabe=kaffee&focus=web');

        $response->assertStatus(503);
        $response->assertHeader('Retry-After');
        $response->assertSee('http-equiv="refresh"', false);
    }
}
