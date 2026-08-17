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
 * Four methods on MetaGer do this today — startSearch, checkCache,
 * waitForMainResults and retrieveResults — and step D7c lifts them into a
 * Search\EngineOrchestrator. SearchHarnessTest and EngineReachabilityTest
 * already pin *which* engines get asked; this is about the mechanics around
 * that, and about what the mechanics cost.
 *
 * ## The cost assertions are characterization, not a target
 *
 * Several tests below count Redis commands. They pass today and they are
 * *meant* to be changed by the commit that reduces them — the point is that the
 * diff shows the change rather than leaving it to a claim in a message. Each one
 * says what it costs and why it costs that.
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
     * One mission per engine, each its own round trip.
     *
     * Searchengine::startSearch pushes its own mission and is called in a loop
     * over the enabled engines, so the count is the number of engines and each
     * one is a separate wait for Valkey to answer. Nothing about the missions
     * requires that — they are independent, and one rpush takes a list.
     */
    public function testEveryEngineIsQueuedWithItsOwnRoundTrip(): void
    {
        [, $recorder, $fake] = $this->searchRecording();

        $queued = count($fake->queuedEngines());

        $this->assertGreaterThanOrEqual(2, $queued, "Fewer than two missions went out, so counting round trips against them proves little.");
        $this->assertSame(
            $queued,
            $recorder->countOfKey("rpush", "fetcher.queue"),
            "One push per mission. If this is 1 for $queued missions they are batched, which is the improvement — invert the assertion.\n"
                . implode("\n", $recorder->trace())
        );
    }

    /**
     * Reading an engine's answer costs two commands: rpoplpush to rotate the
     * list rather than consume it — load-more comes back for the same list —
     * and then a fresh expiry. Nothing between them depends on the other, so
     * they are two waits where one would do.
     */
    public function testReadingAnEngineAnswerTakesTwoRoundTrips(): void
    {
        [, $recorder] = $this->searchRecording();

        $reads = $recorder->countOf("rpoplpush");

        $this->assertGreaterThanOrEqual(1, $reads, "No engine answer was read at all.\n" . implode("\n", $recorder->trace()));
        $this->assertSame(
            $reads + $recorder->countOf("lpush"),
            $recorder->countOf("expire"),
            "Every list read gets its own separate expire.\n" . implode("\n", $recorder->trace())
        );
    }

    /**
     * The answer that ends the wait is taken with brpop and pushed straight
     * back, because brpop consumes and the list has to survive for load-more:
     * three commands — brpop, lpush, expire — to receive one answer.
     */
    public function testTheAwaitedAnswerIsPutBackAfterBeingTaken(): void
    {
        [, $recorder] = $this->searchRecording();

        $this->assertSame(1, $recorder->countOf("brpop"));
        $this->assertSame(
            1,
            $recorder->countOf("lpush"),
            "Nothing put the awaited answer back; load-more will find an empty list.\n" . implode("\n", $recorder->trace())
        );
    }

    /**
     * The whole point of the recorder. Every one of these is serialized, and in
     * production each is a network hop rather than a socket on the same host.
     */
    public function testTheWholeSearchStaysWithinItsRoundTripBudget(): void
    {
        [, $recorder] = $this->searchRecording();

        $this->assertLessThanOrEqual(
            20,
            $recorder->total(),
            "A search got more expensive in round trips, not less. In order:\n" . implode("\n", $recorder->trace())
        );
    }

    /**
     * Six of those twenty are the authorization talking to the same key, and
     * four of them read the same hash. Noted rather than fixed: this is the
     * path that charges a key, and it is not the orchestration.
     */
    public function testAuthorizationAccountsForSixOfTheRoundTrips(): void
    {
        [, $recorder] = $this->searchRecording();

        $claims = $recorder->countOfKey("hgetall", "keyserver:claims:test-key")
            + $recorder->countOfKey("hget", "keyserver:claims:test-key")
            + $recorder->countOfKey("hincrbyfloat", "keyserver:claims:test-key")
            + $recorder->countOfKey("hexpireat", "keyserver:claims:test-key");

        $this->assertSame(
            6,
            $claims,
            "The authorization path changed shape. It is not what these tests are about, but it is a third of the search's Redis traffic.\n"
                . implode("\n", $recorder->trace())
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
     * Cache lookups ask twice: once whether the key exists, once for its value.
     * Cache::has followed by Cache::get, per engine, in checkCache.
     */
    public function testCheckingTheCacheAsksWhetherAKeyExistsBeforeReadingIt(): void
    {
        Cache::flush();

        [, $recorder] = $this->searchRecording();

        $this->assertGreaterThanOrEqual(
            1,
            $recorder->countOf("exists"),
            "checkCache stopped asking `exists` first. If it now reads straight through, that is one round trip per engine saved.\n"
                . implode("\n", $recorder->trace())
        );
    }
}
