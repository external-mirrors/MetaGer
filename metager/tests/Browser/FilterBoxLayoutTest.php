<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\DuskTestCase;

/**
 * The open filter box on a phone: every dropdown on screen, none behind a
 * sideways scroll.
 *
 * The dropdowns used to sit in one row that scrolled sideways, and on a phone
 * the third one ("Sprache") ran off the right edge with nothing to say the row
 * went on. They wrap now, so the box grows downwards instead, and it has to be
 * allowed to: it unfolds by animating max-height, and content past that cap
 * would spill over the first result.
 */
class FilterBoxLayoutTest extends DuskTestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        // See KeyboardAccessTest::tearDown.
        $this->forgetQueuedDischarges();
        parent::tearDown();
    }

    public static function foci(): array
    {
        return [
            "web" => ["web", "brave", "brave-web.json", false],
            // "Datum ⚙" swaps the date dropdown for two date fields.
            "web, own date range" => ["web", "brave", "brave-web.json", true],
            // The most dropdowns of any fokus.
            "images" => ["bilder", "brave_images", "brave-images.json", false],
        ];
    }

    #[DataProvider("foci")]
    public function testEveryDropdownFitsOnAPhone(string $focus, string $engine, string $fixture, bool $ownDates): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([$engine => $this->engineFixture($fixture)]);
        $html = $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus={$focus}")
            ->assertOk()
            ->getContent();

        $this->browse(function (Browser $browser) use ($html, $ownDates) {
            // Firefox will not make its window narrower than 500px, so the page
            // goes into a frame of a phone's width. The frame is a viewport of
            // its own: media queries and layout see 320px.
            $browser->visit("/metager_maps/version.txt");
            $browser->driver->executeScript(
                "const frame = document.createElement('iframe');"
                    . "frame.id = 'phone'; frame.style.cssText = 'width: 320px; height: 800px; border: 0';"
                    . "document.body.replaceChildren(frame);"
                    . "frame.contentDocument.open(); frame.contentDocument.write(arguments[0]); frame.contentDocument.close();",
                [$html]
            );
            $browser->waitUntil("document.querySelector('#phone').contentDocument.readyState === 'complete'"
                . " && document.querySelector('#phone').contentDocument.querySelector('#options-toggle') !== null");

            $browser->script("document.querySelector('#phone').contentDocument.querySelector('#options-toggle').checked = true;");
            if ($ownDates) {
                $browser->script("document.querySelector('#phone').contentDocument.querySelector('label[for=custom-date]').click();");
            }
            // The box unfolds over half a second.
            $browser->pause(700);

            $layout = $browser->script(
                "const page = document.querySelector('#phone').contentDocument;"
                    . "const box = page.querySelector('#options-box');"
                    . "const fold = page.querySelector('#options .scrollbox');"
                    . "const width = page.documentElement.clientWidth;"
                    . "return {"
                    . " width,"
                    . " dates: [...page.querySelectorAll('#options-items input[type=date]')].filter((d) => d.offsetParent !== null).length,"
                    . " selects: page.querySelectorAll('#options-items select').length,"
                    . " sideways: box.scrollWidth - box.clientWidth,"
                    . " spill: fold.scrollHeight - fold.clientHeight,"
                    . " offscreen: [...page.querySelectorAll('#options-items select, #options-items input[type=date]')]"
                    . "   .filter((s) => s.offsetParent !== null)"
                    . "   .filter((s) => { const r = s.getBoundingClientRect(); return r.left < 0 || r.right > width; })"
                    . "   .map((s) => s.name),"
                    . "};"
            )[0];

            $this->assertLessThanOrEqual(320, $layout["width"], "The page is not laid out at a phone's width.");
            if ($ownDates) {
                $this->assertSame(2, $layout["dates"], "The own date range is not open.");
            }
            $this->assertGreaterThanOrEqual(2, $layout["selects"], "Too few filters to say anything about wrapping.");
            $this->assertSame([], $layout["offscreen"], "These dropdowns run off a 320px screen.");
            $this->assertLessThanOrEqual(1, $layout["sideways"], "The filter box scrolls sideways.");
            $this->assertLessThanOrEqual(1, $layout["spill"], "The filters are taller than the box unfolds.");
        });
    }
}
