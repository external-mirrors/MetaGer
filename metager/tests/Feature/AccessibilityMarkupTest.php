<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * The parts of the accessibility pass that are facts about markup.
 *
 * tests/Browser/AccessibilityTest and KeyboardAccessTest are the real checks —
 * axe against the rendered pages, and Tab pressed through them — but they run
 * only in browsertest, after a deploy. Everything here is readable from the
 * HTML, so it is pinned where every push runs it, and a failure names the
 * template rather than an axe rule id.
 */
class AccessibilityMarkupTest extends TestCase
{
    use FakesSearchEngines;

    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        // Every search here queues a discharge for test-key that nothing settles,
        // and the queue is shared by the whole suite: left behind, it is the first
        // charge the next test to settle sees (AnonymousTokenKeyDischargeTest).
        $this->forgetQueuedDischarges();
        parent::tearDown();
    }

    private function resultPage(): string
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        return $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web")
            ->assertOk()
            ->getContent();
    }

    private function accountPage(): string
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response(["per_token" => 0.01, "vat" => 7, "purchasable" => [500]]),
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => 248,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => 248, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]);

        return $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/konto")
            ->assertOk()
            ->getContent();
    }

    /**
     * One request per test: in this class a second request in the same test
     * answers 404, whatever the first one was.
     */
    private function page(string $page): string
    {
        return match ($page) {
            "startpage" => $this->get("/de-DE")->assertOk()->getContent(),
            "result page" => $this->resultPage(),
            "account page" => $this->accountPage(),
        };
    }

    public static function pages(): array
    {
        return [
            "startpage" => ["startpage"],
            "result page" => ["result page"],
            "account page" => ["account page"],
        ];
    }

    private function viewport(string $html): string
    {
        $this->assertMatchesRegularExpression('/<meta[^>]*name="viewport"[^>]*>/', $html);
        preg_match('/<meta[^>]*name="viewport"[^>]*>/', $html, $match);

        return $match[0];
    }

    /**
     * WCAG 1.4.4: pinch zoom has to work. Both layouts used to switch it off,
     * one with maximum-scale=1 and the other with user-scalable=no.
     */
    #[DataProvider("pages")]
    public function testNoLayoutLocksTheZoom(string $page): void
    {
        $viewport = $this->viewport($this->page($page));

        $this->assertStringNotContainsString("maximum-scale", $viewport, "The {$page} caps the zoom: {$viewport}");
        $this->assertStringNotContainsString("user-scalable", $viewport, "The {$page} disables zooming: {$viewport}");
    }

    /**
     * The region code is what is on screen, so it has to be in the name (WCAG
     * 2.5.3 — a voice user says "click DE"); what the link does follows it as
     * hidden text. It used to be an aria-label that replaced the code, and the
     * label read "Hexensprache" — the English source said "wwitch language", and
     * every translation followed it.
     */
    public function testTheLanguageLinkSaysWhatItIsAndWhatItDoes(): void
    {
        $html = $this->get("/de-DE")->getContent();

        $this->assertMatchesRegularExpression(
            '#<a class="lang" href="[^"]*">\s*<span>DE</span><span class="visually-hidden">Sprache wechseln</span>#',
            $html
        );
        $this->assertStringNotContainsString("Hexensprache", $html);

        foreach (glob(lang_path("*/index.php")) as $file) {
            $this->assertStringNotContainsStringIgnoringCase(
                "wwitch",
                (string) (require $file)["lang"],
                "{$file}: index.lang still translates the typo in its English source."
            );
        }
    }

    public function testTheSearchFieldAndItsClearButtonHaveNames(): void
    {
        $html = $this->resultPage();

        $this->assertMatchesRegularExpression('/<input type="search" id="eingabe"[^>]*aria-label="Suchanfrage"/', $html);
        // Its content is "×", which a screen reader reads as "multiplication sign".
        $this->assertMatchesRegularExpression('/<button id="search-delete-btn"[^>]*aria-label="Eingabe der Suchanfrage löschen"/', $html);
        // The suggestion slots are options reached with the arrow keys
        // (resources/js/suggest/combobox.js), not tab stops of their own.
        $this->assertStringNotContainsString('<div class="suggestion" tabindex="0">', $html);
    }

    /**
     * #results holds the news row, the alerts and the pager between and after
     * the results, so it cannot be a list; it is a labelled region, and each
     * result is found by its heading.
     */
    public function testTheResultsAreARegionAndNotABrokenList(): void
    {
        $html = $this->resultPage();

        $this->assertMatchesRegularExpression('/<div id="results" role="region"\s+aria-label="[^"]+"/', $html);
        $this->assertStringNotContainsString('role="listitem"', $html);
        $this->assertStringNotContainsString('aria-label="..."', $html, "The pager's navigation is labelled \"...\".");
    }

    /**
     * One result, one set of tab stops that each go somewhere. The description
     * was a stop that did nothing, and the logo and image links were hidden from
     * screen readers but not from the keyboard — focus on nothing nameable.
     */
    public function testAResultHasNoDeadOrNamelessTabStops(): void
    {
        $html = $this->resultPage();

        $this->assertStringContainsString('<div class="result-description">', $html);
        $this->assertStringNotContainsString('class="result-description" tabindex', $html);

        preg_match_all('/<div class="result-(?:logo|image)" aria-hidden="true">\s*<a [^>]*>/', $html, $hidden);
        foreach ($hidden[0] as $link) {
            $this->assertStringContainsString('tabindex="-1"', $link, "A link inside aria-hidden can still take focus: {$link}");
        }

        preg_match_all('/<a class="result-image"[^>]*>/', $html, $inline);
        foreach ($inline[0] as $link) {
            $this->assertStringContainsString('aria-hidden="true"', $link);
            $this->assertStringContainsString('tabindex="-1"', $link);
        }
    }

    /**
     * A focused <label> ignores Enter and Space. The filter checkbox has to be
     * the tab stop itself; KeyboardAccessTest::testTheFiltersOpenFromTheKeyboard
     * presses it.
     */
    public function testTheFilterToggleIsNotALabelPretendingToBeAButton(): void
    {
        $html = $this->resultPage();

        $this->assertStringContainsString('<label class="navigation-element" for="options-toggle">', $html);
        $this->assertDoesNotMatchRegularExpression('/<label[^>]*for="options-toggle"[^>]*tabindex/', $html);
    }

    public function testTheMenuIsANavigationLandmarkWithRealLists(): void
    {
        $html = $this->get("/de-DE")->getContent();

        $this->assertMatchesRegularExpression('/<nav class="sidebar" aria-label="Hauptmenü">/', $html);
        $this->assertStringNotContainsString('<ul class="sidebar-list" role="presentation">', $html);
        $this->assertDoesNotMatchRegularExpression('#</li>\s*<hr>#', $html, "An <hr> directly inside the menu's list.");
    }

    /**
     * Source order is tab order. The skip links come first, then the menu and
     * the account — the top-right corner — and only then the page. They used to
     * follow the footer.
     */
    #[DataProvider("pages")]
    public function testTheSkipLinksAndTheMenuComeBeforeThePage(string $page): void
    {
        $html = $this->page($page);

        $skip = strpos($html, 'class="skiplinks"');
        $toggle = strpos($html, 'id="sidebarToggle"');
        $main = strpos($html, $page === "result page" ? 'id="research-bar-container"' : 'id="main-content"');

        $this->assertNotFalse($skip, "The {$page} has no skip links.");
        $this->assertNotFalse($toggle);
        $this->assertNotFalse($main);
        $this->assertLessThan($toggle, $skip, "On the {$page} the menu comes before the skip links.");
        $this->assertLessThan($main, $toggle, "On the {$page} the menu comes after the page content.");
    }

    /**
     * Every page without skip links of its own gets the two any page can
     * honour, and no promise about Escape — only the startpage and the result
     * page load the script that keeps it.
     */
    public function testASubpageCanBeSkippedToItsContent(): void
    {
        $html = $this->accountPage();

        $this->assertMatchesRegularExpression('#<div class="skiplinks">.*?<a href="\#main-content"#s', $html);
        $this->assertStringNotContainsString('<div class="escape">', $html);
    }
}
