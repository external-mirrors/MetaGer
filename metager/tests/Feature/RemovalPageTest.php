<?php

namespace Tests\Feature;

use App\Http\Controllers\RemovalController;
use App\Models\Configuration\SearchEngineRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * /entfernen — the guide to where a search result can actually be removed.
 *
 * The page is the target of suma-crm's replies: POST /api/blocklist/check
 * names the engines a result came from (docs/blocklist-api.md), and the reply
 * links each one to /entfernen#<anchor> through config/removal_providers.php.
 * So the two things that must not drift are that every engine has a provider,
 * and every provider's anchor exists on the page.
 */
class RemovalPageTest extends TestCase
{
    /** Sources whose engines were removed from MetaGer in November 2026. */
    private const GONE = ["yandex", "onenewspage", "onenewspagegermany", "ebay", "tuhh", "minism_science"];

    /** @return array<string, array{0: string}> */
    public static function fokiEngines(): array
    {
        $foki = json_decode(file_get_contents(dirname(__DIR__, 2) . "/config/foki.json"), true);
        $engines = [];
        foreach ($foki as $fokus) {
            foreach ($fokus["sumas"] as $engine) {
                $engines[$engine] = [$engine];
            }
        }
        return $engines;
    }

    /** An engine that left foki.json takes its mapping with it. */
    public function testEveryMappedEngineIsInAFokus(): void
    {
        foreach (array_keys(config("removal_providers.engines")) as $engine) {
            $this->assertArrayHasKey($engine, self::fokiEngines(), "config/removal_providers.php maps [$engine], which no fokus in config/foki.json uses.");
        }
    }

    /** And a provider no engine points at has nobody to send anyone to. */
    public function testEveryProviderHasAnEngine(): void
    {
        $used = array_unique(array_values(config("removal_providers.engines")));
        foreach (array_keys(config("removal_providers.providers")) as $id) {
            $this->assertContains($id, $used, "Provider [$id] has no engine mapped to it.");
        }
    }

    #[DataProvider("fokiEngines")]
    public function testEveryEngineInAFokusHasAProvider(string $engine): void
    {
        $this->assertArrayHasKey($engine, config("removal_providers.engines"), "config/removal_providers.php has no entry for engine [$engine] from config/foki.json.");
        $providerId = config("removal_providers.engines.$engine");

        $this->assertIsArray(
            config("removal_providers.providers.$providerId"),
            "Engine [$engine] points at provider [$providerId], which config/removal_providers.php does not define."
        );
    }

    /**
     * Parser files are loaded whether or not a fokus uses them (CLAUDE.md,
     * "Configuration"), so an engine can return results before anyone adds it
     * to foki.json. It still needs somewhere to send people.
     */
    public function testEveryRegisteredEngineHasAProvider(): void
    {
        $engines = array_keys((array) (new SearchEngineRegistry())->sumas);
        $missing = array_diff($engines, array_keys(config("removal_providers.engines")));

        $this->assertSame([], array_values($missing), "Engines without a removal provider.");
    }

    public function testProvidersAreWellFormed(): void
    {
        $anchors = [];
        foreach (config("removal_providers.providers") as $id => $provider) {
            $this->assertContains($provider["type"], ["provider", "website", "metager"], "[$id] type");
            $this->assertNotEmpty($provider["name"], "[$id] name");
            $this->assertMatchesRegularExpression('/^[a-z][a-z0-9-]*$/', $provider["anchor"], "[$id] anchor");
            $this->assertSame(RemovalController::KINDS, array_keys($provider["links"]), "[$id] links");
            foreach ([...array_values($provider["links"]), $provider["contact"]] as $url) {
                if ($url !== null) {
                    $this->assertStringStartsWith("https://", $url, "[$id]");
                }
            }
            $this->assertNotSame($provider["note"], __($provider["note"], [], "de"), "[$id] note has no German text");
            $this->assertNotSame($provider["note"], __($provider["note"], [], "en"), "[$id] note has no English text");
            if ($provider["type"] === "provider" && array_filter($provider["links"]) === []) {
                $this->assertNotEmpty($provider["contact"], "[$id] has no form and no contact: the card would have nowhere to send people.");
            }
            $anchors[] = $provider["anchor"];
        }

        $this->assertSame($anchors, array_unique($anchors), "Anchors must be unique.");
        $this->assertSame(1, count(array_filter(
            config("removal_providers.providers"),
            fn($p) => $p["type"] === "website"
        )), "Exactly one website provider: its anchor is the page's first step.");
    }

