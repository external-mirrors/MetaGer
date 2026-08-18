<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\ResolvesThemeColors;
use Tests\DuskTestCase;

/**
 * The default: no theme chosen, and a system that asks for dark.
 *
 * This is the path most visitors are on, and the one with the most machinery
 * behind it — today a second full stylesheet carried on a media attribute, and
 * after the rework a set of custom properties under prefers-color-scheme. The
 * assertion is that it lands on exactly the palette `dark_mode=dark` produces,
 * so the two ways of arriving at the dark theme cannot drift apart.
 *
 * Its own test class because Firefox preferences are fixed when the browser
 * starts, so prefers-color-scheme cannot be varied inside one.
 */
class ThemeColorsSystemTest extends DuskTestCase
{
    use ResolvesThemeColors;

    /**
     * Firefox reports prefers-color-scheme: dark for this, without the browser
     * having to run on a dark desktop.
     *
     * @var array<string, bool|int|string>
     */
    protected array $driverPreferences = [
        "ui.systemUsesDarkTheme" => 1,
    ];

    public function testSystemThemeFollowsTheBrowserIntoDark(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE?dark_mode=system&zitate=off")
                ->assertPresent("#searchbar-replacement");

            $this->assertPaletteMatchesSnapshot($this->resolvePalette($browser), "dark");
        });
    }

    public function testDarkOnlyMarkupShowsWhenTheSystemAsksForDark(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE?dark_mode=system&zitate=off");

            $visibility = $this->resolveThemeOnlyVisibility($browser);

            $this->assertSame("none", $visibility["light"], "light-only markup is visible under a dark system theme");
            $this->assertNotSame("none", $visibility["dark"], "dark-only markup is hidden under a dark system theme");
        });
    }
}
