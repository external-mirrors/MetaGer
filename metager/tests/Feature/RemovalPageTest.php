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

    #[DataProvider("fokiEngines")]
    public function testEveryEngineInAFokusHasAProvider(string $engine): void
    {
        $providerId = config("removal_providers.engines.$engine");

        $this->assertNotNull($providerId, "config/removal_providers.php has no entry for engine [$engine] from config/foki.json.");
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
                if (!($registry->sumas->{$engine}->disabled ?? false)) {
                    $id = config("removal_providers.engines.$engine");
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
     * Yandex, One News Page and One News Page Germany are switched off in
     * their parsers: MetaGer has no access to Yandex any more, and both One
     * News Page feeds died in August 2026. Sending someone to a provider whose
     * results we do not show is a wasted errand, so their cards are gone.
     * The engine => provider mapping keeps them, so a provider comes back on
     * its own when its engine is switched on again.
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

        // Today's case, spelled out, so the generic loop above cannot pass by
        // having nothing to check.
        $this->assertArrayNotHasKey("yandex", $inUse);
        $this->assertArrayNotHasKey("onenewspage", $inUse);
        $this->assertStringNotContainsString("Yandex", $content);
        $this->assertStringNotContainsString("One News Page", $content);
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
}
