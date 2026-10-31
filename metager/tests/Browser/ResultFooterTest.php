<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Concerns\FakesSearchEngines;
use Tests\DuskTestCase;

/**
 * The row under a result — Anonym, In neuem Tab, ⋯ and who found it.
 *
 * What paints on top is a question only a layout engine answers.
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
