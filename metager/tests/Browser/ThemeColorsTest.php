<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Browser\Concerns\ResolvesThemeColors;
use Tests\DuskTestCase;

/**
 * Characterization of the rendered colours in each theme.
 *
 * Written before the theme is reworked from two full stylesheets into one set of
 * custom properties, and it exists to make that rework checkable: the palette has
 * to come out identical, declaration for declaration, however it is expressed
 * underneath.
 *
 * Why a browser: after the rework a colour is authored as var(--text-color), and
 * only a rendering engine turns that back into a colour. getComputedStyle is the
 * one instrument that reads the old world and the new one the same way.
 *
 * Why not hand-picked selectors: they would pin the handful of places someone
 * thought of. Every rule in every loaded stylesheet is walked instead — 808
 * declarations, of which 297 differ between the themes.
 *
 * Regenerate deliberately, never just to make a red test green:
 *
 *   UPDATE_THEME_SNAPSHOTS=1 php artisan dusk --filter ThemeColors
 *
 * and read the diff. Every changed line is a colour that changed on the site.
 */
class ThemeColorsTest extends DuskTestCase
{
    use ResolvesThemeColors;

    /**
     * The two themes a visitor picks explicitly, by the query parameter
     * SearchSettings reads them from.
     */
    public static function themes(): array
    {
        return [
            "light" => ["light"],
            "dark" => ["dark"],
        ];
    }

    #[DataProvider("themes")]
    public function testPaletteIsUnchanged(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $this->assertPaletteMatchesSnapshot(
                $this->resolvePalette($this->visitStartpage($browser, $theme)),
                $theme
            );
        });
    }

    /**
     * Left to the system, and with nothing telling the browser otherwise, the
     * light palette is what a visitor gets — the same one `light` asks for.
     * ThemeColorsSystemTest is the other half of this: the same page, in a
     * browser whose system theme is dark.
     */
    public function testSystemThemeFollowsTheBrowserIntoLight(): void
    {
        $this->browse(function (Browser $browser) {
            $this->assertPaletteMatchesSnapshot(
                $this->resolvePalette($this->visitStartpage($browser, "system")),
                "light"
            );
        });
    }

    /**
     * The two visibility classes are the rest of the theme: markup that exists
     * for one of them only. They are not colours, so the walk skips them, but
     * they have to survive the merge into a single stylesheet just the same.
     */
    #[DataProvider("themes")]
    public function testThemeOnlyMarkupIsHidden(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $visibility = $this->resolveThemeOnlyVisibility($this->visitStartpage($browser, $theme));

            $hidden = $theme === "dark" ? "light" : "dark";
            $shown = $theme === "dark" ? "dark" : "light";

            $this->assertSame("none", $visibility[$hidden], "{$hidden}-only markup is visible in the {$theme} theme");
            $this->assertNotSame("none", $visibility[$shown], "{$shown}-only markup is hidden in the {$theme} theme");
        });
    }

    /**
     * The startpage, because it is the one page reachable without a key that
     * pulls in the full stylesheet along with the startpage's own pair.
     *
     * zitate=off keeps a random quote from varying the DOM. The walk reads
     * stylesheets rather than the page, but a stable page makes a failure
     * easier to reproduce by hand.
     */
    protected function visitStartpage(Browser $browser, string $theme): Browser
    {
        return $browser
            ->visit("/de-DE?dark_mode={$theme}&zitate=off")
            ->assertPresent("#searchbar-replacement");
    }
}
