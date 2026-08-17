<?php

namespace Tests\Concerns;

use App\Authentication\KeyUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\Support\FakeFetcher;

/**
 * Lets a test run a real search without a real search engine.
 *
 * Two things stand between a test and a result page, and neither is the code
 * under test:
 *
 *   - the fetch is done by a separate worker process (see FakeFetcher), and
 *   - the result page is behind an authenticated key, since MetaGer stopped
 *     serving anonymous searches.
 *
 * Both are dealt with here so the tests themselves are about search behaviour.
 */
trait FakesSearchEngines
{
    private ?FakeFetcher $fakeFetcher = null;

    /**
     * Answer every engine in $bodies with the given raw upstream response, and
     * every other engine with nothing.
     *
     * Bodies are the untouched bytes the engine's API would return, so the
     * parsers run for real. Keys are engine names as `config/foki.json` spells
     * them.
     *
     * @param array<string, string> $bodies
     */
    protected function fakeEngineResponses(array $bodies): FakeFetcher
    {
        $this->fakeFetcher = new FakeFetcher($this->app->make("redis"), $bodies);

        // Swap the facade root. The search path reaches Redis through the facade
        // rather than an injected dependency, so this is the seam that exists;
        // clearResolvedInstances is what makes the facade let go of the manager
        // it has already cached.
        $this->app->instance("redis", $this->fakeFetcher);
        Redis::clearResolvedInstances();

        // Registered here rather than left to each test's tearDown, because
        // forgetting it does not fail the test that forgot — it fails some later
        // test that happens to search the same term, with results it never
        // provided. See FakeFetcher::forgetServedResults.
        $this->beforeApplicationDestroyed(function (): void {
            $this->fakeFetcher?->forgetServedResults();
        });

        return $this->fakeFetcher;
    }

    /**
     * Load a fixture from tests/Fixtures/engines.
     */
    protected function engineFixture(string $name): string
    {
        $path = __DIR__ . "/../Fixtures/engines/" . $name;

        if (!is_file($path)) {
            $this->fail("No engine fixture at tests/Fixtures/engines/$name");
        }

        return file_get_contents($path);
    }

    /**
     * Authenticate as a key with enough charge to pay for any search.
     *
     * KeyUser::getKeyData reads `keyserver:key:<key>` from the cache before it
     * asks the keyserver, so priming that entry authenticates without a
     * keyserver being reachable. Http::preventStrayRequests then turns any
     * request this trait has not accounted for into a failure rather than a
     * silent timeout — if the authentication path grows a new call, a test
     * should say so loudly.
     */
    protected function actingAsSearchUser(string $key = "test-key", float $charge = 1000.0): KeyUser
    {
        Http::preventStrayRequests();
        Http::fake();

        Cache::put("keyserver:key:" . $key, [
            "key" => $key,
            "charge" => $charge,
        ], now()->addMinutes(10));

        $user = new KeyUser($key);
        $this->be($user, "key");

        return $user;
    }

    /**
     * Claims are written to Redis with hincrbyfloat and outlive the test, so a
     * key reused across tests would slowly run itself out of charge. Cleared
     * here rather than in each test.
     */
    protected function forgetSearchUserClaims(string $key = "test-key"): void
    {
        $this->app->make("redis")
            ->connection(config("cache.stores.redis.connection"))
            ->del("keyserver:claims:" . $key);
    }
}
