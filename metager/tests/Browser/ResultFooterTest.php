<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\DuskTestCase;

/**
 * The row under a result — Anonym, In neuem Tab, ⋯ and who found it — at the
 * widths between a phone and a desktop.
 *
 * All three of these went wrong at once, between 425px and about 540px: the
 * labels only shortened below a fixed screen width, so "von 2 Anbietern" wrapped
 * onto a line of its own first; the ⋯ menu dropped down from ⋯ at 240px wide and
 * ran off the right of the page; and it sat above the sticky research bar, so it
 * scrolled over it. Whether text fits, where a box ends and what paints on top
 * are questions only a layout engine answers.
 *
 * The result is found by two engines, because "von 2 Anbietern" is the longest
 * the engines' part of the row gets.
 */
class ResultFooterTest extends DuskTestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        // See KeyboardAccessTest::tearDown.
        $this->forgetQueuedDischarges();
        parent::tearDown();
    }

    /**
     * Window widths. Firefox will not make its viewport narrower than 500px.
     */
    public static function widths(): array
    {
        $widths = [];
        foreach ([500, 520, 560, 600, 640, 700, 800, 1280] as $width) {
            $widths["{$width}px"] = [$width];
        }

        return $widths;
    }

    /**
     * Widths of the row itself, from a 320px phone's up to a desktop's. The
     * row decides by its own width, so the card is narrowed to get there —
     * which also reaches the rows narrower than a 500px window has.
     */
    public static function rowWidths(): array
    {
        $widths = [];
        foreach ([256, 300, 340, 380, 390, 420, 460, 480, 500, 560, 640, 740] as $width) {
            $widths["{$width}px"] = [$width];
        }

        return $widths;
    }

    #[DataProvider("rowWidths")]
    public function testWhoFoundItStaysInTheRow(int $width): void
    {
        $html = $this->renderResultPage();

        $this->browse(function (Browser $browser) use ($html, $width) {
            $this->openRenderedPage($browser->resize(1280, 800), $html);

            $row = $browser->script(
                "const footer = document.querySelector('.result .result-footer');"
                    . "const card = footer.closest('.result');"
                    . "card.style.width = (card.getBoundingClientRect().width - footer.clientWidth + {$width}) + 'px';"
                    // Middles, not tops: the actions are 28px tall, the engines' 12px text is centred on them.
                    . "const middle = (selector) => { const box = footer.querySelector(selector).getBoundingClientRect();"
                    . " return Math.round((box.top + box.bottom) / 2); };"
                    . "const hoster = footer.querySelector('.result-hoster');"
                    . "return {action: middle('.result-open-proxy'), hoster: middle('.result-hoster'),"
                    . " text: hoster.firstChild.textContent.trim(), footer: footer.clientWidth};"
            )[0];

            $this->assertStringContainsString(
                "2",
                $row["text"],
                "The result is not credited to two engines ({$row["text"]}), so the row is not "
                    . "at its widest and this measures nothing."
            );
            $this->assertSame($width, $row["footer"], "The row is not {$width}px wide.");
            $this->assertSame(
                $row["action"],
                $row["hoster"],
                "In a {$width}px row „{$row["text"]}“ wrapped onto a line of its own. The labels "
                    . "should have shortened first: the @container query on .result-footer in "
                    . "resources/less/metager/pages/resultpage/result.less."
            );
        });
    }

    #[DataProvider("widths")]
    public function testTheOpenMenuStaysOnThePage(int $width): void
    {
        $html = $this->renderResultPage();

        $this->browse(function (Browser $browser) use ($html, $width) {
            $this->openRenderedPage($browser->resize($width, 800), $html);

            $page = $browser->script(
                "document.querySelector('.result .result-toggle').checked = true;"
                    . "const menu = document.querySelector('.result .result-options ul').getBoundingClientRect();"
                    . "return {right: Math.round(menu.right), viewport: document.documentElement.clientWidth,"
                    . " scroll: document.documentElement.scrollWidth};"
            )[0];

            $this->assertLessThanOrEqual(
                $page["viewport"],
                $page["right"],
                "At {$width}px the open ⋯ menu ends at {$page["right"]}px, past the page's "
                    . "{$page["viewport"]}px. Where the row is too narrow to drop it down beside ⋯, "
                    . "it spans the row (result.less)."
            );
            $this->assertSame(
                $page["viewport"],
                $page["scroll"],
                "At {$width}px the open ⋯ menu makes the page scroll sideways."
            );
        });
    }

    public function testTheResearchBarStaysAboveAnOpenMenu(): void
    {
        $html = $this->renderResultPage();

        $this->browse(function (Browser $browser) use ($html) {
            $this->openRenderedPage($browser->resize(800, 700), $html);

            $hit = $browser->script(
                // Room to scroll the menu up under the bar.
                "document.body.style.minHeight = '3000px';"
                    . "document.querySelector('.result .result-toggle').checked = true;"
                    . "const menu = document.querySelector('.result .result-options ul');"
                    . "const bar = document.querySelector('#research-bar-container');"
                    . "window.scrollBy(0, menu.getBoundingClientRect().top - bar.getBoundingClientRect().bottom + 20);"
                    . "const box = menu.getBoundingClientRect();"
                    . "if (box.top >= bar.getBoundingClientRect().bottom) { return 'not under the bar'; }"
                    . "const hit = document.elementFromPoint(box.left + 20, box.top + 5);"
                    . "return hit.closest('#research-bar-container') ? 'bar' : hit.className;"
            )[0];

            $this->assertSame(
                "bar",
                $hit,
                "An open ⋯ menu scrolled up under the sticky research bar is drawn over it. "
                    . "Its z-index has to stay below #research-bar-container's."
            );
        });
    }

    private function renderResultPage(): string
    {
        $result = [
            "title" => "Kaffee & Zubereitung",
            "link" => "https://example.org/kaffee",
            "snippet" => "Alles über Kaffee, Röstung und Zubereitung.",
        ];

        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => json_encode(["organic" => [$result]]),
        ]);

        return $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web")
            ->assertOk()
            ->getContent();
    }

    /**
     * See KeyboardAccessTest::openRenderedPage.
     */
    private function openRenderedPage(Browser $browser, string $html): void
    {
        $browser->visit("/metager_maps/version.txt");
        $browser->driver->executeScript("document.open(); document.write(arguments[0]); document.close();", [$html]);
        $browser->waitUntil("document.readyState === 'complete'")
            ->assertPresent("#results");
    }
}
