<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The other half of the progressive-enhancement contract.
 *
 * tests/Browser/ProgressiveEnhancementTest asserts the site still works with
 * scripting off. That is only half a guarantee: a bundle that never executes
 * also passes it, because everything falls back. This asserts the enhancement
 * actually happens when a browser can run it.
 *
 * It exists because it didn't, for the whole life of the Vite migration. Vite
 * emits ES modules, the layouts emitted <script src> without type="module", and
 * a bundle importing a shared chunk is a syntax error as a classic script — so
 * six of thirteen entries, utility.js and scriptResultPage.js among them,
 * downloaded on every page and ran on none. Nothing failed: every enhanced
 * control still had its no-JS fallback, which is exactly why this went unseen
 * until someone noticed the settings page needed its Save button pressed.
 *
 * So these assert observable *behaviour*, not markup. A test for type="module"
 * would pin today's mechanism; these fail for any reason the scripts stop
 * running. AssetPipelineTest carries the cheap markup check alongside, for the
 * fast feedback a browser suite cannot give.
 */
class ScriptExecutionTest extends DuskTestCase
{
    /**
     * utility.js is loaded by every page on the site, and hiding .no-js is the
     * first thing it does. If it runs, the settings page's Save buttons are
     * hidden — they exist for the visitor who cannot run this script.
     */
    public function testTheSharedBundleRuns(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE/meta/settings")
                ->waitFor("#more-settings")
                ->assertScript(
                    "document.querySelector('#more-settings button.no-js').classList.contains('hide')",
                    true
                );
        });
    }

    /**
     * scriptSettings.js turns every settings <select> into its own submit: pick
     * a value and the form goes. Without it the visitor has to reach the Save
     * button, which is the fallback, not the enhanced path.
     *
     * Asserted through the theme because it is the one setting whose effect is
     * visible on the very page that submitted it — the reply carries
     * data-theme on <html>, so a passing test proves the change round-tripped
     * rather than merely that a select fired an event.
     */
    public function testChangingASettingSubmitsItsForm(): void
    {
        $this->browse(function (Browser $browser) {
            // Read through a script rather than a selector: waitFor and
            // assertMissing decide on *visibility*, and WebDriver does not call
            // the root element displayed, so a selector on <html> neither
            // matches when the attribute is there nor means anything when it
            // is not.
            $browser->visit("/de-DE/meta/settings")
                ->waitFor("#more-settings select[name=dark_mode]")
                ->assertScript("document.documentElement.hasAttribute('data-theme')", false)
                ->select("dark_mode", "dark")
                ->waitUntil("document.documentElement.dataset.theme === 'dark'");
        });
    }
}
