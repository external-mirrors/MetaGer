<?php

namespace Tests\Feature\Search;

use app\Models\parserSkripte\Polias;
use App\Models\Configuration\Searchengines;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Polias, a German web index: what MetaGer asks it for, what it reads back,
 * and what it costs.
 *
 * Polias is disabled by default, so every search here that expects it to run
 * opts in with `polias=on`.
 */
class PoliasEngineTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * The mission MetaGer queued for Polias, or null if the search did not ask
     * Polias at all.
     *
     * @param array<string, string> $bodies
     * @return array<string, mixed>|null
     */
    private function poliasMission(string $path, array $bodies = []): ?array
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses($bodies);

        // Redirects are followed because en-US is hidden from the URL.
        $this->followingRedirects()->get($path);

        foreach ($fetcher->missions() as $mission) {
            if ($mission["name"] === "polias") {
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

    private function polias(): Polias
    {
        return ((array) app(Searchengines::class)->sumas)["polias"];
    }

    /** The fixture with its result list cut to the first $count entries. */
    private function fixtureWith(int $count, ?int $total = null): string
    {
        $fixture = json_decode($this->engineFixture("polias-web.json"), true);
        $fixture["results"] = array_slice($fixture["results"], 0, $count);
        if ($total !== null) {
            $fixture["total"] = $total;
        }
        return json_encode($fixture);
    }

    public function testPoliasIsOffUntilTheUserTurnsItOn(): void
    {
        $this->assertNull(
            $this->poliasMission("/de-DE/meta/meta.ger3?eingabe=kaffee"),
            "Polias is billed per request and must not run unless the user opted in.",
        );
    }

    public function testTheRequestGoesToTheSearchEndpoint(): void
    {
        $mission = $this->poliasMission("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on");

        $this->assertNotNull($mission, "An opted-in search queued no Polias request.");
        $this->assertStringStartsWith("https://polias.eu/api/v1/search?", $mission["url"]);
        $parameters = $this->parametersOf($mission);
        $this->assertSame("kaffee", $parameters["q"]);
        $this->assertSame("de", $parameters["lang"]);
        $this->assertSame("0", $parameters["offset"]);
        $this->assertSame("application/json", $mission["headers"]["Accept"]);
    }

    /**
     * Polias' general index is German. `land` prefers results from a country
     * without restricting to it.
     *
     * @return array<string, array{0: string, 1: string}> locale, land
     */
    public static function supportedLocales(): array
    {
        return [
            "de-DE" => ["de-DE", "de"],
            "de-AT" => ["de-AT", "at"],
            "de-CH" => ["de-CH", "ch"],
        ];
    }

    #[DataProvider("supportedLocales")]
    public function testEachGermanLocaleSearchesItsCountry(string $locale, string $land): void
    {
        $mission = $this->poliasMission("/$locale/meta/meta.ger3?eingabe=kaffee&polias=on");

        $this->assertNotNull($mission, "Polias queued no request for a $locale search.");
        $parameters = $this->parametersOf($mission);
        $this->assertSame($land, $parameters["land"]);
        $this->assertSame("de", $parameters["lang"]);
    }

    /**
     * Polias' only other index, `lang=en`, covers developer documentation and
     * IT, and its docs say it is not meant for general queries. Every
     * non-German locale leaves Polias out rather than answer from it.
     *
     * @return array<string, array{0: string}>
     */
    public static function unsupportedLocales(): array
    {
        return [
            "en-US" => ["en-US"],
            "en-GB" => ["en-GB"],
            "fr-FR" => ["fr-FR"],
            "es-ES" => ["es-ES"],
            "nl-NL" => ["nl-NL"],
        ];
    }

    #[DataProvider("unsupportedLocales")]
    public function testANonGermanLocaleLeavesItOut(string $locale): void
    {
        $this->assertNull($this->poliasMission("/$locale/meta/meta.ger3?eingabe=kaffee&polias=on"));
    }

    public function testAChosenMarketOverridesTheLocale(): void
    {
        $mission = $this->poliasMission("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on&m=de_CH");

        $this->assertNotNull($mission);
        $this->assertSame("ch", $this->parametersOf($mission)["land"]);
    }

    public function testAChosenNonGermanMarketLeavesItOut(): void
    {
        $this->assertNull($this->poliasMission("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on&m=en_US"));
    }

    /**
     * The country Polias uses for a locale is the one it uses when that market
     * is chosen in the filter. The two lists live in different files, and a
     * market missing from the filter disables Polias with
     * `INCOMPATIBLE_FILTER` for anyone who picks it.
     */
    public function testTheMarketFilterMatchesTheRegionMap(): void
    {
        $filter = json_decode(file_get_contents(config_path("filters.json")), true)
            ["parameter-filter"]["language"]["sumas"]["polias"];

        $this->assertSame("land", $filter["get-parameter"]);
        $this->assertEquals(Polias::REGIONS, $filter["values"]);
    }

    /**
     * Polias ignores `site:`: asked for "kaffee site:tchibo.de" on
     * 2026-11-04, it answered with kaffeegemeinde.de and city-nms.de among
     * the first five. Sent anyway, the filter would quietly not filter.
     */
    public function testASiteSearchLeavesItOut(): void
    {
        $this->assertNull(
            $this->poliasMission("/de-DE/meta/meta.ger3?eingabe=" . urlencode("kaffee site:example.org") . "&polias=on"),
        );
    }

    public function testSafesearchOffIsPassedOn(): void
    {
        $mission = $this->poliasMission("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on&s=o");

        $this->assertNotNull($mission);
        $this->assertSame("0", $this->parametersOf($mission)["safe"]);
    }

    public function testStrictSafesearchIsPassedOn(): void
    {
        $mission = $this->poliasMission("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on&s=s");

        $this->assertNotNull($mission);
        $this->assertSame("1", $this->parametersOf($mission)["safe"]);
    }

    public function testResultsAreReadFromTheResponse(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias" => $this->engineFixture("polias-web.json")]);

        $response = $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web&polias=on&out=json");
        $response->assertOk();

        $results = collect($response->json("results"))
            ->filter(fn(array $r) => in_array("Polias", array_column($r["engines"], "name"), true))
            ->keyBy("link");

        $this->assertCount(9, $results, "Nine of the ten fixture results have a URL; the tenth must be dropped.");

        $kaffee = $results["https://example.org/kaffee"];
        $this->assertSame("Kaffee Zubereitung | Zubereitungsarten", $kaffee["title"]);
        $this->assertSame("Wir vermitteln dir in unserem Ratgeber die gängigsten Methoden der Kaffeezubereitung.", $kaffee["description"]);
        $this->assertNull($kaffee["date"]);

        $news = $results["https://nachrichten.example.de/wirtschaft/kaffeepreise"];
        $this->assertNotNull($news["date"], "page_age was not read as the result's date.");
        $this->assertStringContainsString("2026-10-20", $news["date"]);
    }

    /**
     * `page_age` carries no offset. Polias documents it as German local time,
     * so 09:12 in October is 07:12 UTC, not 09:12 UTC.
     */
    public function testPageAgeIsGermanLocalTime(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias" => $this->engineFixture("polias-web.json")]);
        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on");

        $news = collect($this->polias()->results)
            ->first(fn($result) => $result->originalLink === "https://nachrichten.example.de/wirtschaft/kaffeepreise");

        $this->assertNotNull($news);
        $this->assertSame(
            "2026-10-20T07:12:05+00:00",
            $news->additionalInformation["date"]->copy()->utc()->toIso8601String(),
        );
    }

    public function testAFullPageAsksForTheNextOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias" => $this->engineFixture("polias-web.json")]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on&m=de_AT");

        $next = $this->polias()->next;
        $this->assertNotNull($next, "A full page of ten asked for no next page.");
        $this->assertSame(10, $next->configuration->getParameter->offset);
        $this->assertSame("at", $next->configuration->getParameter->land, "The next page lost the chosen market.");
    }

    public function testAShortPageIsTheLastOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias" => $this->fixtureWith(4)]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on");

        // `next` is a dynamic property, only set when there is a next page.
        $this->assertNull($this->polias()->next ?? null);
    }

    public function testAFullPageThatReachesTheTotalIsTheLastOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias" => $this->fixtureWith(10, total: 10)]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on");

        $this->assertNull($this->polias()->next ?? null);
    }

    /**
     * Polias stops at offset 90. Asked for offset 100 on 2026-11-04 it did not
     * refuse, it answered with results 91 to 100 again, so a page past 90
     * would only repeat the last one.
     */
    public function testThereIsNoPageAfterOffsetNinety(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["polias" => $this->engineFixture("polias-web.json")]);
        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on");

        $polias = $this->polias();
        $polias->configuration->getParameter->offset = Polias::MAX_OFFSET;
        unset($polias->next);
        $polias->getNext(app(\App\MetaGer::class), $this->engineFixture("polias-web.json"));

        $this->assertNull($polias->next ?? null);
    }

    /**
     * Polias bills €1 per 1,000 searches, €0.001 each. At our usual 100%
     * markup that is 0.2 Token, added to the search's cost whenever the user
     * turns Polias on.
     */
    public function testTurningPoliasOnAddsPointTwoTokenToTheSearch(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee");
        $without = app(Searchengines::class)->getRawSearchCost();

        $this->forgetRequestScopedServices();
        $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&polias=on");
        $with = app(Searchengines::class)->getRawSearchCost();

        $this->assertEqualsWithDelta(0.2, $with - $without, 0.0001);
    }
}
