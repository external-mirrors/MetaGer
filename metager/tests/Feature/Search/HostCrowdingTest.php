<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * No host takes more than two places in the result list.
 *
 * Reported from a real search — "gebrauchte waschmaschine mit garantie
 * lüneburg" — where Brave answered twenty results, eleven of them from one
 * classifieds site and three from one shop, and MetaGer showed every one of
 * them in Brave's order. Brave sent no `cluster` for them, so nothing grouped
 * them. tests/Fixtures/engines/brave-web-same-host.json reproduces that
 * answer's shape position for position, with the hosts renamed: the shop is
 * `waschmaschinen-laden.example` (places 1, 4, 5), the classifieds site
 * `anzeigen.example` (2, 3, 7, 9, 11–15, 17, 20), every other place its own
 * host.
 *
 * The unit-level rules are in tests/Unit/Search/HostCrowdingTest. This proves
 * the step runs in a real search, after ranking and deduplication, and that
 * both the page and out=json can still reach what it folded.
 */
class HostCrowdingTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function search(string $path, string $out = "", string $query = "gebrauchte waschmaschine", string $fixture = "brave-web-same-host.json"): \Illuminate\Testing\TestResponse
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture($fixture)]);

        $response = $this->get($path . "?eingabe=" . urlencode($query) . "&focus=web" . ($out !== "" ? "&out=" . $out : ""));
        $response->assertOk();

        return $response;
    }

    public function testEachHostKeepsTwoPlacesInTheOrderTheyRanked(): void
    {
        $results = $this->search("/meta/meta.ger3", "json")->json("results");

        $this->assertSame([
            "https://waschmaschinen-laden.example/seite-1",
            "https://www.anzeigen.example/seite-2",
            "https://anzeigen.example/seite-3",
            "https://waschmaschinen-laden.example/seite-4",
            "https://seite6.example/seite-6",
            "https://seite8.example/seite-8",
            "https://seite10.example/seite-10",
            "https://seite16.example/seite-16",
            "https://seite18.example/seite-18",
            "https://seite19.example/seite-19",
        ], array_column($results, "link"));
    }

    /**
     * A folded result is not lost to an API client: it travels under the
     * host's second result as `moreFromHost`, in the order it ranked. Without
     * this field a client of out=json would see ten results where there were
     * twenty and have no way to know.
     */
    public function testOutJsonCarriesTheFoldedResultsUnderTheHostsSecondResult(): void
    {
        $results = $this->search("/meta/meta.ger3", "json")->json("results");

        $this->assertSame([], $results[1]["moreFromHost"], "The host's first result should carry nothing.");
        $this->assertSame([
            "https://www.anzeigen.example/seite-7",
            "https://www.anzeigen.example/seite-9",
            "https://www.anzeigen.example/seite-11",
            "https://www.anzeigen.example/seite-12",
            "https://www.anzeigen.example/seite-13",
            "https://www.anzeigen.example/seite-14",
            "https://www.anzeigen.example/seite-15",
            "https://www.anzeigen.example/seite-17",
            "https://www.anzeigen.example/seite-20",
        ], array_column($results[2]["moreFromHost"], "link"));
        $this->assertSame(
            ["https://waschmaschinen-laden.example/seite-5"],
            array_column($results[3]["moreFromHost"], "link")
        );
        $this->assertSame(
            [],
            $results[2]["moreFromHost"][0]["moreFromHost"],
            "A folded result must not carry a fold of its own: one level deep, like the cluster."
        );
    }

    /**
     * On the page the folded results sit in a closed <details> under the
     * host's second result — which opens without JavaScript — with a link to
     * the same search restricted to that host for anything beyond this page.
     */
    public function testThePageFoldsThemIntoAClosedDetailsWithASiteSearchLink(): void
    {
        $html = $this->search("/de-DE/meta/meta.ger3")->getContent();

        $this->assertMatchesRegularExpression(
            '~<details class="result-more-from-host">\s*<summary>\s*9 weitere Ergebnisse von anzeigen\.example\s*</summary>~',
            $html
        );
        $this->assertMatchesRegularExpression(
            '~<summary>\s*1 weiteres Ergebnis von waschmaschinen-laden\.example\s*</summary>~',
            $html
        );

        // Read inside the fold only: every result's options menu already
        // carries a site: link, so the whole page would prove nothing.
        $this->assertSame(1, preg_match(
            '~<details class="result-more-from-host">\s*<summary>\s*9 weitere.*?</details>~s',
            $html,
            $fold
        ));
        $fold = str_replace("&amp;", "&", $fold[0]);

        foreach ([7, 9, 11, 12, 13, 14, 15, 17, 20] as $place) {
            $this->assertStringContainsString('href="https://www.anzeigen.example/seite-' . $place . '"', $fold);
        }

        $this->assertStringContainsString(
            "eingabe=" . rawurlencode("gebrauchte waschmaschine site:anzeigen.example"),
            $fold,
            "The fold has no link to search the host itself."
        );
    }

    /**
     * Folded results are not result rows. scriptResultPage.js reaches the rows
     * as `#results > .result`, and the region's label announces how many
     * there are — a folded result counted as a row would be numbered and
     * announced as one.
     */
    public function testFoldedResultsAreNotResultRows(): void
    {
        $html = $this->search("/de-DE/meta/meta.ger3")->getContent();

        $this->assertSame(10, substr_count($html, '<div class="result" data-count='));
    }

    /**
     * The search the fold links to shows everything it folded. The fixture
     * answers with every host regardless of the query, which is fine: the
     * point is that the host asked for is not capped.
     */
    public function testTheSiteSearchTheFoldLinksToIsNotCapped(): void
    {
        $results = $this->search("/meta/meta.ger3", "json", "gebrauchte waschmaschine site:anzeigen.example")->json("results");

        $anzeigen = array_filter(array_column($results, "host"), fn(string $host) => $host === "anzeigen.example");
        $this->assertCount(11, $anzeigen);
    }

    /**
     * `(site:a OR site:b)` is a different query filter from `site:a` —
     * `multisite` rather than `sitesearch` — and just as much a request for
     * those hosts' results.
     */
    public function testASearchOverSeveralSitesIsNotCappedEither(): void
    {
        $results = $this->search(
            "/meta/meta.ger3",
            "json",
            "gebrauchte waschmaschine (site:anzeigen.example OR site:waschmaschinen-laden.example)"
        )->json("results");

        $anzeigen = array_filter(array_column($results, "host"), fn(string $host) => $host === "anzeigen.example");
        $this->assertCount(11, $anzeigen);
    }

    /**
     * Ten results where the engine sent twenty would surprise someone who did
     * not notice the folds, so the page says up front how many there are —
     * all of them, across every host.
     */
    public function testThePageSaysHowManyResultsAreFolded(): void
    {
        $html = $this->search("/de-DE/meta/meta.ger3")->getContent();

        $this->assertMatchesRegularExpression(
            '~<p class="more-from-host-notice">10 Ergebnisse von Websites, die schon zweimal vorkommen, sind unter ihnen eingeklappt\.</p>~',
            $html
        );
    }

    /**
     * And says nothing when nothing is folded. brave-web.json has no host
     * more than twice.
     */
    public function testAPageWithoutFoldsHasNoNotice(): void
    {
        $html = $this->search("/de-DE/meta/meta.ger3", fixture: "brave-web.json")->getContent();

        $this->assertStringNotContainsString("more-from-host-notice", $html);
    }
}
