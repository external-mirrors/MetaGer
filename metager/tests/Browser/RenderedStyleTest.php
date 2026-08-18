<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
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
}
