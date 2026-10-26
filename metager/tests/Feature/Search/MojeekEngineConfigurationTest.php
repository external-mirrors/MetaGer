<?php

namespace Tests\Feature\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * What MetaGer asks Mojeek for, per market.
 *
 * Mojeek takes the market as two independent preferences: `lb`, a language
 * (ISO 639-1), and `rb`, a region (ISO 3166-1 alpha-2), each with a boost.
 * Neither is validated — an unknown value is silently ignored — so getting
 * them wrong does not fail, it just stops steering the results.
 *
 * Which is what happened. The region map was written against `lb`, so every
 * locale sent a *country* as its language: `lb=gb` for en-GB, `lb=us`,
 * `lb=at`, and `lb=ca` — Catalan — for fr-CA. Only de-DE was right, by
 * coincidence. The market filter wrote to `lr`, a language *restriction*, with
 * country codes too; `lr=GB` does nothing. Measured against the live API.
 *
 * Mojeek is disabled by default, hence `mojeek=on` on every search here.
 */
class MojeekEngineConfigurationTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * The query parameters MetaGer would send Mojeek, or null if the search
     * never asked it.
     *
     * @return array<string, string>|null
     */
    private function requestParametersFor(string $locale, string $extra = ""): ?array
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([]);

        // Redirects are followed because en-US is hidden from the URL.
        $this->followingRedirects()->get("/$locale/meta/meta.ger3?eingabe=cafe&mojeek=on$extra");

        foreach ($fetcher->missions() as $mission) {
            if ($mission["name"] === "mojeek") {
                parse_str(parse_url($mission["url"], PHP_URL_QUERY), $parameters);
                return $parameters;
            }
        }

        return null;
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}> locale, lb, rb
     */
    public static function markets(): array
    {
        return [
            "de-DE" => ["de-DE", "DE", "DE"],
            "en-GB" => ["en-GB", "EN", "GB"],
            "en-US" => ["en-US", "EN", "US"],
            "de-AT" => ["de-AT", "DE", "AT"],
            // At the default boost, rbb=10, the preference is mild: Mojeek's
            // index holds few Irish pages, and a stronger boost mostly demoted
            // UK sites rather than promoting Irish ones.
            "en-IE" => ["en-IE", "EN", "IE"],
            "en-MY" => ["en-MY", "EN", "MY"],
            // The two that a single country code cannot express: the language
            // is not the one the country's code would suggest.
            "fr-CA" => ["fr-CA", "FR", "CA"],
            "ca-ES" => ["ca-ES", "CA", "ES"],
            "pt-BR" => ["pt-BR", "PT", "BR"],
        ];
    }

    #[DataProvider("markets")]
    public function testEachLocaleAsksForItsLanguageAndItsRegion(string $locale, string $language, string $region): void
    {
        $parameters = $this->requestParametersFor($locale);

        $this->assertNotNull($parameters, "mojeek queued no request for a $locale search.");
        $this->assertSame($language, $parameters["lb"], "$locale should ask Mojeek to prefer $language pages.");
        $this->assertSame($region, $parameters["rb"], "$locale should ask Mojeek to prefer pages from $region.");
    }

    /**
     * Choosing a market moves both preferences, as it does on Brave and
     * Serper — a German interface searching the UK asks for English pages from
     * the UK — and never writes the language restriction `lr`, which would drop
     * every page not in that language rather than prefer the ones that are.
     */
    public function testAChosenMarketMovesLanguageAndRegion(): void
    {
        $parameters = $this->requestParametersFor("de-DE", "&m=en_GB");

        $this->assertNotNull($parameters);
        $this->assertSame("EN", $parameters["lb"]);
        $this->assertSame("GB", $parameters["rb"]);
        $this->assertArrayNotHasKey("lr", $parameters);
    }

    /**
     * The next page is built from a copy of this page's configuration, so it
     * keeps the market that was split into it — including a chosen one, which
     * the constructor used to overwrite from the interface locale.
     */
    public function testTheNextPageKeepsTheMarket(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["mojeek" => json_encode(["response" => [
            "status" => "OK",
            "head" => ["start" => 1, "return" => 10, "results" => 100],
            "results" => [],
        ]])]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=cafe&mojeek=on&m=en_GB");

        $mojeek = ((array) app(\App\Models\Configuration\Searchengines::class)->sumas)["mojeek"];
        $this->assertNotNull($mojeek->next, "A page with 90 more results asked for no next page.");
        $this->assertSame("EN", $mojeek->next->configuration->getParameter->lb);
        $this->assertSame("GB", $mojeek->next->configuration->getParameter->rb);
        $this->assertSame(11, $mojeek->next->configuration->getParameter->s);
    }

    /**
     * Every market Mojeek searches by locale, it also searches when chosen.
     *
     * The two lists are separate — the region map on the parser, the market
     * filter in `config/filters.json` — and a market missing from the filter
     * disables Mojeek with `INCOMPATIBLE_FILTER` for anyone who picks it. The
     * filter offered Mojeek three markets out of the twenty its region map had.
     */
    public function testTheMarketFilterOffersEveryMarketTheRegionMapHas(): void
    {
        $regions = array_keys(\app\Models\parserSkripte\Mojeek::CONFIG_OVERLOAD["mojeek"]["lang"]["regions"]);
        $filter = json_decode(file_get_contents(config_path("filters.json")), true)
            ["parameter-filter"]["language"]["sumas"]["mojeek"]["values"];

        foreach ($regions as $market) {
            $this->assertSame(
                $market,
                $filter[$market] ?? null,
                "Choosing the $market market disables Mojeek, though Mojeek searches $market by locale.",
            );
        }
    }
}
