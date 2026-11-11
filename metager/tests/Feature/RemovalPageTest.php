<?php

namespace Tests\Feature;

use App\Http\Controllers\RemovalController;
use App\Models\Configuration\SearchEngineRegistry;
use Illuminate\Support\Facades\Vite;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * /entfernen — the guide to where a search result can actually be removed.
 *
 * suma-crm links here: POST /api/blocklist/check names the engines a result
 * came from (docs/blocklist-api.md), each linked to /entfernen#<anchor>
 * through config/removal_providers.php. So the two things that must not drift
 * are that every engine has a provider, and every provider's anchor exists on
 * the page.
 *
 * We take no removal requests ourselves (decided 2026-11-11): the page lists
 * where each source is delisted, SUMA-EV's own searches included, and links
 * no form of ours. suma-crm's form is kept but switched off.
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
     * The guide once existed only in de, en and ca, and the controller sent
     * everyone else to English. Every locale now has its own text, so a
     * regional URL renders that language, and nothing falls back.
     */
    public function testAFrenchVisitorGetsTheGuideInFrench(): void
    {
        $french = trans("removal.website.heading", [], "fr");
        $this->assertNotSame(trans("removal.website.heading", [], "en"), $french);

        $this->get("/fr-FR/entfernen")
            ->assertOk()
            ->assertSeeText($french)
            ->assertDontSeeText(trans("removal.website.heading", [], "en"))
            ->assertDontSeeText(trans("removal.website.heading", [], "de"));
    }

    /** The guide is complete in the markup; its script only adds hints. */
    public function testTheGuideWorksWithoutItsScript(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();
        $start = strpos($content, '<div id="removal">');

        $this->assertNotFalse($start);
        $this->assertStringNotContainsString("<script", substr($content, $start));
        $this->assertStringContainsString("<details", $content);
        $this->assertStringNotContainsString("removal-row-active", $content, "Marking a row is the script's job.");
        $this->assertStringNotContainsString("aria-current", substr($content, $start), "Marking a step is the script's job.");
    }

    public function testTheGuideLoadsItsScriptAsAModule(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertSee('<script type="module" src="' . Vite::asset("resources/js/removal.js") . '"></script>', false);
    }

    /** The script pairs a kind in step 2 with its row on the cards by this name. */
    public function testEveryRequestKindNamesTheRowsItMarks(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();

        foreach (RemovalController::KINDS as $kind) {
            $this->assertStringContainsString('class="removal-kind" id="kind-' . str_replace("_", "-", $kind) . '" data-removal-kind="' . $kind . '"', $content);
            $this->assertStringContainsString('<div data-removal-kind="' . $kind . '">', $content);
        }
    }

    /** One kind starts open, so the page shows that these open at all. */
    public function testOnlyTheFirstRequestKindStartsOpen(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<details[^>]*\bopen\b/', $content));
        $this->assertMatchesRegularExpression('/id="kind-personal-data"[^>]*\bopen\b/', $content);
    }

    public function testTheContentsCoverEveryStep(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();

        preg_match('/<nav class="removal-toc".*?<\/nav>/s', $content, $toc);
        $this->assertNotEmpty($toc);
        preg_match_all('/href="#([^"]+)"/', $toc[0], $targets);
        preg_match_all('/<li class="removal-step" id="([^"]+)"/', $content, $steps);

        $this->assertCount(3, $steps[1]);
        $this->assertSame($steps[1], $targets[1]);
    }

    /**
     * The page used to end at suma-crm's removal form. That form is switched
     * off; nothing on the page may lead to it, under any CRM address.
     */
    public function testThePageLinksNoFormOfOurs(): void
    {
        config(["metager.metager.crm.url" => "https://my.suma-ev.de"]);

        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();

        $this->assertStringNotContainsString("my.suma-ev.de", $content);
        $this->assertStringNotContainsString("/entfernung", $content);
        $this->assertStringNotContainsString('id="write-to-us"', $content);
        $this->assertStringNotContainsString("Zum Formular", $content);
    }

    /**
     * SUMA-EV's own searches get cards among the providers, not a step of
     * their own: TootNews' card (which MetaGer shows) and one for Nolm (which
     * it does not), whose removal goes through the Open Web Index's takedown
     * form and also takes the result off Nolm.
     */
    public function testNolmHasACardAmongTheProviders(): void
    {
        $this->assertSame("https://openwebindex.eu/takedown", config("removal_providers.own.nolm.removal"));

        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();
        $providers = substr($content, strpos($content, '<li class="removal-step" id="providers"'));
        $nolm = substr($providers, strpos($providers, 'id="nolm"'), 1200);

        $this->assertStringContainsString('id="nolm"', $providers);
        $this->assertStringContainsString('id="suma-ev"', $providers);
        $this->assertStringContainsString('href="https://openwebindex.eu/takedown"', $nolm);
        $this->assertStringContainsString("Open Web Index", $nolm);
        $this->assertStringContainsString("getrennt von MetaGer", $nolm);
        $this->assertStringNotContainsString('id="own-searches"', $content);
    }

    /** People search for this page; the generic MetaGer description says nothing about it. */
    public function testThePageHasItsOwnDescription(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertSee('<meta name="description" content="' . e(trans("removal.meta_description", [], "de")) . '"', false)
            ->assertDontSee('<meta name="description" content="' . e(trans("staticPages.meta.Description", [], "de")) . '"', false);
    }

    public function testOtherPagesKeepTheGenericDescription(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSee('<meta name="description" content="' . e(trans("staticPages.meta.Description", [], "de")) . '"', false);
    }

    /**
     * TootNews keeps no posts of its own: its card sends people to its start
     * page, which lists the Mastodon servers it searches, on the host the
     * parser searches for that language (TroetNews for German).
     */
    public function testTheTootNewsCardLeadsToTheServersItSearches(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();
        $card = substr($content, strpos($content, 'id="suma-ev"'), 900);

        $this->assertStringContainsString('href="https://troet.suma-lab.de/"', $card);
        $this->assertStringContainsString("Mastodon-Server", $card);
    }

    /** One request per test: a second one is routed against the first one's locale-stripped state and 404s. */
    public function testTheTootNewsCardLeadsToTheEnglishHostInEnglish(): void
    {
        $content = $this->get("/entfernen")->assertOk()->getContent();
        $card = substr($content, strpos($content, 'id="suma-ev"'), 900);

        $this->assertStringContainsString('href="https://toot.suma-lab.de/"', $card);
    }

    /** The page used to say we only hide a result where removal at the source fails. */
    public function testThePageDoesNotTurnRequestsAwayOverTheSource(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertDontSeeText("an der Quelle nicht möglich ist");
    }

    /**
     * The page used to promise an answer within a month, proof of identity
     * where it mattered, and when we delete it. Those described suma-crm's
     * removal cases, which are switched off; a promise for a process we do
     * not run is not one we can keep.
     */
    public function testThePageMakesNoPromisesForAProcessWeDoNotRun(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertDontSeeText("innerhalb eines Monats")
            ->assertDontSeeText("Kopie Ihres Ausweises")
            ->assertDontSeeText("Vollmacht");
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

    /**
     * Google is named without a contract with Google, so its name carries an
     * asterisk to a general trademark note at the bottom of the page (team
     * decision, 2026-11-11). The note claims no non-affiliation: we are a
     * customer of some of these services, and TootNews and Nolm are ours.
     */
    public function testGoogleCarriesAnAsteriskToTheTrademarkNote(): void
    {
        $content = $this->get("/de-DE/entfernen")->assertOk()->getContent();

        $this->assertStringContainsString(
            '<h3 id="google-name">Google<a class="removal-trademark-ref" href="#trademarks" aria-label="Markenhinweis">*</a></h3>',
            $content,
        );
        $this->assertSame(1, substr_count($content, 'class="removal-trademark-ref"'), "Only Google is marked.");
        $this->assertMatchesRegularExpression(
            '#<p class="removal-trademarks" id="trademarks"><span aria-hidden="true">\*</span> Die Namen der Suchmaschinen anderer Anbieter sind Marken ihrer jeweiligen Inhaber\.#',
            $content,
        );
        $this->assertStringNotContainsString("nicht verbunden", $content);
        $this->assertGreaterThan(strpos($content, 'id="providers"'), strpos($content, 'id="trademarks"'), "The note is at the bottom.");
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
     * on it, and the page must not look as if we would rather not know. With
     * no form, the address comes by email; the blocklist is how we act on it.
     */
    public function testIllegalContentAsksForTheAddressButNeverTheContent(): void
    {
        $this->get("/de-DE/entfernen")
            ->assertOk()
            ->assertDontSeeText("weder die Inhalte noch Links darauf")
            ->assertSeeText("nennen Sie uns nur seine Adresse (URL) als Text per E-Mail")
            ->assertSeeText("nie die Inhalte selbst");
    }
}
