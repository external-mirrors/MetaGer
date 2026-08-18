<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Facades\Cache;
use Tests\Concerns\FakesSearchEngines;
use Tests\Support\RecordingRedis;
use Tests\TestCase;

/**
 * Characterization tests for the orchestration half of a search: asking the
 * engines, waiting for them, and reading what came back.
 *
 * This was four methods on MetaGer — startSearch, checkCache,
 * waitForMainResults and retrieveResults — each looping over the engines and
 * talking to Redis one engine at a time; Search\EngineOrchestrator now does it a
 * phase at a time. SearchHarnessTest and EngineReachabilityTest already pin
 * *which* engines get asked; this is about the mechanics around that, and about
 * what the mechanics cost.
 *
 * ## Several of these tests count round trips
 *
 * They were written before the extraction, against the per-engine shape, so that
 * the commit which batched them would have to change them — the diff shows the
 * improvement rather than leaving it to a claim in a message. They are still
 * ratchets: a change that makes a search chattier again has to come here and say
 * so.
 *
 * The numbers matter because these round trips are serialized and because
 * production does not look like this test: here Valkey answers in 16µs over a
 * socket on the same host, and there it is a network hop to a pod through a
 * proxy.
 */
class EngineOrchestrationTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @return array{0: \Illuminate\Testing\TestResponse, 1: RecordingRedis, 2: \Tests\Support\FakeFetcher}
     */
    private function searchRecording(string $query = "kaffee"): array
    {
        $this->actingAsSearchUser();
        $fake = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);
        $recorder = $this->recordRedisTraffic();

        $response = $this->get("/meta/meta.ger3?eingabe=" . urlencode($query) . "&focus=web&out=json");
        $response->assertOk();

        return [$response, $recorder, $fake];
    }

    /**
     * Every engine's mission goes out in a single push.
     *
     * This used to be one rpush per engine, because Searchengine::startSearch
     * queued its own and was called in a loop. The missions are independent and
     * the worker pops them in batches anyway, so nothing was gained by waiting
     * for Valkey between them; EngineOrchestrator::queueMissions collects them
     * and pushes once.
     *
     * Asserted through the fake rather than the command counter because
     * Quicktips queues onto the same list separately, so counting rpush against
     * `fetcher.queue` would measure Quicktips as much as the engines.
     */
    public function testEveryEngineIsQueuedInOneRoundTrip(): void
    {
        [, , $fake] = $this->searchRecording();

        $enginePushes = array_values(array_filter(array_map(
            fn(array $names) => array_values(array_diff($names, ["Quicktips"])),
            $fake->pushes()
        )));

        $this->assertCount(
            1,
            $enginePushes,
            "The engine missions went out in " . count($enginePushes) . " pushes, not one."
        );
        $this->assertGreaterThanOrEqual(
            2,
            count($enginePushes[0]),
            "Fewer than two missions travelled together, so this proves nothing about batching."
        );
    }

    /**
     * Receiving and reading the engines' answers costs two round trips in total,
     * not two per engine.
     *
     * One pipeline puts the awaited answer back where brpop took it from — brpop
     * consumes, and load-more comes back for the same list — and one rotates and
     * re-expires every remaining engine's list at once. Both used to be a pair
     * of separate commands issued per engine.
     *
     * The commands inside a pipeline are not recorded individually, which is the
     * point: `lpush` no longer appearing means it no longer costs a wait of its
     * own.
     */
    public function testReceivingAndReadingTheAnswersIsTwoRoundTrips(): void
    {
        [, $recorder] = $this->searchRecording();

        $this->assertSame(
            1,
            $recorder->countOfKey("pipeline", "lpush,expire"),
            "The awaited answer is put back and re-expired in one round trip.\n" . implode("\n", $recorder->trace())
        );

        // Whatever is left after the wait, read together. How many engines that
        // is depends on which of them answered first, so the assertion is that
        // there is one such round trip — not how many commands rode in it.
        $reads = array_values(array_filter(
            $recorder->trace(),
            fn(string $line) => str_starts_with($line, "pipeline rpoplpush")
        ));
        $this->assertCount(
            1,
            $reads,
            "The remaining engines were not all read in one round trip.\n" . implode("\n", $recorder->trace())
        );

        $this->assertSame(
            0,
            $recorder->countOf("lpush"),
            "The awaited answer was put back with a round trip of its own instead of alongside its expiry.\n"
                . implode("\n", $recorder->trace())
        );
    }

    /**
     * The awaited answer is still put back, whatever it costs.
     *
     * Load-more reads the same list later, so an answer consumed by brpop and
     * not returned would leave that engine's results unreachable on the second
     * page. Behaviour, not cost — this one is not meant to change.
     */
    public function testTheAwaitedAnswerIsPutBackAfterBeingTaken(): void
    {
        [, $recorder] = $this->searchRecording();

        $this->assertSame(1, $recorder->countOf("brpop"));

        $engines = app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengines();
        foreach ($engines as $engine) {
            if (!$engine->loaded) {
                continue;
            }
            $this->assertNotSame(
                0,
                $this->app->make("redis")->llen($engine->getHash()),
                "The list for {$engine->name} is empty after the search, so load-more will find nothing.\n"
                    . implode("\n", $recorder->trace())
            );
        }
    }

    /**
     * The whole point of the recorder. Every one of these is serialized, and in
     * production each is a network hop rather than a socket on the same host.
     *
     * Was 20 before EngineOrchestrator and the claims batching. Three parts
     * make up what is left:
     *
     *   - the search itself: queue every mission in one push, wait, put the
     *     awaited answer back, read the rest in one pipeline;
     *   - Quicktips, which queues and reads its own mission with three commands
     *     of its own when its answer is not already cached;
     *   - the payment, which releases the claim once the page has been built;
     *   - settling the suggestion debt on the way in.
     *
     * The budget was 12 until the harness started faking the keyserver's
     * discharge response properly. That is not a regression: makePayment was
     * returning false on an empty response body and abandoning the payment
     * before it touched Redis, so the old number was the cost of a search that
     * never paid for itself. See FakesSearchEngines::actingAsSearchUser.
     *
     * From there 15 -> 14: batching the engine discharges into one call took
     * three claim round trips down to one, and settling the suggestion debt
     * added one back. The Redis saving is the smaller half of that change —
     * each discharge is also a synchronous HTTP POST to the keyserver made
     * while the user waits, which this recorder cannot see. See
     * SearchAuthorizationTest::testAllTheEnginesOfASearchArePaidForInOneCall.
     */
    public function testTheWholeSearchStaysWithinItsRoundTripBudget(): void
    {
        [, $recorder] = $this->searchRecording();

        $this->assertLessThanOrEqual(
            14,
            $recorder->total(),
            "A search got more expensive in round trips, not less. In order:\n" . implode("\n", $recorder->trace())
        );
    }

    /**
     * Authorizing the search reads other requests' claims once and stakes this
     * request's own claim once — two round trips, where it used to be six.
     *
     * What went away: the claim and its deadline were separate commands, and
     * every paid engine read this request's own claim back out of Redis before
     * discharging it — a number the process had just written there itself and
     * which no other process can touch, because the field is keyed by an id
     * unique to this KeyUser. See KeyUserClaimsTest for what the claims mean.
     *
     * Deliberately not asserted as an exact number of claim writes. Paying
     * stakes more claim when an engine costs more than is left of the original
     * one, which happens because the middleware claims getSearchCost() while
     * the controller pays each engine's own cost, and the former is capped. How
     * often that lands is a pricing detail; that no payment *reads* is the
     * property this commit is about.
     */
    public function testAuthorizationCostsTwoRoundTrips(): void
    {
        [, $recorder] = $this->searchRecording();

        $this->assertSame(
            1,
            $recorder->countOfKey("hgetall", "keyserver:claims:test-key"),
            "Other requests' claims are read once per request.\n" . implode("\n", $recorder->trace())
        );
        $this->assertSame(
            0,
            $recorder->countOfKey("hget", "keyserver:claims:test-key"),
            "A payment read back a claim this process wrote itself, which Redis cannot know better than we do.\n"
                . implode("\n", $recorder->trace())
        );

        // Every claim write carries its own expiry in the same round trip. A
        // bare hexpireat means one got split back out of its pipeline, which
        // leaves a window where a claim exists with no deadline on it — and a
        // request that dies in that window holds the charge until someone
        // notices.
        $this->assertSame(
            0,
            $recorder->countOf("hexpireat"),
            "A claim's expiry travelled on its own instead of with the claim.\n" . implode("\n", $recorder->trace())
        );
        $this->assertGreaterThanOrEqual(
            1,
            $recorder->countOfKey("pipeline", "hincrbyfloat,hexpireat"),
            "The request never staked a claim at all.\n" . implode("\n", $recorder->trace())
        );
    }

    /**
     * A repeated search asks nobody.
     *
     * checkCache looks for each engine's answer under the same hash the fetcher
     * stored it against, and an engine found there is marked loaded before any
     * mission is queued — so the second search neither pushes nor waits, and
     * still renders the same results. It is the branch that makes a repeated
     * query fast, and nothing reached it before: the harness stood in for the
     * fetcher's Redis write but not for its cache write.
     */
    public function testASecondSearchIsServedFromTheCacheWithoutAskingAnyEngine(): void
    {
        $bodies = [
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ];

        $this->actingAsSearchUser();
        $first = $this->fakeEngineResponses($bodies);
        $firstResponse = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");
        $firstResponse->assertOk();

        $this->assertContains("brave", $first->queuedEngines(), "The first search asked nobody, so the second asking nobody would prove nothing.");

        $this->forgetRequestScopedServices();
        $second = $this->fakeEngineResponses($bodies);
        $secondResponse = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");
        $secondResponse->assertOk();

        $this->assertSame(
            [],
            $second->queuedEngines(),
            "The second search queued a mission for an engine whose answer was already cached."
        );
        $this->assertSame(
            array_column($firstResponse->json("results"), "link"),
            array_column($secondResponse->json("results"), "link"),
            "The cached search returned a different page than the one that filled the cache."
        );
    }

    /**
     * And it costs a fraction of the round trips, because there is nothing to
     * push and nothing to wait for.
     */
    public function testACachedSearchCostsFarFewerRoundTrips(): void
    {
        $bodies = [
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ];

        $this->actingAsSearchUser();
        $this->fakeEngineResponses($bodies);
        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $this->forgetRequestScopedServices();
        $this->fakeEngineResponses($bodies);
        $recorder = $this->recordRedisTraffic();
        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $this->assertSame(0, $recorder->countOf("brpop"), "A fully cached search still blocked waiting for an engine.");
        $this->assertSame(0, $recorder->countOfKey("rpush", "fetcher.queue"), "A fully cached search still queued a fetch.");
    }

    /**
     * An engine that never answers must not hold the page. The fake serves
     * "no-result" for an engine it has no fixture for, which is the same string
     * the worker writes when upstream fails — so this is the shape of a real
     * engine failure, and the page still renders with what the others found.
     */
    public function testAnEngineThatFailsDoesNotStopThePage(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            // serper_web deliberately absent
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");

        $response->assertOk();
        $this->assertNotEmpty($response->json("results"), "One failing engine took the whole page with it.");
    }

    /**
     * The cache is asked once for all the engines, not twice for each of them.
     *
     * checkCache used to do `Cache::has($hash)` and then `Cache::get($hash)` per
     * engine — the first call only asking whether the second would find
     * anything. EngineOrchestrator::loadFromCache reads them all together.
     *
     * Counted at the cache repository rather than through RecordingRedis: the
     * suite runs with `CACHE_STORE=array` (phpunit.xml), so these never reach
     * Redis in a test even though every one of them is a round trip in
     * production. One `many` for N keys is one `mget`; a `has` plus a `get` per
     * engine is 2N.
     */
    public function testTheCacheIsAskedOnceForEveryEngineAtATime(): void
    {
        $bodies = [
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ];

        $this->actingAsSearchUser();
        $first = $this->fakeEngineResponses($bodies);
        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $engineHashes = array_values(array_unique(array_column(
            array_filter($first->missions(), fn(array $m) => $m["name"] !== "Quicktips"),
            "resulthash"
        )));
        $this->assertNotEmpty($engineHashes, "No engine was fetched, so there is nothing cached to read back.");

        $this->forgetRequestScopedServices();
        $this->fakeEngineResponses($bodies);
        $cache = $this->recordCacheReads();
        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $this->assertSame(
            0,
            $cache->countFor("has", $engineHashes),
            "An engine's cached answer was asked about before being read, which is a round trip that returns nothing useful.\n"
                . implode("\n", $cache->trace())
        );
        $this->assertSame(
            $engineHashes,
            array_values(array_intersect($cache->keysOfFirst("many"), $engineHashes)),
            "The engines were not all read in one batch.\n" . implode("\n", $cache->trace())
        );
    }
}
