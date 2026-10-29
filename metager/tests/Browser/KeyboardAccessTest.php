<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\DuskTestCase;

/**
 * The pages, operated with a keyboard and nothing else.
 *
 * AccessibilityTest asks axe, and axe cannot answer the two questions that
 * decide whether a keyboard user gets anywhere: where focus is, and whether you
 * can see it. Both are answered here by pressing Tab — a real key event, so the
 * browser applies :focus-visible exactly as it would for a person — and looking
 * at each element focus lands on.
 *
 * A tab stop passes when it is on screen and something about it changes when it
 * gains focus: outline, shadow, background, border or underline. That is a
 * deliberately low bar — it does not judge whether the change is *enough* — but
 * it is the bar `outline: none` fails, and `outline: none` is how focus went
 * missing on this site.
 */
class KeyboardAccessTest extends DuskTestCase
{
    use FakesSearchEngines;

    private const KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    /**
     * More than any of these pages has stops. Tabbing stops early once focus
     * comes back round to where it started.
     */
    private const MAX_STOPS = 200;

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
    public function testEveryStopOnTheAnonymousStartpageIsVisible(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE?dark_mode={$theme}&zitate=off")
                ->assertPresent("#searchbar-replacement");

            $this->assertEveryStopIsVisible($browser, "startpage-anonymous-{$theme}");
        });
    }

    #[DataProvider("themes")]
    public function testEveryStopOnTheStartpageWithAKeyIsVisible(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE")
                // false: `key` steht in EncryptCookies::$except.
                ->addCookie("key", self::KEY, null, [], false)
                ->visit("/de-DE?dark_mode={$theme}&zitate=off")
                ->assertPresent("#eingabe");

            $this->assertEveryStopIsVisible($browser, "startpage-key-{$theme}");
        });
    }

    #[DataProvider("themes")]
    public function testEveryStopOnTheAccountPageIsVisible(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE")
                ->addCookie("key", self::KEY, null, [], false)
                ->visit("/de-DE/konto?dark_mode={$theme}")
                ->assertPresent(".account-save__qr img");

            $this->assertEveryStopIsVisible($browser, "account-{$theme}");
        });
    }

    /**
     * Rendered in-process and written into a same-origin document, for the
     * reason AccessibilityTest::testTheResultPage gives.
     */
    #[DataProvider("themes")]
    public function testEveryStopOnTheResultPageIsVisible(string $theme): void
    {
        $html = $this->renderResultPage($theme);

        $this->browse(function (Browser $browser) use ($html) {
            $this->openRenderedPage($browser, $html);

            $this->assertEveryStopIsVisible($browser, "resultpage");
        });
    }

    /**
     * The menu, opened the way a keyboard opens it: Tab to the toggle, Space.
     *
     * It needs its own walk because it is closed for every other one — closed,
     * its links are visibility: hidden and not tab stops at all. Its links set
     * `outline: none` until the accessibility pass and showed focus only by
     * turning red, so this is where a ring is most likely to be taken away
     * again.
     */
    #[DataProvider("themes")]
    public function testEveryStopInTheOpenMenuIsVisible(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE?dark_mode={$theme}&zitate=off")
                ->assertPresent("#sidebarToggle");

            $this->tabTo($browser, "sidebarToggle");
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::SPACE);
            $browser->waitUntil("getComputedStyle(document.querySelector('.sidebar')).visibility === 'visible'");

            $this->assertEveryStopIsVisible($browser, "the open menu ({$theme})", ".sidebar");
        });
    }

    /**
     * The result page's filters, opened from the keyboard.
     *
     * Their checkbox was display: none and their label carried tabindex="0" in
     * its place, which looks keyboard-accessible and is not: focus lands on the
     * label, and a label ignores Enter and Space. The walk above cannot see that
     * — the stop was visible and had a focus ring — so it is asserted by using it.
     */
    public function testTheFiltersOpenFromTheKeyboard(): void
    {
        $html = $this->renderResultPage("light");

        $this->browse(function (Browser $browser) use ($html) {
            $this->openRenderedPage($browser, $html);

            $this->tabTo($browser, "options-toggle");
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::SPACE);

            $browser->waitUntil("getComputedStyle(document.querySelector('#options .scrollbox')).display !== 'none'");
            $this->assertTrue(
                $browser->driver->executeScript("return document.querySelector('#options-toggle').checked;"),
                "Space on the focused filter toggle did not open the filters."
            );
        });
    }

    /**
     * Press Tab until the element with this id has focus.
     */
    private function tabTo(Browser $browser, string $id): void
    {
        $browser->driver->executeScript("document.activeElement && document.activeElement.blur(); window.scrollTo(0, 0);");
        for ($i = 0; $i < self::MAX_STOPS; $i++) {
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::TAB);
            if ($browser->driver->executeScript("return document.activeElement && document.activeElement.id;") === $id) {
                return;
            }
        }
        $this->fail("Tab never reached #{$id}.");
    }

    /**
     * Walk the tab order and report every stop that is off screen or shows no
     * sign of having focus.
     */
    private function assertEveryStopIsVisible(Browser $browser, string $label, ?string $within = null): void
    {
        $stops = $this->walkTabOrder($browser, $within);
        if ($within !== null) {
            $stops = array_values(array_filter($stops, fn(array $stop) => $stop["inside"]));
        }

        $this->assertNotEmpty($stops, "Tab never focused anything on {$label}.");

        $problems = collect($stops)
            ->filter(fn(array $stop) => !$stop["visible"] || !$stop["indicated"])
            ->map(fn(array $stop) => sprintf(
                "  #%d %s%s%s",
                $stop["index"],
                $stop["element"],
                $stop["visible"] ? "" : " — not on screen ({$stop["why"]})",
                $stop["indicated"] ? "" : " — nothing changes when it gains focus",
            ));

        $this->assertTrue(
            $problems->isEmpty(),
            "Keyboard focus is lost at " . $problems->count() . " of " . count($stops)
                . " tab stops on {$label}:\n" . $problems->implode("\n")
        );
    }

    /**
     * @return list<array{index: int, element: string, visible: bool, why: string, indicated: bool, inside: bool}>
     */
    private function walkTabOrder(Browser $browser, ?string $within = null): array
    {
        // Start from the top of the document, not from wherever autofocus put us.
        $browser->driver->executeScript(
            "document.activeElement && document.activeElement.blur(); window.scrollTo(0, 0);"
                . "window.__stops = []; window.__previous = null; window.__first = null;"
                . "window.__within = arguments[0];"
                // Transitions off, so every stop is read in its final state: the
                // skip links slide in over 0.25s, and a stop measured mid-slide is
                // "outside the viewport". Waiting them out cost 350ms a key press,
                // three minutes over the suite; what they end on is what counts.
                // A constructed sheet and not a <style>: the CSP says
                // style-src 'self', which blocks inline styles but not the CSSOM.
                . "const sheet = new CSSStyleSheet();"
                . "sheet.replaceSync('*, *::before, *::after { transition: none !important; animation: none !important; }');"
                . "document.adoptedStyleSheets = [...document.adoptedStyleSheets, sheet];",
            [$within]
        );

        $describe = <<<'JS'
            const el = document.activeElement;
            const describe = (e) => {
                let s = e.tagName.toLowerCase();
                if (e.id) s += '#' + e.id;
                else if (e.classList.length) s += '.' + [...e.classList].slice(0, 3).join('.');
                const name = (e.getAttribute('aria-label') || e.innerText || e.value || e.getAttribute('title') || '')
                    .trim().replace(/\s+/g, ' ').slice(0, 50);
                return name ? s + ' "' + name + '"' : s;
            };
            // What can show that a control has focus. Its own box, and the boxes
            // of its nearest ancestors — the search field is borderless inside its
            // bar, and the bar draws the ring. For a control hidden from sight (the
            // menu and filter checkboxes) its own box shows nothing whatever it
            // computes to; what a keyboard user sees is its labels, including the
            // ones it reveals on focus, so those are what is looked at instead.
            const hiddenFromSight = (e) => {
                const r = e.getBoundingClientRect();
                return r.width <= 1 || r.height <= 1 || parseFloat(getComputedStyle(e).opacity) === 0;
            };
            const surfaces = (e) => {
                if (hiddenFromSight(e)) {
                    const labelledBy = (e.getAttribute('aria-labelledby') || '').split(/\s+/)
                        .map(id => id && document.getElementById(id)).filter(Boolean);
                    return [...(e.labels || []), ...labelledBy];
                }
                const list = [e];
                for (let p = e.parentElement; p && list.length < 4; p = p.parentElement) list.push(p);
                return list;
            };
            const look = (elements) => elements.map(e => {
                const cs = getComputedStyle(e);
                const r = e.getBoundingClientRect();
                return [cs.outlineStyle === 'none' || parseFloat(cs.outlineWidth) === 0 ? 'none' : cs.outlineStyle + cs.outlineWidth + cs.outlineColor,
                    cs.boxShadow, cs.backgroundColor, cs.borderColor, cs.textDecorationLine, cs.color,
                    // Revealed on focus: the menu's floating label slides in, a result's
                    // options label appears. Position only for fixed boxes — the
                    // walk scrolls, and everything else moves with it.
                    cs.display, cs.visibility, cs.position === 'fixed' ? Math.round(r.top) : ''].join('|');
            }).join('#');
            // The previous stop, now that focus has left it: if it looked the same
            // with and without focus, nothing told a keyboard user where they were.
            if (window.__previous) {
                const p = window.__previous;
                window.__stops[window.__stops.length - 1].indicated = p.surfaces.length > 0 && look(p.surfaces) !== p.look;
            }
            if (!el || el === document.body || el === document.documentElement) {
                window.__previous = null;
                return null;
            }
            // Round again: the walk is over.
            if (window.__first === el) {
                return 'wrapped';
            }
            window.__first = window.__first || el;
            const r = el.getBoundingClientRect();
            const cs = getComputedStyle(el);
            let why = '';
            if (r.width < 1 || r.height < 1) why = 'zero size';
            else if (cs.visibility === 'hidden') why = 'visibility hidden';
            else if (parseFloat(cs.opacity) === 0) why = 'transparent';
            else if (r.bottom <= 0 || r.right <= 0 || r.top >= innerHeight || r.left >= innerWidth) why = 'outside the viewport';
            else {
                const hit = document.elementFromPoint(Math.min(Math.max(r.left + r.width / 2, 0), innerWidth - 1),
                    Math.min(Math.max(r.top + r.height / 2, 0), innerHeight - 1));
                if (hit && hit !== el && !el.contains(hit) && !hit.contains(el)
                    && !(el.labels && [...el.labels].some(l => l.contains(hit)))) {
                    why = 'covered by ' + describe(hit);
                }
            }
            if (el.matches('input[type=checkbox], input[type=radio]') && why) {
                // A visually hidden control whose label shows the focus instead.
                const label = el.labels && [...el.labels].find(l => { const lr = l.getBoundingClientRect(); return lr.width > 0 && lr.height > 0 && getComputedStyle(l).visibility !== 'hidden'; });
                if (label) why = '';
            }
            const stop = {index: window.__stops.length + 1, element: describe(el), visible: why === '', why, indicated: true,
                inside: window.__within ? el.closest(window.__within) !== null : true};
            window.__stops.push(stop);
            const shown = surfaces(el);
            window.__previous = {surfaces: shown, look: look(shown)};
            return stop.element;
        JS;

        for ($i = 0; $i < self::MAX_STOPS; $i++) {
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::TAB);
            if ($browser->driver->executeScript($describe) === "wrapped") {
                break;
            }
        }

        $stops = $browser->driver->executeScript("return window.__stops;");

        return $stops;
    }

    private function renderResultPage(string $theme): string
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        return $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web&dark_mode={$theme}")
            ->assertOk()
            ->getContent();
    }

    private function openRenderedPage(Browser $browser, string $html): void
    {
        $browser->visit("/de-DE/impressum");
        $browser->driver->executeScript("document.open(); document.write(arguments[0]); document.close();", [$html]);
        $browser->waitUntil("document.readyState === 'complete'")
            ->assertPresent("#results");
    }
}