    /**
     * provider id => provider, for the providers MetaGer still gets results
     * from: those with at least one engine in a fokus that its parser does not
     * switch off (CONFIG_OVERLOAD `disabled`; sumas.json cannot turn it back
     * on). Mojeek and Staan are only off *by default* — a user can opt in, so
     * they count. The website step is always there.
     *
     * Worked out here independently of RemovalController, from foki.json and
     * the registry, so the test is not the controller checking itself.
     *
     * @return array<string, array>
     */
    private function providersInUse(): array
    {
        $registry = new SearchEngineRegistry();
        $providers = config("removal_providers.providers");
        $inUse = [];
        foreach ($registry->foki as $fokus) {
            foreach ($fokus->sumas as $engine) {
                $id = config("removal_providers.engines.$engine");
                if ($id !== null && !($registry->sumas->{$engine}->disabled ?? false)) {
                    $inUse[$id] = $providers[$id];
                }
            }
        }
        foreach ($providers as $id => $provider) {
            if ($provider["type"] === "website") {
                $inUse[$id] = $provider;
            }
        }
        return $inUse;
    }

    public function testEveryProviderInUseHasItsAnchorOnThePage(): void
    {
        $page = $this->get("/de-DE/entfernen")->assertOk();

        foreach ($this->providersInUse() as $provider) {
            $page->assertSee('id="' . $provider["anchor"] . '"', false);
        }
    }

    /**
     * An engine switched off in its parser (CONFIG_OVERLOAD `disabled`) sends
     * no results, so its provider gets no card until it is switched on again.
     * No engine is switched off today; Yandex and One News Page were, until
     * they were removed.
     */
    public function testProvidersWeGetNoResultsFromAreNotShown(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();
        $inUse = $this->providersInUse();

        foreach (config("removal_providers.providers") as $id => $provider) {
            if (!isset($inUse[$id])) {
                $this->assertStringNotContainsString('id="' . $provider["anchor"] . '"', $content, "[$id] has no engine in use but is on the page.");
                $this->assertStringNotContainsString(e($provider["name"]), $content, "[$id] has no engine in use but is named on the page.");
            }
        }
    }

    public function testEveryProviderFormIsLinked(): void
    {
        $page = $this->get("/de-DE/entfernen")->assertOk();

        foreach ($this->providersInUse() as $provider) {
            foreach ($provider["links"] as $url) {
                if ($url !== null) {
                    $page->assertSee('href="' . e($url) . '"', false);
                }
            }
        }
        foreach (config("removal_providers.hotlines") as $hotline) {
            $page->assertSee('href="' . e($hotline["url"]) . '"', false);
        }
    }

    public function testThePageRendersInGerman(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertSee("<title>" . e(trans("removal.title", [], "de")) . "</title>", false)
            ->assertSeeText(trans("removal.website.heading", [], "de"))
            ->assertSeeText(trans("removal.providers.google", [], "de"));
    }

