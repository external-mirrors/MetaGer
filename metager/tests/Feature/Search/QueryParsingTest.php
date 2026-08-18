<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Carbon;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for the half of MetaGer::parseFormData and
 * checkSpecialSearches that decides *what is being searched for*, ahead of the
 * QueryParser extraction in step D7b.
 *
 * tests/Feature/Search/SpecialSearchesTest already covers the operators typed
 * into the query box. What was left uncovered, and is covered here, is
 * everything the same code does with the *request parameters* around it:
 *
 *   - `fc` / `ff` / `ft`, the custom date range, and the fifty lines of
 *     sanitising it goes through before an engine sees it
 *   - `blacklist=` and `stop=`, which do not add to the operators in the query
 *     but replace them outright
 *   - the self-harm trigger list, which is checked against the query after the
 *     operators have been stripped out of it
 *
 * The date range is the part worth the most here. It is validated across five
 * separate rules, all of them silent — an unparseable date, a half-filled
 * range, a range running backwards and a range older than a year each produce a
 * *different* outcome, and none of them is an error the user sees. It is
 * observed through the URL the fetcher is told to request, because that is where
 * the decision actually lands: config/filters.json maps `fc=on` to
 * `dyn-FreshnessCustomBrave`, which reads `ff` and `ft` straight back off the
 * request.
 */
class QueryParsingTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Run a search and return the URL the fetcher was told to request of Brave.
     *
     * @param array<string, string> $parameters
     */
    private function braveUrl(array $parameters): string
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?" . http_build_query($parameters + [
            "eingabe" => "kaffee",
            "focus" => "web",
            "out" => "json",
        ]));
        $response->assertOk();

        $brave = collect($fetcher->missions())->firstWhere("name", "brave");
        $this->assertNotNull($brave, "Brave was never queued, so there is no URL to look at.");

        return $brave["url"];
    }

    private function daysAgo(int $days): string
    {
        return Carbon::now()->subDays($days)->format("Y-m-d");
    }

    // ---------------------------------------------------------------- dates

    /**
     * The happy path: a range inside the last year, the right way round.
     */
    public function testACustomDateRangeIsPassedToTheEngine(): void
    {
        $from = $this->daysAgo(60);
        $to = $this->daysAgo(30);

        $url = $this->braveUrl(["fc" => "on", "ff" => $from, "ft" => $to]);

        $this->assertStringContainsString(
            urlencode($from . "to" . $to),
            $url,
            "Brave was not asked for the requested date range."
        );
    }

    /**
     * A range entered backwards is swapped rather than refused, so the search
     * still happens.
     */
    public function testARangeRunningBackwardsIsSwapped(): void
    {
        $earlier = $this->daysAgo(60);
        $later = $this->daysAgo(30);

        $url = $this->braveUrl(["fc" => "on", "ff" => $later, "ft" => $earlier]);

        $this->assertStringContainsString(urlencode($earlier . "to" . $later), $url);
    }

    /**
     * Dates in the future are pulled back to today.
     */
    public function testADateInTheFutureIsPulledBackToToday(): void
    {
        $from = $this->daysAgo(30);
        $today = Carbon::now()->format("Y-m-d");

        $url = $this->braveUrl(["fc" => "on", "ff" => $from, "ft" => Carbon::now()->addYear()->format("Y-m-d")]);

        $this->assertStringContainsString(urlencode($from . "to" . $today), $url);
    }

    /**
     * And dates further back than a year are pulled forward to a year ago.
     *
     * The comment in MetaGer attributes this to Bing, which MetaGer no longer
     * queries — the clamp now applies to Brave, which has no such documented
     * limit. Characterized as it stands: whether the limit still belongs there
     * is a question for whoever owns the engine configuration.
     */
    public function testADateOlderThanAYearIsPulledForwardToAYearAgo(): void
    {
        $yearAgo = Carbon::now()->subYear()->format("Y-m-d");
        $to = $this->daysAgo(30);

        $url = $this->braveUrl(["fc" => "on", "ff" => Carbon::now()->subYears(3)->format("Y-m-d"), "ft" => $to]);

        $this->assertStringContainsString(urlencode($yearAgo . "to" . $to), $url);
    }

    /**
     * @return array<string, array{0: array<string, string>}>
     */
    public static function unusableRanges(): array
    {
        return [
            "only a start date" => [["fc" => "on", "ff" => "2026-01-01"]],
            "only an end date" => [["fc" => "on", "ft" => "2026-01-01"]],
            "no dates at all" => [["fc" => "on"]],
            "a start date that is not a date" => [["fc" => "on", "ff" => "morgen", "ft" => "2026-01-01"]],
            "an end date that is not a date" => [["fc" => "on", "ff" => "2026-01-01", "ft" => "irgendwann"]],
            "a date in the wrong format" => [["fc" => "on", "ff" => "01.01.2026", "ft" => "01.02.2026"]],
        ];
    }

    /**
     * Anything that is not a usable range drops the whole filter silently: no
     * error, no warning, just a search without a date restriction.
     *
     * @param array<string, string> $parameters
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("unusableRanges")]
    public function testAnUnusableRangeDropsTheFilterEntirely(array $parameters): void
    {
        $url = $this->braveUrl($parameters);

        $this->assertStringNotContainsString(
            "freshness=",
            $url,
            "Brave was sent a freshness parameter built from a date range it cannot have understood."
        );
    }

    /**
     * A custom range wins over the quick "past day/week/month" filter when both
     * are submitted — the wider one is dropped so the two cannot contradict
     * each other in the same request.
     */
    public function testACustomRangeReplacesTheQuickFreshnessFilter(): void
    {
        $from = $this->daysAgo(60);
        $to = $this->daysAgo(30);

        $url = $this->braveUrl(["f" => "d", "fc" => "on", "ff" => $from, "ft" => $to]);

        $this->assertStringContainsString(urlencode($from . "to" . $to), $url);
        $this->assertStringNotContainsString(
            "freshness=pd",
            $url,
            "Both the quick filter and the custom range reached the engine."
        );
    }

    /**
     * Dates submitted without the switch that turns the custom range on are
     * removed from the request altogether, rather than left lying around for
     * the next page of results to pick up.
     *
     * Observed on the rendered page rather than through an engine, because
     * without `fc=on` no engine would be sent the range either way — the
     * question is whether the request still carries it. It does not:
     * parts/custom-daterange.blade.php fills the two date inputs straight from
     * the request, and they come back empty.
     */
    public function testDatesWithoutTheCustomRangeSwitchAreRemovedFromTheRequest(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&ff=" . $this->daysAgo(60) . "&ft=" . $this->daysAgo(30));

        $response->assertOk();
        $response->assertSee('form="searchForm"  name="ff">', false);
        $response->assertSee('form="searchForm"  name="ft">', false);
    }

    /**
     * ...but a copy of them survives in the link to the settings page, which is
     * built from a URL snapshot taken before any of the scrubbing happens.
     *
     * MetaGer::parseFormData reads $this->fullUrl in its first few lines and
     * only removes the parameters afterwards, so `url=` on that link is the
     * request exactly as it arrived — including parameters the search itself
     * has decided to ignore. Characterizing, not endorsing: it is the reason to
     * be careful about *when* the scrub runs during the D7b extraction, not
     * only that it runs.
     */
    public function testTheScrubbedDatesSurviveInTheLinkToTheSettingsPage(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&ff=" . $this->daysAgo(60) . "&ft=" . $this->daysAgo(30));

        $response->assertOk();
        $response->assertSee(urlencode("ff=" . $this->daysAgo(60)), false);
    }

    // ----------------------------------------------------------- blacklists

    /**
     * `blacklist=` does not add to the `-site:` operators in the query — it
     * replaces them. A user who submits both gets only the parameter.
     *
     * The query asks for `-site:example.org` and the parameter names
     * beispiel.de, so if the two were merged nothing from the fixture would
     * survive at all.
     */
    public function testTheBlacklistParameterReplacesTheSiteOperatorsInTheQuery(): void
    {
        $links = $this->search("kaffee -site:example.org", ["blacklist" => "beispiel.de"]);

        $this->assertStringContainsString("example.org", $links, "The -site: operator in the query was applied as well as the parameter.");
        $this->assertStringNotContainsString("beispiel.de", $links, "The blacklist parameter was not applied.");
    }

    /**
     * A leading `*.` in the parameter means a whole domain rather than one
     * host, mirroring the `-site:*.domain` operator.
     */
    public function testAStarInTheBlacklistParameterMeansTheWholeDomain(): void
    {
        $links = $this->search("kaffee", ["blacklist" => "*.beispiel.de"]);

        $this->assertStringNotContainsString("beispiel.de", $links);
        $this->assertStringContainsString("example.org", $links);
    }

    /**
     * A comma separates several entries, and the two kinds may be mixed in one
     * parameter.
     */
    public function testTheBlacklistParameterTakesACommaSeparatedList(): void
    {
        $links = $this->search("kaffee", ["blacklist" => "*.beispiel.de, example.org"]);

        $this->assertSame("", trim($links), "Both blacklisted sites should have removed everything the fixture returns.");
    }

    // ------------------------------------------------------------ stopwords

    /**
     * `stop=` likewise replaces the `-word` stopwords in the query rather than
     * adding to them: `kaffee -espresso` with `stop=sorten` filters out the
     * result mentioning "sorten" and keeps the one about espresso.
     *
     * The branch also restores MetaGer::$q to the query as typed, operators and
     * all — and that half is deliberately not asserted, because it cannot be
     * observed: the query sent upstream is read from the SearchSettings
     * singleton, which is never stripped, and the cleaned $q reaches only the
     * quicktips lookup and the self-harm check. Removing that line changes
     * nothing any test or user can see, which is worth knowing before D7b
     * decides what SearchQuery should carry.
     */
    public function testTheStopParameterReplacesTheStopwordsInTheQuery(): void
    {
        $links = $this->search("kaffee -espresso", ["stop" => "sorten"]);

        $this->assertStringContainsString("espresso", $links, "The -espresso stopword in the query was applied as well as the parameter.");
        $this->assertStringNotContainsString("sorten", $links, "The stop parameter was not applied.");
    }

    public function testTheStopParameterTakesACommaSeparatedList(): void
    {
        $links = $this->search("kaffee", ["stop" => "sorten, espresso"]);

        $this->assertStringNotContainsString("sorten", $links);
        $this->assertStringNotContainsString("espresso", $links);
        $this->assertStringContainsString("example.org/kaffee", $links);
    }

    // ----------------------------------------------------------- prevention

    /**
     * A query that reads as a search about suicide or self-harm gets a link to
     * MetaGer's prevention page above the results.
     *
     * It is checked against the query *after* the operators have been stripped
     * out of it, which is why it belongs with the query parser rather than with
     * the view — and why moving the strip and the check apart would break it
     * quietly. Only an HTML page shows it: it lands in `htmlwarnings`, which
     * `out=json` does not publish.
     */
    public function testAQueryAboutSelfHarmIsAnsweredWithAPreventionLink(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $response = $this->get("/meta/meta.ger3?eingabe=" . urlencode("depressionen") . "&focus=web");

        $response->assertOk();
        $response->assertSee("prevention", false);
    }

    /**
     * The control. Without it the assertion above would pass on any page that
     * happens to link to the prevention page from its footer.
     */
    public function testAnOrdinaryQueryIsNotAnsweredWithAPreventionLink(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $response->assertOk();
        $response->assertDontSee("prevention", false);
    }

    /**
     * The trigger words are matched as whole words, so a query that merely
     * contains one as a substring is left alone.
     */
    public function testATriggerWordInsideALongerWordDoesNotCount(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $response = $this->get("/meta/meta.ger3?eingabe=" . urlencode("einsamkeitsforschung") . "&focus=web");

        $response->assertOk();
        $response->assertDontSee("prevention", false);
    }

    /**
     * Run a search and return the result links as one string.
     *
     * @param array<string, string> $parameters
     */
    private function search(string $query, array $parameters = []): string
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?" . http_build_query($parameters + [
            "eingabe" => $query,
            "focus" => "web",
            "out" => "json",
        ]));
        $response->assertOk();

        return collect($response->json("results"))->pluck("link")->implode(" ");
    }
}
