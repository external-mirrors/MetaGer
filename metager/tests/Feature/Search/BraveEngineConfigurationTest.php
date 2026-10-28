<?php

namespace Tests\Feature\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * What every Brave endpoint has to agree on, asserted across all three.
 *
 * `brave`, `brave_images` and `brave_news` are one API behind three parser
 * classes, and they used to state their shared facts three times. They drifted:
 * the day `ca-ES` was added, `Brave` learned that Brave's `ui_lang` enum does
 * not contain every MetaGer locale and its two siblings did not, so a Catalan
 * image or news search would have sent `ui_lang=ca-ES` — a value Brave does not
 * define. `BraveBase` holds the shared facts now; these tests are what say so,
 * and they run against all three endpoints rather than the one that happened to
 * be fixed.
 *
 * They assert the outgoing request rather than the parser, because the outgoing
 * request is the whole of what these settings do.
 */
class BraveEngineConfigurationTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @return array<string, array{0: string, 1: string}> fokus, engine
     */
    public static function endpoints(): array
    {
        return [
            "web" => ["web", "brave"],
            "images" => ["bilder", "brave_images"],
            "news" => ["nachrichten", "brave_news"],
        ];
    }

    /**
     * The URL the fetcher would request for $engine, or null if the search never
     * asked it — which is what a locale the engine does not support looks like
     * (`SearchengineConfiguration::applyLocale()` disables it outright).
     */
    private function requestUrlFor(string $locale, string $fokus, string $engine): ?string
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([]);

        $this->get("/$locale/meta/meta.ger3?eingabe=cafe&focus=$fokus");

        foreach ($fetcher->missions() as $mission) {
            if ($mission["name"] === $engine) {
                return $mission["url"];
            }
        }

        return null;
    }

    /**
     * Catalan is the market that only the shared region map supplies.
     *
     * `config/filters.json` has no `ca_ES` entry, so the market never arrives
     * through the parameter filter; the only thing that keeps these engines
     * alive for a Catalan user is `ca_ES` being in BraveBase::REGIONS. Until
     * this refactor that entry existed in `Brave`'s constructor and in the two
     * siblings' CONFIG_OVERLOAD, in three separate copies — deleting the
     * constructor without hoisting it would have disabled `brave` for every
     * Catalan search, and `brave` is the web fokus's only main engine.
     */
    #[DataProvider("endpoints")]
    public function testEveryBraveEndpointSearchesTheCatalanMarket(string $fokus, string $engine): void
    {
        $url = $this->requestUrlFor("ca-ES", $fokus, $engine);

        $this->assertNotNull($url, "$engine queued no request for a Catalan search, so ca_ES is missing from its region map.");
        // Brave takes the market as two parameters, so MetaGer's ca_ES is split.
        $this->assertStringContainsString("search_lang=ca", $url);
        $this->assertStringContainsString("country=ES", $url);
    }

    /**
     * Malaysia is in Brave's `country` enum (`MY`) and its `ui_lang` list
     * (`en-MY`), yet `en-MY` was a MetaGer locale no engine could search: the
     * market filter in `config/filters.json` already mapped `en_MY`, the region
     * map did not, and the region map is what decides.
     */
    #[DataProvider("endpoints")]
    public function testEveryBraveEndpointSearchesTheMalaysianMarket(string $fokus, string $engine): void
    {
        $url = $this->requestUrlFor("en-MY", $fokus, $engine);

        $this->assertNotNull($url, "$engine queued no request for a Malaysian search, so en_MY is missing from its region map.");
        $this->assertStringContainsString("search_lang=en", $url);
        $this->assertStringContainsString("country=MY", $url);
        $this->assertStringContainsString("ui_lang=en-MY", $url);
    }

    /**
     * Brave cannot search Ireland, so an Irish search gets English with no country.
     *
     * `country=IE` is rejected with a 422 — Brave's enum has `GB` and `ALL` but
     * no `IE`. `GB` would answer with the UK market; `ALL` with `search_lang=en`
     * is English results without a country preference, which is the honest
     * reading of "Ireland" for an engine that has no Ireland. Leaving Brave out
     * instead cost the en-IE user the web fokus's main engine. Serper still
     * carries the actual Irish market with `gl=ie`.
     *
     * `en-IE` is not in Brave's `ui_lang` list either, and the language fallback
     * would pick `en-AU`, the first `en` entry; UK English is the closer one.
     *
     * If Brave adds Ireland, map `en_IE` to it and change this test.
     */
    #[DataProvider("endpoints")]
    public function testEveryBraveEndpointSearchesIrelandAsGlobalEnglish(string $fokus, string $engine): void
    {
        $url = $this->requestUrlFor("en-IE", $fokus, $engine);

        $this->assertNotNull($url, "$engine queued no request for an Irish search, so en_IE is missing from its region map.");
        $this->assertStringContainsString("search_lang=en", $url);
        $this->assertStringContainsString("country=ALL", $url);
        $this->assertStringContainsString("ui_lang=en-GB", $url);
    }

    /**
     * The market filter has to agree with the region map: choosing "Ireland" in
     * the market filter on another locale must not disable Brave either.
     */
    #[DataProvider("endpoints")]
    public function testChoosingTheIrishMarketKeepsEveryBraveEndpoint(string $fokus, string $engine): void
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([]);

        $this->get("/de-DE/meta/meta.ger3?eingabe=cafe&focus=$fokus&m=en_IE");

        $url = null;
        foreach ($fetcher->missions() as $mission) {
            if ($mission["name"] === $engine) {
                $url = $mission["url"];
            }
        }

        $this->assertNotNull($url, "$engine was disabled by choosing the Irish market.");
        $this->assertStringContainsString("search_lang=en", $url);
        $this->assertStringContainsString("country=ALL", $url);
    }

    /**
     * Brave defines a fixed list of `ui_lang` values and `ca-ES` is not on it.
     * Leaving the parameter off lets Brave choose; sending an undefined value is
     * an error. This is the regression the split between the three classes hid.
     */
    #[DataProvider("endpoints")]
    public function testNoBraveEndpointSendsAnInterfaceLanguageBraveDoesNotDefine(string $fokus, string $engine): void
    {
        $url = $this->requestUrlFor("ca-ES", $fokus, $engine);

        $this->assertNotNull($url);
        $this->assertStringNotContainsString("ui_lang=", $url, "$engine asked Brave to answer in a language Brave does not list.");
    }

    /**
     * The other half of the same rule: a locale Brave *does* define is still
     * passed on, so the fix above did not simply stop sending the parameter.
     */
    #[DataProvider("endpoints")]
    public function testEveryBraveEndpointAsksForASupportedInterfaceLanguage(string $fokus, string $engine): void
    {
        $url = $this->requestUrlFor("de-DE", $fokus, $engine);

        $this->assertNotNull($url);
        $this->assertStringContainsString("ui_lang=de-DE", $url);
    }
}
