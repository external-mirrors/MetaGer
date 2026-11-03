<?php

namespace Tests\Feature\Search;

use app\Models\parserSkripte\Staan;
use App\Models\Configuration\Searchengines;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Staan, the web index behind Qwant and Ecosia: what MetaGer asks it for, what
 * it reads back, and what it costs.
 *
 * Staan is disabled by default, so every search here that expects it to run
 * opts in with `staan=on`.
 */
class StaanEngineTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * The mission MetaGer queued for Staan, or null if the search did not ask
     * Staan at all.
     *
     * @param array<string, string> $bodies
     * @return array<string, mixed>|null
     */
    private function staanMission(string $path, array $bodies = []): ?array
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses($bodies);

        // Redirects are followed because en-US is hidden from the URL.
        $this->followingRedirects()->get($path);

        foreach ($fetcher->missions() as $mission) {
            if ($mission["name"] === "staan") {
                return $mission;
            }
        }

        return null;
    }

    /** @return array<string, string> */
    private function parametersOf(array $mission): array
    {
        parse_str(parse_url($mission["url"], PHP_URL_QUERY), $parameters);
        return $parameters;
    }

    private function staan(): Staan
    {
        return ((array) app(Searchengines::class)->sumas)["staan"];
    }

    public function testStaanIsOffUntilTheUserTurnsItOn(): void
    {
        $this->assertNull(
            $this->staanMission("/de-DE/meta/meta.ger3?eingabe=kaffee"),
            "Staan is billed per request and must not run unless the user opted in.",
        );
    }

    public function testTheRequestGoesToTheWebSearchEndpoint(): void
    {
        $mission = $this->staanMission("/de-DE/meta/meta.ger3?eingabe=kaffee&staan=on");

        $this->assertNotNull($mission, "An opted-in search queued no Staan request.");
        $this->assertStringStartsWith("https://api.staan.ai/v2/search/web?", $mission["url"]);
        $parameters = $this->parametersOf($mission);
        $this->assertSame("kaffee", $parameters["q"]);
        $this->assertSame("0", $parameters["offset"]);
        $this->assertSame("application/json", $mission["headers"]["Accept"]);
    }

    /**
     * Staan has French, German and English markets only. German and French
     * outside their home country use the home market, which is what Staan's
     * FAQ recommends for Austria.
     *
     * @return array<string, array{0: string, 1: string}> locale, market
     */
    public static function supportedLocales(): array
    {
        return [
            "de-DE" => ["de-DE", "de-de"],
            "de-AT" => ["de-AT", "de-de"],
            "de-CH" => ["de-CH", "de-de"],
            "fr-FR" => ["fr-FR", "fr-fr"],
            "fr-CA" => ["fr-CA", "fr-fr"],
            "en-US" => ["en-US", "en-us"],
            "en-GB" => ["en-GB", "en-gb"],
            "en-IE" => ["en-IE", "en-ie"],
            "en-AU" => ["en-AU", "en-au"],
        ];
    }

    #[DataProvider("supportedLocales")]
    public function testEachLocaleSearchesItsMarket(string $locale, string $market): void
    {
        $mission = $this->staanMission("/$locale/meta/meta.ger3?eingabe=kaffee&staan=on");

        $this->assertNotNull($mission, "Staan queued no request for a $locale search.");
        $this->assertSame($market, $this->parametersOf($mission)["market"]);
    }

    /**
     * Staan does not index these languages. Searching its English or French
     * index for a Spanish user would answer in the wrong language, so the
     * engine stays out of the search.
     *
     * @return array<string, array{0: string}>
     */
    public static function unsupportedLocales(): array
    {
        return [
            "es-ES" => ["es-ES"],
            "it-IT" => ["it-IT"],
            "nl-NL" => ["nl-NL"],
            "pl-PL" => ["pl-PL"],
            // English, but Staan has no Malaysian market.
            "en-MY" => ["en-MY"],
        ];
    }

    #[DataProvider("unsupportedLocales")]
    public function testALocaleStaanDoesNotIndexLeavesItOut(string $locale): void
    {
        $this->assertNull($this->staanMission("/$locale/meta/meta.ger3?eingabe=kaffee&staan=on"));
    }

    public function testAChosenMarketOverridesTheLocale(): void
    {
        $mission = $this->staanMission("/de-DE/meta/meta.ger3?eingabe=kaffee&staan=on&m=en_NZ");

        $this->assertNotNull($mission);
        $this->assertSame("en-nz", $this->parametersOf($mission)["market"]);
    }

    /**
     * The market Staan uses for a locale is the one it uses when that market is
     * chosen in the filter. The two lists live in different files, and a market
     * missing from the filter disables Staan with `INCOMPATIBLE_FILTER` for
     * anyone who picks it.
     */
    public function testTheMarketFilterMatchesTheRegionMap(): void
    {
        $filter = json_decode(file_get_contents(config_path("filters.json")), true)
            ["parameter-filter"]["language"]["sumas"]["staan"];

        $this->assertSame("market", $filter["get-parameter"]);
        $this->assertEquals(Staan::REGIONS, $filter["values"]);
    }

    public function testASiteSearchIsPassedOnAsAnOperator(): void
    {
        $mission = $this->staanMission("/de-DE/meta/meta.ger3?eingabe=" . urlencode("kaffee site:example.org") . "&staan=on");

        $this->assertNotNull($mission, "A site: search disabled Staan, which supports the operator.");
        $this->assertStringContainsString("site:example.org", $this->parametersOf($mission)["q"]);
    }

    public function testResultsAreReadFromTheResponse(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["staan" => $this->engineFixture("staan-web.json")]);

        $response = $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web&staan=on&out=json");
        $response->assertOk();

        $results = collect($response->json("results"))
            ->filter(fn(array $r) => in_array("Staan", array_column($r["engines"], "name"), true))
            ->keyBy("link");

        $this->assertCount(9, $results, "Nine of the ten fixture results have a URL; the tenth must be dropped.");

        $kaffee = $results["https://example.org/kaffee"];
        $this->assertSame("Kaffee & Zubereitung", $kaffee["title"]);
        $this->assertSame("Alles über Kaffee, Röstung und Zubereitung.", $kaffee["description"]);
        // Staan sends "example.org > kaffee"; the address is built from the
        // link like every other engine's (Search\DisplayUrl).
        $this->assertSame("example.org › kaffee", $kaffee["displayLink"]);
        $this->assertNotNull($kaffee["date"], "published_date was not read as the result's date.");

        $sorten = $results["https://beispiel.de/sorten"];
        $this->assertSame("Kaffeesorten im Vergleich – Beispiel", $sorten["title"], "A line break inside a title must not reach the page.");
        $this->assertNotNull($sorten["image"], "The thumbnail was not attached to the result.");
        $this->assertNull($sorten["date"]);
    }

    public function testAFullPageAsksForTheNextOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["staan" => $this->engineFixture("staan-web.json")]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&staan=on&m=en_GB");

        $next = $this->staan()->next;
        $this->assertNotNull($next, "A full page of ten asked for no next page.");
        $this->assertSame(10, $next->configuration->getParameter->offset);
        $this->assertSame("en-gb", $next->configuration->getParameter->market, "The next page lost the chosen market.");
    }

    public function testAShortPageIsTheLastOne(): void
    {
        $fixture = json_decode($this->engineFixture("staan-web.json"), true);
        $fixture["web"]["results"] = array_slice($fixture["web"]["results"], 0, 4);

        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["staan" => json_encode($fixture)]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&staan=on");

        // `next` is a dynamic property, only set when there is a next page.
        $this->assertNull($this->staan()->next ?? null);
    }

    /** Staan rejects an offset over 30, so the fourth page is the last. */
    public function testThereIsNoPageAfterOffsetThirty(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["staan" => $this->engineFixture("staan-web.json")]);
        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&staan=on");

        $staan = $this->staan();
        $staan->configuration->getParameter->offset = Staan::MAX_OFFSET;
        unset($staan->next);
        $staan->getNext(app(\App\MetaGer::class), $this->engineFixture("staan-web.json"));

        $this->assertNull($staan->next ?? null);
    }

    /**
     * Staan bills €2 per 1,000 requests, €0.004 each. At our usual 100% markup
     * that is 0.4 Token, added to the search's cost whenever the user turns
     * Staan on.
     */
    public function testTurningStaanOnAddsPointFourTokenToTheSearch(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee");
        $without = app(Searchengines::class)->getRawSearchCost();

        $this->forgetRequestScopedServices();
        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&staan=on");
        $with = app(Searchengines::class)->getRawSearchCost();

        $this->assertEqualsWithDelta(0.4, $with - $without, 0.0001);
    }
}
