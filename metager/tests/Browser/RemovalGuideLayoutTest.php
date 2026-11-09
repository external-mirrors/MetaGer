<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * /entfernen on a wide screen, and the two hints its script adds.
 *
 * Where the table of contents lands is a question about the rendered layout,
 * and whether it follows the scroll position needs a real scroll. The script's
 * logic itself is in resources/js/removal/enhance.test.js.
 */
class RemovalGuideLayoutTest extends DuskTestCase
{
    public function testTheContentsSitBesideTheStepsOnAWideScreenAndStayInView(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)->visit("/de-DE/entfernen")->assertPresent("#removal");

            [$tocRight, $stepsLeft, $tocTop] = $browser->script(
                "const toc = document.querySelector('.removal-toc');"
                    . "window.scrollTo({ top: 1500, behavior: 'instant' });"
                    . "const box = toc.getBoundingClientRect();"
                    . "return [box.right, document.querySelector('.removal-steps').getBoundingClientRect().left, box.top];"
            )[0];

            $this->assertLessThan($stepsLeft, $tocRight, "The contents should be a column beside the steps.");
            $this->assertGreaterThanOrEqual(0, $tocTop, "The contents should stay in view while the steps scroll.");
        });
    }

    /** Less leaves an unparenthesised division alone, and the line then sits at the left edge. */
    public function testTheLineBetweenStepsRunsThroughTheirNumbers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)->visit("/de-DE/entfernen")->assertPresent("#removal");

            [$left, $number] = $browser->script(
                "const step = document.querySelector('.removal-step');"
                    . "return [parseFloat(getComputedStyle(step, '::after').left),"
                    . " parseFloat(getComputedStyle(step, '::before').width)];"
            )[0];

            $this->assertEqualsWithDelta($number / 2, $left, 1);
        });
    }

    public function testTheContentsSitAboveTheStepsOnATablet(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(768, 800)->visit("/de-DE/entfernen")->assertPresent("#removal");

            [$tocBottom, $stepsTop] = $browser->script(
                "return [document.querySelector('.removal-toc').getBoundingClientRect().bottom,"
                    . " document.querySelector('.removal-steps').getBoundingClientRect().top];"
            )[0];

            $this->assertLessThan($stepsTop, $tocBottom);
        });
    }

    public function testScrollingMovesTheCurrentStep(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)->visit("/de-DE/entfernen")->assertPresent("#removal");
            $browser->waitFor('.removal-toc a[aria-current="location"]');

            $browser->script("document.querySelector('#write-to-us').scrollIntoView({ behavior: 'instant' });");

            $browser->waitFor('.removal-toc a[href="#write-to-us"][aria-current="location"]');
            $this->assertCount(1, $browser->elements(".removal-toc a[aria-current]"));
        });
    }

    public function testOpeningARequestKindMarksItsRowOnTheCards(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1280, 800)->visit("/de-DE/entfernen")->assertPresent("#removal");
            $browser->waitFor('[data-removal-kind="personal_data"].removal-row-active');

            $browser->click("#kind-outdated summary");

            $browser->waitFor('[data-removal-kind="outdated"].removal-row-active');
            $browser->assertMissing('[data-removal-kind="personal_data"].removal-row-active');
        });
    }
}