    /** en-US is the default locale, so its prefix is redirected out of the path. */
    public function testThePageRendersInEnglish(): void
    {
        $this->get("/en-US/entfernen", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect("/entfernen");

        $this->get("/entfernen")
            ->assertOk()
            ->assertSee("<title>" . e(trans("removal.title", [], "en")) . "</title>", false)
            ->assertSeeText(trans("removal.website.heading", [], "en"));
    }

    /**
     * The page exists in de, en and ca. The app's fallback locale is `de`,
     * which for this page is the wrong answer to a French visitor; the
     * controller switches the fallback to English where removal.php is
     * missing.
     */
    public function testALanguageWithoutTheTextFallsBackToEnglish(): void
    {
        $this->assertFileDoesNotExist(lang_path("fr/removal.php"));

        $this->get("/fr-FR/entfernen")
            ->assertOk()
            ->assertSeeText(trans("removal.website.heading", [], "en"))
            ->assertDontSeeText(trans("removal.website.heading", [], "de"));
    }

    /** No script on the page: it has to work without JS. */
    public function testThePageNeedsNoScriptOfItsOwn(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();
        $start = strpos($content, '<div id="removal">');

        $this->assertNotFalse($start);
        $this->assertStringNotContainsString("<script", substr($content, $start));
        $this->assertStringContainsString("<details", $content);
    }

    public function testThePageLinksToTheContactPage(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertSee('href="' . route("contact") . '"', false);
    }

    /**
     * The page recommends asking at the source first, but it must not make
     * that a condition for us to act. A search engine's duty to delist under
     * Art. 17 GDPR stands on its own: the person need not go to the publisher
     * first, and the duty holds even when the page stays up (CJEU C-131/12,
     * Google Spain, paras. 85-88). The first version said we only hide a
     * result "where removal at the source is not possible", which read as
     * turning valid requests away.
     */
    public function testWritingToUsDoesNotRequireAskingTheSourceFirst(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertDontSeeText("an der Quelle nicht möglich ist")
            ->assertSeeText("Steht Ihnen gegenüber MetaGer ein Anspruch zu")
            ->assertSeeText("müssen Sie sich nicht zuerst an die Webseite oder die Suchmaschinen wenden");
    }

    /**
     * Art. 12(3) GDPR: an answer within a month. suma-crm's removal cases run
     * on the same deadline (RemovalRequest::deadline_at). Where to complain
     * (Art. 12(4)) belongs in the reply that refuses a request, not on this
     * page (team feedback, 2026-11-04).
     */
    public function testWritingToUsNamesTheDeadline(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertSeeText("innerhalb eines Monats")
            ->assertDontSeeText("Aufsichtsbehörde")
            ->assertDontSeeText("vor Gericht");
    }

    /** One request per test: a second one is routed against the first one's locale-stripped state and 404s. */
    public function testWritingToUsNamesTheDeadlineInEnglish(): void
    {
        $this->followingRedirects()->get("/en-US/entfernen", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSeeText("within one month")
            ->assertDontSeeText("supervisory authority")
            ->assertDontSeeText("go to court");
    }

    /**
     * The page used to say MetaGer keeps no copy of the results. It caches
     * every engine's answer, for up to an hour by default
     * (SearchengineConfiguration::$cacheDuration), and the result page keeps
     * what it showed for another hour so "load more" can rebuild it
     * (MetaGerSearch, "loader_<uid>"). The page says "a few hours", as the
     * privacy policy does (privacy.contexts.search.query); this fails when an
     * engine in use is cached for longer than the hour that wording assumes.
     */
    public function testThePageDoesNotUnderstateHowLongWeCacheResults(): void
    {
        $registry = new SearchEngineRegistry();
        foreach ($registry->foki as $fokus) {
            foreach ($fokus->sumas as $engine) {
                $config = $registry->sumas->{$engine};
                if ($config->disabled ?? false) {
                    continue;
                }
                $this->assertLessThanOrEqual(
                    60,
                    (new \App\Models\SearchengineConfiguration($config))->cacheDuration,
                    "[$engine] is cached for longer than an hour; lang/*/removal.php providers.text says \"a few hours\".",
                );
            }
        }

        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertDontSeeText("MetaGer speichert diese Ergebnisse nicht selbst")
            ->assertDontSeeText("höchstens eine Stunde")
            ->assertSeeText("für wenige Stunden");
    }

    /**
     * "Eine eigene Sammlung dieser Ergebnisse" is not how anyone in Germany
     * talks about a search index. The step says what MetaGer does instead.
     */
    public function testTheProviderStepSaysWhatMetaGerDoesInPlainWords(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertDontSeeText("Sammlung")
            ->assertSeeText("MetaGer durchsucht das Web nicht selbst");
    }

    /**
     * The card says Google's results come through Serper and what Google
     * removes is gone here too. Whether Google's Europe-only delisting
     * covers Serper's queries is ours to check when someone writes, not a
     * caveat for the card.
     */
    public function testTheGoogleCardDoesNotDiscussSerpersLocation(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertSeeText("MetaGer bezieht Googles Ergebnisse über den Dienst Serper")
            ->assertDontSeeText("nur für Suchende in Europa")
            ->assertDontSeeText("können wir nicht zusagen");
    }

    /** Removed sources leave no card, no mapping and no text; TootNews stays. */
    public function testRemovedSourcesAreNotOnThePage(): void
    {
        foreach (self::GONE as $engine) {
            $this->assertArrayNotHasKey($engine, config("removal_providers.engines"), "[$engine] is still mapped.");
        }
        foreach (["yandex", "onenewspage", "ebay"] as $provider) {
            $this->assertArrayNotHasKey($provider, config("removal_providers.providers"));
            $this->assertArrayNotHasKey($provider, __("removal.providers"));
        }

        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();
        $start = strpos($content, '<div id="removal">');
        $page = substr($content, $start);

        foreach (["eBay", "TUBdok", "Minisucher", "VeRO", "Yandex", "One News Page"] as $gone) {
            $this->assertStringNotContainsStringIgnoringCase($gone, $page, "$gone is still named on the page.");
        }
        $this->assertStringContainsString("TootNews", $page);
        $this->assertStringContainsString("BASE", $page);
    }

    /**
     * Polias has no form. Its FAQ ("Wie lasse ich eine Seite aus den
     * Ergebnissen entfernen?", polias.eu/fragen, checked 2026-11-06) asks
     * for an email to hallo@polias.eu with the page's address and the reason.
     */
    public function testThePoliasCardSaysToWriteToPolias(): void
    {
        $this->assertSame("polias", config("removal_providers.engines.polias"));
        $polias = config("removal_providers.providers.polias");
        $this->assertSame("https://polias.eu/fragen", $polias["contact"]);
        $this->assertSame([null], array_values(array_unique($polias["links"])));

        $page = $this->get("/de-DE/entfernen")->assertOk();
        $page->assertSee('id="polias"', false);
        $page->assertSee('href="https://polias.eu/fragen"', false);
        $page->assertSeeText("hallo@polias.eu");
    }

    /** Staan has its own removal form, for personal data only. */
    public function testTheStaanCardLinksStaansOwnRemovalForm(): void
    {
        $this->assertSame("https://staan.ai/removal", config("removal_providers.providers.staan.links.personal_data"));
        $this->assertSame("Staan", config("removal_providers.providers.staan.name"));

        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertSee('href="https://staan.ai/removal"', false)
            ->assertDontSee("report.qwant.com/removal", false);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function guideAndContactTexts(): array
    {
        $cases = [];
        foreach (glob(dirname(__DIR__, 2) . "/lang/*/{removal,kontakt}.php", GLOB_BRACE) as $file) {
            if (is_link(dirname($file))) {
                continue;
            }
            $cases[basename(dirname($file)) . "/" . basename($file)] = [$file];
        }
        return $cases;
    }

    /** House style: no em dashes in what the guide and contact pages say. */
    #[DataProvider("guideAndContactTexts")]
    public function testTheTextsUseNoEmDash(string $file): void
    {
        $strings = [];
        $data = require $file;
        array_walk_recursive($data, function ($value) use (&$strings) {
            $strings[] = $value;
        });

        foreach ($strings as $string) {
            $this->assertStringNotContainsString("—", (string) $string, basename(dirname($file)) . "/" . basename($file));
        }
    }

    /**
     * Not wanting the content is right; discouraging the address was not. A
     * search engine that is told about a clearly illegal result has to act
     * on it, and the page must not look as if we would rather not know.
     */
    public function testIllegalContentAsksForTheAddressButNeverTheContent(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertDontSeeText("weder die Inhalte noch Links darauf")
            ->assertSeeText("nennen Sie uns nur seine Adresse (URL) als Text")
            ->assertSeeText("nie die Inhalte selbst");
    }

    /**
     * suma-crm may ask for proof of identity and keeps it with the case;
     * otherwise anyone could have any address removed. The page says so up
     * front, with what we need it for and that it can be blacked out
     * (Art. 12(6) GDPR, data minimisation), right after what to include.
     */
    public function testWritingToUsSaysWhenWeAskForProofOfIdentity(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();

        $this->assertStringContainsString("etwa eine Kopie Ihres Ausweises", $content);
        $this->assertStringContainsString("schwärzen", $content);
        $this->assertStringContainsString("mit Ihren übrigen Angaben", $content);
        $this->assertLessThan(
            strpos($content, "Kopie Ihres Ausweises"),
            strpos($content, "die genauen Adressen (URLs)"),
            "The identity note belongs after what to include in the mail.",
        );
    }
}
