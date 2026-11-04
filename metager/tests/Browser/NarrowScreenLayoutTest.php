<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DuskTestCase;

/**
 * /entfernen and /kontakt on a phone.
 *
 * Both pages sat flush against the screen edges, and the removal guide gave
 * every step's text a column of its own beside the step number, so on 390px
 * the text started almost a sixth of the way in. Whether text touches the
 * edge, or a card runs past it, is a question about the rendered layout, so
 * it is asked here and not of the .less files.
 */
class NarrowScreenLayoutTest extends DuskTestCase
{
    /** A common phone width. */
    private const WIDTH = 390;

    /** The smallest gap between text and the screen edge that reads as a margin. */
    private const MIN_GAP = 12;

    /** @return array<string, array{0: string, 1: string}> */
    public static function pages(): array
    {
        return [
            "removal guide" => ["/de-DE/entfernen", "#removal"],
            "contact page" => ["/de-DE/kontakt", "#kontakt"],
        ];
    }

    #[DataProvider("pages")]
    public function testTheTextKeepsAGapToTheScreenEdges(string $path, string $root): void
    {
        $this->browse(function (Browser $browser) use ($path, $root) {
            $browser->resize(self::WIDTH, 800)->visit($path)->assertPresent($root);

            [$viewport, $scrollWidth, $boxes] = $browser->script(
                "const root = document.querySelector('$root');"
                    . "const boxes = Array.from(root.querySelectorAll('h1, h2, h3, p, li, dt, dd, summary, section, details'))"
                    . ".filter((el) => el.getClientRects().length)"
                    . ".map((el) => { const r = el.getBoundingClientRect();"
                    . " return [el.tagName + '.' + el.className, Math.floor(r.left), Math.ceil(r.right)]; });"
                    . "return [document.documentElement.clientWidth, document.documentElement.scrollWidth, boxes];"
            )[0];

            $this->assertLessThanOrEqual($viewport, $scrollWidth, "$path scrolls sideways at " . self::WIDTH . "px.");
            $this->assertNotEmpty($boxes);
            foreach ($boxes as [$what, $left, $right]) {
                $this->assertGreaterThanOrEqual(self::MIN_GAP, $left, "$path: $what touches the left edge.");
                $this->assertLessThanOrEqual($viewport - self::MIN_GAP, $right, "$path: $what touches the right edge.");
            }
        });
    }

    /**
     * On a phone the step number sits beside the heading only; the text
     * below uses the full width instead of a column next to the number.
     */
    public function testTheGuidesStepsUseTheFullWidthOnAPhone(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(self::WIDTH, 800)->visit("/de-DE/entfernen")->assertPresent("#removal");

            [$rootLeft, $textLeft, $headingLeft] = $browser->script(
                "const step = document.querySelector('.removal-step');"
                    . "return [document.querySelector('#removal').getBoundingClientRect().left,"
                    . " step.querySelector(':scope > p').getBoundingClientRect().left,"
                    . " step.querySelector(':scope > h2').getBoundingClientRect().left];"
            )[0];

            $this->assertEqualsWithDelta($rootLeft + self::MIN_GAP, $textLeft, self::MIN_GAP, "A step's text is indented past the page margin.");
            $this->assertGreaterThan($textLeft, $headingLeft, "The heading should sit beside the step number.");
        });
    }
}
