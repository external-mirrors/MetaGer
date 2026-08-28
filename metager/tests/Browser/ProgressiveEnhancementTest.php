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
     * Both paths, and the asymmetry between them is deliberate: logging in is a
     * button because it is what most people on this page need, and creating a
     * key is a link in the "first time here?" row because a second key splits
     * their balance. Both still have to work with scripting off — that is what
     * this pins. The returning-user copy swap is enhancement on top and is
     * covered in resources/js/accountBreadcrumb.test.js.
     *
     * The authorized search form and the result page itself need a key to reach,
     * so their no-JS coverage belongs with D0, where the search fixtures live.
     */
    public function testAnonymousStartpageOffersTheKeyFlowWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->assertPresent("#searchbar-replacement")
                ->assertPresent("#searchbar-replacement a.startpage-login-btn")
                ->assertPresent("#searchbar-replacement .first-time-line a.startpage-create-link");

            // Real navigable links, not script hooks: an href a browser with no
            // JavaScript can follow on its own, carrying the locale prefix that
            // LaravelLocalization put on the current request.
            //
            // Asserted rather than clicked. /keys is not a MetaGer route — nginx
            // proxies "^(/.*)?/keys" to the keymanager service — so following the
            // link leaves the application under test, and its answer (today a 301
            // to /de-DE/keys/) is that service's business, not this suite's.
            // Clicking through without JavaScript is covered below, on a page
            // MetaGer actually serves.
            $this->assertStringEndsWith(
                "/de-DE/keys",
                $browser->attribute("#searchbar-replacement a.startpage-create-link", "href")
            );

            $this->assertStringContainsString(
                "/de-DE/keys/key/enter",
                $browser->attribute("#searchbar-replacement a.startpage-login-btn", "href")
            );
        });
    }

    public function testSidebarNavigationWorksWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            // `:not(.close)` and not just `[for=sidebarToggle]`: both labels
            // point at the same checkbox, and the hidden ✕ comes first in the
            // document, so the bare selector resolves to the one that cannot be
            // clicked. See testTheOpenSidebarCanBeClosedAgain… below.
            $browser->visit("/de-DE")
                ->click("label.sidebar-opener:not(.close)")
                ->clickLink("Datenschutz")
                ->waitForLocation("/de-DE/datenschutz")
                ->assertTitle(trans("titles.datenschutz", [], "de"));
        });
    }

    /**
     * Opening the sidebar is half a disclosure; closing it again is the other
     * half, and it is the half that broke.
     *
     * The ≡ and the ✕ are two labels for one checkbox, swapped by
     * `#sidebarToggle:checked ~ .sidebar-opener`. When the ≡ moved into
     * .navigation-cluster the ✕ went with it, one level below where a sibling
     * combinator can reach — so it was never revealed, while the cluster around
     * it hid itself because the open sidebar covers that corner. The menu opened
     * and nothing on the page could close it again. On the startpage there was
     * no second label to fall back on.
     *
     * A rendering engine is what proves this: the whole mechanism is one
     * selector against a checkbox's state, and nothing about it is visible in
     * the rendered HTML, which contains both labels either way.
     */
    public function testTheOpenSidebarCanBeClosedAgainWithoutJavascript(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                // In the DOM from the start, and hidden until it is needed —
                // this is a CSS state swap, not markup that appears.
                ->assertPresent("label.sidebar-opener.close")
                ->assertMissing("label.sidebar-opener.close");

            // Named before it is used: the ✕ has to be a sibling of the
            // checkbox, not a descendant of the cluster, or the rule below
            // cannot reach it. Asserted on the DOM rather than on visibility,
            // because a ✕ in the wrong place is invisible for the wrong reason
            // and would pass the assertion above.
            $this->assertCount(
                0,
                $browser->elements(".navigation-cluster label.sidebar-opener.close"),
                "The close button is inside .navigation-cluster. `#sidebarToggle:checked ~ .sidebar-opener.close` "
                . "cannot reach it there, and the cluster hides itself while the sidebar is open — so the sidebar "
                . "opens with no way to close it. It belongs beside the checkbox in parts/sidebar.blade.php."
            );

            $browser->click("label.sidebar-opener:not(.close)")
                ->waitFor(".sidebar")
                ->assertVisible("label.sidebar-opener.close")
                ->click("label.sidebar-opener.close")
                ->waitUntilMissing(".sidebar");
        });
    }

    public function testNestedSidebarSectionOpensWithoutJavascript(): void
    {
        // The services group is a <summary>/<details> disclosure nested inside
        // the sidebar — two layers of CSS-only interaction, no script involved.
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->click("label.sidebar-opener:not(.close)")
                ->click("summary#navigationServices")
                ->clickLink("Widget")
                ->waitForLocation("/de-DE/widget")
                ->assertTitle(trans("titles.widget", [], "de"));
        });
    }
}
