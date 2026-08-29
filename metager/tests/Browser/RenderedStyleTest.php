<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DuskTestCase;

/**
 * Assertions about CSS that only a rendering engine can settle.
 *
 * A feature test sees the stylesheet link, never the cascade. Anything here has
 * to be a property that survives the build, the theme and the browser's own
 * defaults — not a rule that merely exists in a .less file.
 *
 * Unlike ProgressiveEnhancementTest this class runs with JavaScript on, because
 * reading computed styles goes through WebDriver's script execution.
 */
class RenderedStyleTest extends DuskTestCase
{
    /**
     * Regression test for the <details> disclosure triangle.
     *
     * general/base.less tried to suppress it with five selectors, of which only
     * ::-webkit-details-marker exists; ::-moz-details-marker, ::-ms-details-marker,
     * ::-o-details-marker and a bare ::details-marker are not implemented by any
     * browser. So the marker was hidden in Safari and drawn everywhere else,
     * including Firefox. lightningcss flagged them while replacing laravel-mix
     * with Vite — webpack had passed them through without a word.
     *
     * Firefox is what Dusk drives, which makes it both the browser that was
     * broken and the one that can prove the fix. list-style is what does the
     * work: the triangle is a list marker.
     */
    public function testSummaryDisclosureMarkerIsHidden(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->assertPresent("summary#navigationServices");

            $listStyleType = $browser->script(
                "return getComputedStyle(document.querySelector('summary#navigationServices'))"
                    . ".listStyleType;"
            )[0];

            $this->assertSame(
                "none",
                $listStyleType,
                "The <summary> disclosure marker is showing again — check the list-style rule "
                    . "on `summary` in resources/less/metager/general/base.less."
            );
        });
    }

    /**
     * Die Zahlungsart-Kacheln auf /preise sind alle gleich hoch.
     *
     * Sie waren es nicht: das Raster ließ jede Zeile so hoch werden wie ihr
     * Inhalt, und "Kredit- / Debitkarte" bricht als einzige Beschriftung auf
     * zwei Zeilen um — also war die erste Zeile 40px höher als die zweite und
     * die Reihe sah aus, als wäre etwas verrutscht. `grid-auto-rows: 1fr` macht
     * alle Zeilen so hoch wie die höchste, und das ist eine Aussage über den
     * Umbruch von übersetztem Text und über das Raster, die nur eine
     * Layout-Engine beantworten kann.
     */
    #[DataProvider("themes")]
    public function testEveryPaymentTileIsTheSameHeight(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE/preise?dark_mode={$theme}")
                ->assertPresent("#payment-methods");

            $heights = $browser->script(
                "return Array.from(document.querySelectorAll('.payment-methods-container'))"
                    . ".map((list) => Array.from(list.querySelectorAll('.payment-method'))"
                    . ".map((tile) => Math.round(tile.getBoundingClientRect().height)));"
            )[0];

            $this->assertNotEmpty($heights, "Auf /preise steht keine Liste mit Zahlungsarten.");

            foreach ($heights as $index => $list) {
                $this->assertNotEmpty($list, "Die {$index}. Liste der Zahlungsarten ist leer.");
                $this->assertCount(
                    1,
                    array_unique($list),
                    "Die Kacheln der {$index}. Liste sind unterschiedlich hoch ("
                        . implode(", ", $list) . " px). Das Raster in "
                        . "resources/less/metager/pages/price.less braucht grid-auto-rows: 1fr — "
                        . "sonst ist jede Zeile nur so hoch wie ihr eigener Inhalt."
                );
            }
        });
    }

    /**
     * Der Grund der Kacheln ist in beiden Paletten hell — aber im Dunkelmodus
     * nicht weiß.
     *
     * Hell muss er sein, weil Markenzeichen weder umgefärbt noch abgedunkelt
     * werden dürfen und PayPal, Bancontact und MyBank schwarze Wortmarken sind,
     * die auf MetaGers fast schwarzem Grund verschwänden. Weiß darf er im
     * Dunkelmodus nicht sein, weil elf Kacheln in #fff dann das Hellste auf der
     * Seite sind und blenden.
     *
     * Beides steckt in @payment-tile-background, und nur ein Browser löst das
     * var() zu einer Farbe auf.
     */
    #[DataProvider("themes")]
    public function testThePaymentTilesCarryBrandMarksWithoutGlaring(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE/preise?dark_mode={$theme}")
                ->assertPresent(".payment-method");

            $background = $browser->script(
                "return getComputedStyle(document.querySelector('.payment-method')).backgroundColor;"
            )[0];

            preg_match_all("/\\d+/", $background, $channels);
            $channels = array_map("intval", array_slice($channels[0], 0, 3));
            $this->assertCount(3, $channels, "Der Kachelgrund ist keine Farbe: $background");

            // Genug Kontrast zu einer schwarzen Wortmarke, in beiden Paletten.
            $this->assertGreaterThanOrEqual(
                150,
                min($channels),
                "Der Kachelgrund ($background) ist zu dunkel für die schwarzen Wortmarken "
                    . "(PayPal, Bancontact, MyBank). --payment-tile-background in "
                    . "resources/less/metager/variables.less."
            );

            if ($theme === "dark") {
                $this->assertLessThan(
                    240,
                    max($channels),
                    "Der Kachelgrund ($background) ist im Dunkelmodus wieder weiß — das ist "
                        . "die Leuchtwand, wegen der --payment-tile-background überhaupt ein "
                        . "eigener Token ist."
                );
            }
        });
    }

    /**
     * Beide Paletten, so wie ThemeColorsTest sie benennt.
     */
    public static function themes(): array
    {
        return [
            "light" => ["light"],
            "dark" => ["dark"],
        ];
    }
}
