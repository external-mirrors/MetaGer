<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * MetaGer must work without client-side JavaScript; JS is an enhancement only.
 *
 * That requirement is not assertable from a feature test — it is a property of a
 * real rendering engine with scripting switched off. This is the reason the Dusk
 * suite still exists after the static-page tests moved to tests/Feature.
 */
class ProgressiveEnhancementTest extends DuskTestCase
{
    /**
     * Firefox with scripting off. The sidebar is a <label for>/<summary>
     * disclosure precisely so it keeps working here.
     *
     * @var array<string, bool>
     */
    protected array $driverPreferences = [
        "javascript.enabled" => false,
    ];

    public function testStartpageRendersWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->assertTitle(trans("titles.index", [], "de"))
                ->assertSee(trans("mg-story.privacy.title", [], "de"));
        });
    }

    /**
     * An anonymous visitor does not get a search box at all — the startpage
     * offers the key flow instead (see index.blade.php: the searchbar is only
     * included once a key is present). So the entry path that has to survive
     * without JavaScript is that CTA, and it has to be real links.
     *
     * The authorized search form and the result page itself need a key to reach,
     * so their no-JS coverage belongs with D0, where the search fixtures live.
     */
    public function testAnonymousStartpageOffersTheKeyFlowWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->assertPresent("#searchbar-replacement")
                ->assertPresent("#searchbar-replacement a.startpage-create-btn")
                ->assertPresent("#searchbar-replacement a.startpage-login-btn");

            // Real navigable links, not script hooks.
            $browser->click("#searchbar-replacement a.startpage-create-btn")
                ->waitForLocation("/de-DE/keys");
        });
    }

    public function testSidebarNavigationWorksWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("Datenschutz")
                ->waitForLocation("/de-DE/datenschutz")
                ->assertTitle(trans("titles.datenschutz", [], "de"));
        });
    }

    public function testNestedSidebarSectionOpensWithoutJavascript(): void
    {
        // The services group is a <summary>/<details> disclosure nested inside
        // the sidebar — two layers of CSS-only interaction, no script involved.
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("summary#navigationServices")
                ->clickLink("Widget")
                ->waitForLocation("/de-DE/widget")
                ->assertTitle(trans("titles.widget", [], "de"));
        });
    }
}
