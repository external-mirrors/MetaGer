<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\DuskTestCase;

/**
 * axe-core against the pages people actually use, in both palettes.
 *
 * Contrast is the reason this is a browser test: it is a property of the
 * resolved colours, and the palette is custom properties that only a rendering
 * engine turns back into colours (see ThemeColorsTest). The rest — names,
 * labels, landmarks, ARIA that points at nothing — would be readable from the
 * markup, but axe reads it from the same DOM the contrast check needs, so one
 * instrument covers both.
 *
 * What axe cannot judge is covered next to it, in KeyboardAccessTest: whether
 * focus is visible and whether the page can be operated without a pointer.
 *
 * axe-core comes from node_modules. CI's browsertest job gets no node_modules,
 * which is why the npm job publishes axe.min.js as an artifact along with
 * public/ (.gitlab/ci/build_dependencies.yml).
 */
class AccessibilityTest extends DuskTestCase
{
    use FakesSearchEngines;

    /**
     * WCAG 2.2 A and AA. best-practice is left out on purpose: it is advice, not
     * conformance, and a rule that merely recommends something should not fail
     * a pipeline.
     */
    private const TAGS = ["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"];

    private const KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        // Every search here queues a discharge for test-key that nothing settles,
        // and the queue is shared by the whole suite: left behind, it is the first
        // charge the next test to settle sees (AnonymousTokenKeyDischargeTest).
        $this->forgetQueuedDischarges();
        parent::tearDown();
    }

    public static function themes(): array
    {
        return [
            "light" => ["light"],
            "dark" => ["dark"],
        ];
    }

    #[DataProvider("themes")]
    public function testTheAnonymousStartpage(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE?dark_mode={$theme}&zitate=off")
                ->assertPresent("#searchbar-replacement");

            $this->assertNoViolations($browser, "startpage-anonymous-{$theme}");
        });
    }

    #[DataProvider("themes")]
    public function testTheStartpageWithAKey(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE")
                // false: `key` steht in EncryptCookies::$except.
                ->addCookie("key", self::KEY, null, [], false)
                ->visit("/de-DE?dark_mode={$theme}&zitate=off")
                ->assertPresent("#eingabe");

            $this->assertNoViolations($browser, "startpage-key-{$theme}");
        });
    }

    #[DataProvider("themes")]
    public function testTheAccountPage(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE")
                ->addCookie("key", self::KEY, null, [], false)
                ->visit("/de-DE/konto?dark_mode={$theme}")
                ->assertPresent(".account-save__qr img");

            $this->assertNoViolations($browser, "account-{$theme}");
        });
    }

    /**
     * The result page cannot be visited: CI points the browser at a deployed
     * review pod, and no engine behind it answers with anything a test could
     * rely on. So it is rendered here, in-process, from the engine fixtures
     * every other result page test uses, and written into a document of the
     * app's own origin — its stylesheets are root-relative and resolve against
     * the same build.
     */
    #[DataProvider("themes")]
    public function testTheResultPage(string $theme): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $html = $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web&dark_mode={$theme}")
            ->assertOk()
            ->getContent();

        $this->browse(function (Browser $browser) use ($theme, $html) {
            $browser->visit("/de-DE/impressum");
            $browser->driver->executeScript("document.open(); document.write(arguments[0]); document.close();", [$html]);
            $browser->waitUntil("document.readyState === 'complete'")
                ->assertPresent("#results");

            $this->assertNoViolations($browser, "resultpage-{$theme}");
        });
    }

    private function assertNoViolations(Browser $browser, string $label): void
    {
        $browser->script(file_get_contents(base_path("node_modules/axe-core/axe.min.js")));

        $violations = $browser->driver->executeAsyncScript(
            "const done = arguments[arguments.length - 1];"
                . "axe.run(document, {runOnly: {type: 'tag', values: arguments[0]}})"
                . ".then(r => done(r.violations.map(v => ({id: v.id, impact: v.impact, help: v.help,"
                . " nodes: v.nodes.map(n => ({target: n.target.join(' '), summary: n.failureSummary}))}))))"
                . ".catch(e => done([{id: 'axe-error', impact: 'critical', help: String(e), nodes: []}]));",
            [self::TAGS]
        );

        $report = collect($violations)->map(function (array $violation) {
            $nodes = collect($violation["nodes"])
                ->map(fn(array $node) => "    {$node["target"]}\n      " . str_replace("\n", "\n      ", $node["summary"]))
                ->implode("\n");

            return "[{$violation["impact"]}] {$violation["id"]}: {$violation["help"]}\n{$nodes}";
        })->implode("\n\n");

        // assertTrue and not assertSame([]): the array diff repeats the report
        // below it, three times as long and harder to read.
        $this->assertTrue($violations === [], "axe found violations on {$label}:\n\n{$report}");
    }
}
