<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for what happens between "the engines answered" and
 * "the page has a list of results": ranking order and deduplication.
 *
 * This is the part of the search that users actually experience as MetaGer
 * rather than as its upstreams, and it is the part step D7a extracts into
 * ResultRanker and ResultDeduplicator. It currently lives in four methods on
 * MetaGer — rankAll, combineResults, prepareResults and duplicationCheck — with
 * the per-result arithmetic on Result::rank.
 *
 * These tests assert *relative* order and merge behaviour, not rank values. The
 * numbers (sourceRank * 0.02, a URL boost, a search-word boost, all multiplied
 * by the engine boost) are a tuning surface someone is entitled to change; the
 * guarantee worth defending is that the list is ordered by rank, that one URL
 * appears once, and that a merged result credits every engine that found it.
 *
 * Results are read out of `out=json`, which gives the ranked list in order
 * without the noise of the HTML page.
 */
class ResultMergingTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @param array<string, string> $bodies
     * @return array<int, array<string, mixed>>
     */
    private function resultsFor(array $bodies, string $query = "kaffee"): array
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses($bodies);

        $response = $this->get("/meta/meta.ger3?eingabe=" . urlencode($query) . "&focus=web&out=json");
        $response->assertOk();

        return $response->json("results");
    }

    /**
     * The same page found by two engines is one result, not two.
     *
     * The fixtures spell the URL differently on purpose: Brave returns
     * `https://example.org/kaffee`, Serper `http://www.example.org/kaffee/`.
     * duplicationCheck normalises scheme, `www.` and a trailing slash away
     * before comparing, so these are the same page.
     */
    public function testTheSamePageFromTwoEnginesIsMergedIntoOneResult(): void
    {
        $results = $this->resultsFor([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web-duplicates.json"),
        ]);

        $kaffee = array_values(array_filter(
            $results,
            fn(array $r) => str_contains($r["link"], "example.org/kaffee")
        ));

        $this->assertCount(
            1,
            $kaffee,
            "example.org/kaffee came back more than once — deduplication no longer normalises scheme/www/trailing slash."
        );
    }

    /**
     * A merged result names both engines. This is what makes the "found by"
     * line on a result honest, and it is the part most easily lost in a
     * refactor: gefVon and gefVonLink are two parallel arrays appended to by
     * hand inside duplicationCheck.
     */
    public function testAMergedResultCreditsEveryEngineThatFoundIt(): void
    {
        $results = $this->resultsFor([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web-duplicates.json"),
        ]);

        $kaffee = collect($results)->firstWhere(fn(array $r) => str_contains($r["link"], "example.org/kaffee"));
        $this->assertNotNull($kaffee, "The duplicated result vanished entirely.");

        $names = collect($kaffee["engines"])->pluck("name")->all();

        $this->assertCount(2, $names, "A result found by both engines credits only " . implode(", ", $names) . ".");
        $this->assertSame(count($names), count($kaffee["engines"]), "gefVon and gefVonLink came apart.");
    }

    /**
     * A query string makes two URLs different pages as far as dedup is
     * concerned — normalisation covers scheme, `www.` and trailing slash, and
     * stops there.
     *
     * Characterizing, not endorsing: `?utm_source=…` is tracking noise and the
     * two URLs are the same page to a reader. Whether to strip known tracking
     * parameters before comparing is a product decision; this test records that
     * today MetaGer does not, so a change to that is visible rather than
     * accidental.
     */
    public function testAQueryStringDefeatsDeduplication(): void
    {
        $results = $this->resultsFor([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web-duplicates.json"),
        ]);

        $espresso = array_values(array_filter(
            $results,
            fn(array $r) => str_contains($r["link"], "example.org/espresso")
        ));

        $this->assertCount(
            2,
            $espresso,
            "example.org/espresso is no longer duplicated — deduplication learned to ignore query parameters. That is an improvement; update this test to describe it."
        );
    }

    /**
     * Every result from every answering engine reaches the list. A dedup that
     * merged too eagerly would still pass the tests above.
     */
    public function testEveryDistinctResultFromEveryEngineSurvives(): void
    {
        $results = $this->resultsFor([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web-duplicates.json"),
        ]);

        $links = collect($results)->pluck("link")->implode(" ");

        // one unique to Brave, one unique to Serper
        $this->assertStringContainsString("beispiel.de/sorten", $links);
        $this->assertStringContainsString("serper-example.net/nur-hier", $links);
    }

    /**
     * The list is ordered by rank, highest first.
     *
     * Asserted through the ranking rather than against it: a result whose URL
     * carries the search word is boosted by calcURLBoost, so with both engines
     * answering, `example.org/kaffee` — found twice and matching in the URL —
     * must not sit below a result that matches nowhere.
     */
    public function testResultsAreOrderedByRankNotByEngineOrArrivalOrder(): void
    {
        $results = $this->resultsFor([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web-duplicates.json"),
        ]);

        $links = collect($results)->pluck("link")->values();

        $kaffee = $links->search(fn(string $l) => str_contains($l, "example.org/kaffee"));
        $sorten = $links->search(fn(string $l) => str_contains($l, "beispiel.de/sorten"));

        $this->assertNotFalse($kaffee);
        $this->assertNotFalse($sorten);
        $this->assertLessThan(
            $sorten,
            $kaffee,
            "A result matching the query in its URL ranked below one that does not. Ranking order changed."
        );
    }

    /**
     * A single engine answering is still a well-formed result list — the
     * merging code has to cope with nothing to merge.
     */
    public function testOneAnsweringEngineStillProducesResults(): void
    {
        $results = $this->resultsFor([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->assertCount(3, $results, "The Brave fixture carries three results; all three should reach the list.");
    }

    /**
     * No engine answering is not an error page: it is an empty result list with
     * a message. Pinned because the branch that produces it sits in
     * prepareResults among the ad handling that step D4 deletes.
     */
    public function testNoAnsweringEngineYieldsAnEmptyListWithAnError(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");

        $response->assertOk();
        $this->assertSame([], $response->json("results"));
        $this->assertNotEmpty($response->json("errors"), "An empty result set must say so rather than looking like a successful empty search.");
    }
}
