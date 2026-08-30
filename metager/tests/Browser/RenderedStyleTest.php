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
     * Die beiden Nebenwege auf der Anmeldeseite sind gleich hoch.
     *
     * Dieselbe Sache wie bei den Zahlungsart-Kacheln und aus demselben Grund:
     * „Sicherungsdatei wählen“ trägt ein Feld und zwei Zeilen Hinweis,
     * „QR-Code scannen“ einen Knopf und eine — und wie viele Zeilen es wirklich
     * werden, entscheidet die Sprache. Zwei Rahmen, die auf verschiedenen
     * Linien enden, sehen aus wie ein Fehler.
     *
     * Der QR-Block steht `hidden` in der Auslieferung; diese Klasse fährt mit
     * Javascript, also ist er hier aufgedeckt — und genau dann muss es stimmen.
     */
    #[DataProvider("themes")]
    public function testBothWaysIntoTheLoginPageAreTheSameHeight(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE/anmelden?dark_mode={$theme}")
                ->waitFor("#login-qr:not([hidden])");

            $heights = $browser->script(
                "return Array.from(document.querySelectorAll('.login-alternative'))"
                    . ".map((tile) => Math.round(tile.getBoundingClientRect().height));"
            )[0];

            $this->assertCount(2, $heights, "Auf /anmelden stehen nicht zwei Nebenwege.");
            $this->assertCount(
                1,
                array_unique($heights),
                "Die beiden Nebenwege sind unterschiedlich hoch (" . implode(", ", $heights)
                    . " px). Das Raster in resources/less/metager/pages/login.less braucht "
                    . "grid-auto-rows: 1fr, und der Hinweis darin margin-top: auto."
            );
        });
    }

    /**
     * Ein abgewiesener Anmeldeversuch ist auf der Karte lesbar.
     *
     * Bootstraps .alert-danger — womit diese Meldung naheliegenderweise
     * ausgezeichnet worden wäre — färbt ihren Text #a94442 und lässt den Grund
     * durchscheinen. Auf der dunklen Karte der Anmeldeseite (#404040) sind das
     * 1,9:1, also unlesbar; deswegen hat der Fehler ein eigenes Tokenpaar.
     *
     * Nur ein Browser kann das beantworten: gefragt sind zwei var(), die erst
     * er zu Farben auflöst, und der Grund kommt von einem anderen Element als
     * die Schrift.
     */
    #[DataProvider("themes")]
    public function testARejectedLoginIsLegibleInBothPalettes(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $browser->visit("/de-DE/anmelden?dark_mode={$theme}&key_error=invalid_key")
                ->assertVisible(".login-card__error");

            $colours = $browser->script(
                "const error = document.querySelector('.login-card__error');"
                    . "const style = getComputedStyle(error);"
                    . "return [style.color, style.backgroundColor];"
            )[0];

            $ratio = $this->contrast($colours[0], $colours[1]);

            $this->assertGreaterThanOrEqual(
                4.5,
                $ratio,
                sprintf(
                    "Die Fehlermeldung steht in %s auf %s — das sind %.1f:1. --form-error-color "
                        . "und --form-error-background in resources/less/metager/variables.less "
                        . "müssen für die Palette „%s“ nachgezogen werden.",
                    $colours[0],
                    $colours[1],
                    $ratio,
                    $theme
                )
            );
        });
    }

    /**
     * Der QR-Code auf /schluessel-erstellen steht auf hellem Grund.
     *
     * Ein QR-Code ist schwarz auf durchsichtig: das PNG von endroid/qr-code hat
     * weiße Module, aber wer es auf einen dunklen Kasten legt, hat trotzdem
     * gute Chancen, ihn unlesbar zu machen — und die dunkle Karte dieser Seite
     * ist genau so ein Kasten. Ein Code, der zu wenig Kontrast hat, sieht
     * vollkommen in Ordnung aus; er wird nur nie eingelesen, und zwar von
     * jemandem, der ein Jahr später wieder in sein Konto will.
     *
     * Nur ein Browser beantwortet das: der Grund kommt aus einem var(), das
     * erst er auflöst, und ob er im Dunkelmodus mitkippt, entscheidet eine
     * Regel und keine Zeichenkette.
     */
    #[DataProvider("themes")]
    public function testTheQrCodeStaysReadableInBothPalettes(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $this->revealTheKey($browser, $theme)
                ->assertVisible(".create-save__qr img");

            $background = $browser->script(
                "return getComputedStyle(document.querySelector('.create-save__qr img'))"
                    . ".backgroundColor;"
            )[0];

            preg_match_all("/\\d+/", $background, $matches);
            $channels = array_map("intval", array_slice($matches[0], 0, 3));

            $this->assertGreaterThan(
                200,
                min($channels),
                "Der Grund hinter dem QR-Code ist in der Palette „{$theme}“ {$background}. "
                    . "Die Module des Codes sind schwarz — auf einem dunklen Grund liest ihn "
                    . "keine Kamera mehr. resources/less/metager/pages/key-create.less setzt "
                    . "dafür ein festes Weiß hinter das Bild."
            );
        });
    }

    /**
     * Die beiden Wege, den Schlüssel aufzubewahren, sind gleich hoch.
     *
     * Dieselbe Sache wie bei den Nebenwegen der Anmeldeseite: „Als Bild
     * speichern“ trägt ein Bild und eine Zeile Hinweis, „Lesezeichen“ ein Feld
     * und zwei — und wie viele Zeilen es wirklich werden, entscheidet die
     * Sprache.
     */
    #[DataProvider("themes")]
    public function testBothWaysToKeepTheKeyAreTheSameHeight(string $theme): void
    {
        $this->browse(function (Browser $browser) use ($theme) {
            $this->revealTheKey($browser, $theme);

            $heights = $browser->script(
                "return Array.from(document.querySelectorAll('.create-save__option'))"
                    . ".map((tile) => Math.round(tile.getBoundingClientRect().height));"
            )[0];

            $this->assertCount(2, $heights, "Auf /schluessel-erstellen stehen nicht zwei Wege.");
            $this->assertCount(
                1,
                array_unique($heights),
                "Die beiden Wege sind unterschiedlich hoch (" . implode(", ", $heights)
                    . " px). Das Raster in resources/less/metager/pages/key-create.less braucht "
                    . "grid-auto-rows: 1fr, und der Hinweis darin margin-top: auto."
            );
        });
    }

    /**
     * /schluessel-erstellen bis zu dem Zustand, in dem der Schlüssel dasteht.
     *
     * Diese Klasse fährt mit Javascript, und dann fängt die Seite mit einem
     * Knopf an statt mit dem Schlüssel: resources/js/key-create.js blendet ihn
     * weg, damit vor einem *zweiten* Schlüssel einmal nachgefragt wird. Alles,
     * was hier gemessen wird, steht erst dahinter.
     */
    private function revealTheKey(Browser $browser, string $theme): Browser
    {
        return $browser->visit("/de-DE/schluessel-erstellen?dark_mode={$theme}")
            ->waitFor("#key-create-start")
            ->click("#key-create-start")
            // Nicht auf das Feld: das ist schon während des Trommelwirbels da,
            // und der Rest der Karte ist es dann noch nicht.
            ->waitFor(".create-continue__button");
    }

    /**
     * Das Kontrastverhältnis zweier `rgb(...)`-Angaben nach WCAG 2.
     *
     * Beide Farben kommen deckend aus getComputedStyle, weil beide auf
     * denselben Kasten gesetzt sind — ein Alphakanal wäre hier eine andere
     * Rechnung und ist an dieser Stelle keiner.
     */
    private function contrast(string $foreground, string $background): float
    {
        $luminance = static function (string $colour): float {
            preg_match_all("/\\d+/", $colour, $matches);
            $channels = array_map(
                static function (string $value): float {
                    $channel = ((int) $value) / 255;

                    return $channel <= 0.03928
                        ? $channel / 12.92
                        : (($channel + 0.055) / 1.055) ** 2.4;
                },
                array_slice($matches[0], 0, 3)
            );

            return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
        };

        $lighter = $luminance($foreground);
        $darker = $luminance($background);
        if ($lighter < $darker) {
            [$lighter, $darker] = [$darker, $lighter];
        }

        return ($lighter + 0.05) / ($darker + 0.05);
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
