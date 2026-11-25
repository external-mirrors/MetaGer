<?php

namespace Tests\Feature\Search;

use app\Models\parserSkripte\PoliasNews;
use App\Models\Configuration\SearchEngineRegistry;
use App\Models\Configuration\Searchengines;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Polias' news search, an opt-in source in the news fokus: what MetaGer asks
 * it for, what it reads back, and what it costs.
 *
 * What it shares with the web search (dates, paging limits) is pinned in
 * PoliasEngineTest. This covers what differs.
 */
class PoliasNewsEngineTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /** @return array<string, mixed>|null */
    private function missionFor(string $engine, string $path, array $bodies = []): ?array
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses($bodies);

        // Redirects are followed because en-US is hidden from the URL.
        $this->followingRedirects()->get($path);

        foreach ($fetcher->missions() as $mission) {
            if ($mission["name"] === $engine) {
                return $mission;
            }
        }

        return null;
    }

    /** @return array<string, mixed>|null */
    private function newsMission(string $locale, string $parameters = ""): ?array
    {
        return $this->missionFor(
            "polias_news",
            "/$locale/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias_news=on$parameters",
        );
    }

    /** @return array<string, string> */
    private function parametersOf(array $mission): array
    {
        parse_str(parse_url($mission["url"], PHP_URL_QUERY), $parameters);
        return $parameters;
    }

    private function engine(): PoliasNews
    {
        return ((array) app(Searchengines::class)->sumas)["polias_news"];
    }

    private function fixtureWith(int $count, ?int $total = null): string
    {
        $fixture = json_decode($this->engineFixture("polias-news.json"), true);
        $fixture["news"]["results"] = array_slice($fixture["news"]["results"], 0, $count);
        if ($total !== null) {
            $fixture["news"]["total"] = $total;
        }
        return json_encode($fixture);
    }

    public function testItIsOffUntilTheUserTurnsItOn(): void
    {
        $this->assertNull(
            $this->missionFor("polias_news", "/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten"),
            "Polias is billed per request and must not run unless the user opted in.",
        );
    }

    public function testTheRequestAsksTheSearchEndpointForNews(): void
    {
        $mission = $this->newsMission("de-DE");

        $this->assertNotNull($mission, "An opted-in news search queued no Polias request.");
        $this->assertStringStartsWith("https://polias.eu/api/v1/search?", $mission["url"]);
        $parameters = $this->parametersOf($mission);
        $this->assertSame("haushalt", $parameters["q"]);
        $this->assertSame("news", $parameters["result_filter"]);
        $this->assertSame("de", $parameters["lang"]);
        $this->assertSame("0", $parameters["offset"]);
        $this->assertArrayNotHasKey("land", $parameters, "Polias' news ignores `land`.");
        $this->assertSame("application/json", $mission["headers"]["Accept"]);
    }

    /**
     * The web search's key works for news, so the news engine reads the
     * `polias` entry of sumas.json instead of needing one of its own.
     */
    public function testItUsesTheWebSearchKey(): void
    {
        $mission = $this->newsMission("de-DE");
        $key = app(SearchEngineRegistry::class)->sumas->polias->{"request-header"}->Authorization ?? null;

        $this->assertNotNull($key, "sumas.json has no Polias key to share.");
        $this->assertNotNull($mission);
        // assertTrue, not assertSame: a failure must not print the key.
        $this->assertTrue(
            ($mission["headers"]["Authorization"] ?? null) === $key,
            "The news request does not carry the `polias` entry's Authorization header.",
        );
    }

    public function testEachFokusAsksItsOwnPoliasEngine(): void
    {
        $this->assertNull(
            $this->missionFor("polias_news", "/de-DE/meta/meta.ger3?eingabe=haushalt&focus=web&polias_news=on"),
            "A web search asked Polias for news.",
        );
        $this->assertNull(
            $this->missionFor("polias", "/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias=on"),
            "A news search asked Polias' web index.",
        );
    }

    /** @return array<string, array{0: string, 1: string}> locale, lang */
    public static function supportedLocales(): array
    {
        return [
            "de-DE" => ["de-DE", "de"],
            "de-AT" => ["de-AT", "de"],
            "de-CH" => ["de-CH", "de"],
            "en-US" => ["en-US", "en"],
            "en-GB" => ["en-GB", "en"],
            "en-IE" => ["en-IE", "en"],
            "it-IT" => ["it-IT", "it"],
            "nl-NL" => ["nl-NL", "nl"],
            "pl-PL" => ["pl-PL", "pl"],
            "sv-SE" => ["sv-SE", "sv"],
        ];
    }

    #[DataProvider("supportedLocales")]
    public function testEachSupportedLocaleAsksForItsLanguage(string $locale, string $lang): void
    {
        $mission = $this->newsMission($locale);

        $this->assertNotNull($mission, "Polias queued no news request for a $locale search.");
        $this->assertSame($lang, $this->parametersOf($mission)["lang"]);
    }

    /**
     * Polias has no news in Catalan, and three and five articles for "ukraine"
     * in Spanish and Portuguese on 2026-11-25. French, Danish and Finnish
     * answered but were not announced.
     *
     * @return array<string, array{0: string}>
     */
    public static function unsupportedLocales(): array
    {
        return [
            "fr-FR" => ["fr-FR"],
            "es-ES" => ["es-ES"],
            "pt-PT" => ["pt-PT"],
            "da-DK" => ["da-DK"],
            "fi-FI" => ["fi-FI"],
            "ca-ES" => ["ca-ES"],
        ];
    }

    #[DataProvider("unsupportedLocales")]
    public function testAnUnsupportedLocaleLeavesItOut(string $locale): void
    {
        $this->assertNull($this->newsMission($locale));
    }

    public function testAChosenMarketPicksTheLanguage(): void
    {
        $mission = $this->newsMission("de-DE", "&m=pl_PL");

        $this->assertNotNull($mission);
        $this->assertSame("pl", $this->parametersOf($mission)["lang"]);
    }

    public function testAMarketOutsideItsCoverageLeavesItOut(): void
    {
        $this->assertNull($this->newsMission("de-DE", "&m=fr_FR"));
        $this->assertNull($this->newsMission("en-US", "&m=en_AU"), "Polias' English news is British, American and Irish.");
    }

    /**
     * The market filter only names languages the engine has. A market mapped
     * to anything else would send a `lang` Polias refuses.
     */
    public function testTheMarketFilterOnlyNamesSupportedLanguages(): void
    {
        $filter = json_decode(file_get_contents(config_path("filters.json")), true)
            ["parameter-filter"]["language"]["sumas"]["polias_news"];

        $this->assertSame("lang", $filter["get-parameter"]);
        foreach ($filter["values"] as $market => $lang) {
            $this->assertContains($lang, PoliasNews::LANGUAGES, "Market $market maps to [$lang].");
            $this->assertSame(PoliasNews::LANGUAGES[explode("_", $market)[0]], $lang, "Market $market.");
        }
    }

    /**
     * Asked with `freshness=pd` on 2026-11-25, Polias returned articles from
     * four days back.
     */
    public function testADateFilterLeavesItOut(): void
    {
        $this->assertNull($this->newsMission("de-DE", "&f=d"));
    }

    public function testASiteSearchLeavesItOut(): void
    {
        $this->assertNull($this->missionFor(
            "polias_news",
            "/de-DE/meta/meta.ger3?eingabe=" . urlencode("haushalt site:example.org") . "&focus=nachrichten&polias_news=on",
        ));
    }

    public function testResultsAreReadFromTheNewsBlock(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias_news" => $this->engineFixture("polias-news.json")]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias_news=on");

        $results = collect($this->engine()->results)->keyBy("originalLink");
        $this->assertCount(9, $results, "Nine of the ten fixture results have a URL; the tenth must be dropped.");

        $first = $results["https://nachrichten.example.de/politik/stromsteuer"];
        $this->assertSame("Haushaltsausschuss macht Weg für Stromsteuersenkung frei", $first->titel);
        $this->assertStringStartsWith("Der Haushaltsausschuss hat", $first->descr);
        $this->assertSame("Polias", $first->gefVon[0]);
        $this->assertSame(
            "2026-10-20T07:12:05+00:00",
            $first->additionalInformation["date"]->copy()->utc()->toIso8601String(),
            "page_age is German local time, as on the web.",
        );

        $bare = $results["https://zeitung2.example.de/politik/haushalt-2"];
        $this->assertSame("Ohne Anreißer & ohne Text", $bare->titel);
        $this->assertSame("", $bare->descr);
    }

    /** A body shaped like the web search's answer is not news. */
    public function testAWebShapedResponseYieldsNothing(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias_news" => $this->engineFixture("polias-web.json")]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias_news=on");

        $this->assertSame([], $this->engine()->results);
    }

    public function testAFullPageAsksForTheNextOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias_news" => $this->engineFixture("polias-news.json")]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias_news=on&m=sv_SE");

        $next = $this->engine()->next;
        $this->assertInstanceOf(PoliasNews::class, $next);
        $this->assertSame(10, $next->configuration->getParameter->offset);
        $this->assertSame("news", $next->configuration->getParameter->result_filter);
        $this->assertSame("sv", $next->configuration->getParameter->lang, "The next page lost the chosen market.");
    }

    public function testAShortPageIsTheLastOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias_news" => $this->fixtureWith(4)]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias_news=on");

        $this->assertNull($this->engine()->next ?? null);
    }

    public function testAFullPageThatReachesTheTotalIsTheLastOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias_news" => $this->fixtureWith(10, total: 10)]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias_news=on");

        $this->assertNull($this->engine()->next ?? null);
    }

    /** Polias bills news like web search: €0.001 a request, 0.2 Token. */
    public function testTurningItOnAddsPointTwoTokenToTheSearch(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten");
        $without = app(Searchengines::class)->getRawSearchCost();

        $this->forgetRequestScopedServices();
        $this->get("/de-DE/meta/meta.ger3?eingabe=haushalt&focus=nachrichten&polias_news=on");
        $with = app(Searchengines::class)->getRawSearchCost();

        $this->assertEqualsWithDelta(0.2, $with - $without, 0.0001);
    }
}
