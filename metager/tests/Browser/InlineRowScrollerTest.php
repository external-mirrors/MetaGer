<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Concerns\FakesSearchEngines;
use Tests\DuskTestCase;

/**
 * ‹ › on the news row, in a browser that lays it out.
 *
 * resources/js/resultpage/scroller.test.js covers the decisions with the widths
 * stood in for; this is what jsdom cannot say: that a click really moves the
 * row, that it stops on a card's edge (scroll-snap), and that the buttons show
 * where Firefox's pointer is a mouse.
 *
 * brave-web.json carries one news card, so the row is filled up in the page to
 * overflow before the script looks at it.
 */
class InlineRowScrollerTest extends DuskTestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        // See KeyboardAccessTest::tearDown.
        $this->forgetQueuedDischarges();
        parent::tearDown();
    }

    public function testForwardMovesTheRowOnToACardsEdge(): void
    {
        $html = $this->renderResultPage();

        $this->browse(function (Browser $browser) use ($html) {
            $this->openRenderedPage($browser->resize(1280, 900), $html);
            $this->fillRow($browser);

            $this->assertSame(["back" => false, "forward" => true], $this->visibleButtons($browser));

            $browser->script("document.querySelector('#news').closest('.inline-scroller').querySelector('.forward').click();");
            $browser->waitUntil("document.querySelector('#news').scrollLeft > 0");
            // The smooth scroll and the snap after it.
            $browser->pause(800);

            $state = $browser->script(
                "const row = document.querySelector('#news');"
                    . "const left = row.getBoundingClientRect().left;"
                    . "const edges = [...row.children].map((card) => Math.round(card.getBoundingClientRect().left - left));"
                    . "return {scrolled: row.scrollLeft, edges};"
            )[0];

            $this->assertGreaterThan(240, $state["scrolled"], "One click should move more than a card.");
            $this->assertContains(0, $state["edges"], "The row stopped part-way through a card.");
            $this->assertSame(["back" => true, "forward" => true], $this->visibleButtons($browser));
        });
    }

    public function testTheButtonsSwapAtTheEndOfTheRow(): void
    {
        $html = $this->renderResultPage();

        $this->browse(function (Browser $browser) use ($html) {
            $this->openRenderedPage($browser->resize(1280, 900), $html);
            $this->fillRow($browser);

            $browser->script("const row = document.querySelector('#news'); row.scrollLeft = row.scrollWidth;");
            $browser->pause(300);

            $this->assertSame(["back" => true, "forward" => false], $this->visibleButtons($browser));
        });
    }

    /** Eight cards: more than a desktop row shows. Then the script looks again, as after load-more. */
    private function fillRow(Browser $browser): void
    {
        $browser->script(
            "const frame = document.querySelector('#news').closest('.inline-scroller');"
                . "const fresh = frame.cloneNode(true);"
                . "const row = fresh.querySelector('#news');"
                . "const card = row.querySelector('.inline-result');"
                . "for (let i = 0; i < 7; i++) row.append(card.cloneNode(true));"
                . "fresh.querySelectorAll('.inline-scroll-button').forEach((b) => b.remove());"
                . "frame.replaceWith(fresh);"
                . "document.dispatchEvent(new Event('resultsChanged'));"
        );
        $browser->waitUntil("document.querySelectorAll('#news ~ .inline-scroll-button').length === 2");
    }

    /** @return array{back: bool, forward: bool} whether each button is on screen */
    private function visibleButtons(Browser $browser): array
    {
        return $browser->script(
            "const frame = document.querySelector('#news').closest('.inline-scroller');"
                . "const shown = (b) => getComputedStyle(frame.querySelector(b)).display !== 'none';"
                . "return {back: shown('.back'), forward: shown('.forward')};"
        )[0];
    }

    private function renderResultPage(): string
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
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
            ->assertPresent("#news");
    }
}
