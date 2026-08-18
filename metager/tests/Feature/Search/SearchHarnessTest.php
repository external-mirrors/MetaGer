<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Proves the search-fixture harness works, before anything relies on it.
 *
 * Every characterization test in step D is going to assert something about a
 * result page produced from a canned engine response. If the harness quietly
 * fails to deliver those responses, all of them still pass — against an empty
 * result set — and pin nothing at all. That failure mode is silent and total,
 * so it gets its own test rather than being assumed by the others.
 *
 * This is deliberately not a characterization test of ranking or dedup. It
 * asserts only that a fixture goes in and comes out the far side of the real
 * pipeline: mission queued, response served where the worker would serve it,
 * parser run, result rendered.
 */
class SearchHarnessTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    public function testAFixtureReachesTheResultPage(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $response->assertOk();
        // From the fixture, through Brave's parser, onto the page. html_entity_decode
        // in the parser is why the title is asserted decoded.
        $response->assertSee("Kaffee & Zubereitung", false);
        $response->assertSee("https://example.org/kaffee", false);
    }

    public function testTheSearchQueuesAMissionForEveryEnabledEngine(): void
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        // config/foki.json puts four engines in the web fokus. Whether all four
        // are queued depends on the user's settings, but brave is the fokus's
        // only `main` engine — the one waitForMainResults blocks on — so a web
        // search that did not ask brave would not be a web search.
        $this->assertContains(
            "brave",
            $fetcher->queuedEngines(),
            "The web fokus queued no mission for brave, the engine it waits on."
        );
    }

    public function testAMissionCarriesTheUrlTheFetcherWouldRequest(): void
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $missions = collect($fetcher->missions())->keyBy("name");
        $this->assertArrayHasKey("brave", $missions->all());

        $brave = $missions->get("brave");
        // The query has to reach the engine. This is the whole contract between
        // MetaGer and an upstream: a URL, and a hash to answer on.
        $this->assertStringContainsString("kaffee", $brave["url"]);
        $this->assertNotEmpty($brave["resulthash"]);
    }

    /**
     * Every later test in step D fakes more than one engine at a time — ranking
     * and deduplication are meaningless with a single source — so the harness
     * has to serve them independently rather than answering every mission with
     * the same body.
     */
    public function testSeveralEnginesCanBeFakedAtOnce(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $response->assertOk();
        // One result unique to each fixture, so neither engine can account for
        // both. The two parsers read entirely different response shapes —
        // web.results[].description for Brave, organic[].snippet for Serper —
        // which is the point: each fixture goes through its own engine's parser.
        $response->assertSee("https://example.org/espresso", false);
        $response->assertSee("https://serper-example.net/kaffee", false);
    }

    /**
     * A characterization of which engines a default web search actually uses.
     *
     * config/foki.json lists four for the web fokus, but two of them never run
     * unless something changes: mojeek is disabledByDefault and has to be opted
     * into, and yandex is disabled in config/sumas.json. So the default search
     * is brave plus serper_web, and any test that fakes mojeek or yandex is
     * quietly faking an engine that is never asked.
     *
     * Worth pinning because D2 removes 28 parsers on the grounds that
     * config/foki.json governs what users can reach — this records that foki.json
     * is an upper bound, not the actual set.
     *
     * Quicktips is filtered out rather than asserted: it is not a search engine
     * but rides the same fetch queue under the name "Quicktips"
     * (Quicktips::…->rpush(MetaGer::FETCHQUEUE_KEY)), and it only queues a
     * mission when its own answer is not already cached — so whether it appears
     * depends on cache state left by earlier runs, not on this search. That it
     * shares the queue at all is worth knowing before D7c touches the
     * orchestration: the queue carries more than engines.
     */
    public function testADefaultWebSearchQueriesOnlyTheEnginesEnabledByDefault(): void
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $engines = array_values(array_diff($fetcher->queuedEngines(), ["Quicktips"]));

        $this->assertSame(
            ["brave", "serper_web"],
            $engines,
            "The set of engines a default web search asks has changed."
        );
    }

    /**
     * The harness has to be able to *fail*. An engine with no fixture must come
     * back empty rather than falling through to a real HTTP request — otherwise
     * a typo in a fixture key turns into a live call to a search engine from the
     * test suite.
     */
    public function testAnEngineWithoutAFixtureReturnsNothing(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $response->assertOk();
        $response->assertDontSee("https://example.org/kaffee", false);
    }
}
