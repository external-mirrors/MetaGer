<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\About;
use Tests\Browser\Pages\App;
use Tests\Browser\Pages\Datenschutz;
use Tests\Browser\Pages\Hilfe;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\Impress;
use Tests\Browser\Pages\Kontakt;
use Tests\Browser\Pages\Spende;
use Tests\Browser\Pages\Team;
use Tests\DuskTestCase;

class StaticPagesTest extends DuskTestCase
{
    private $bs = null;
    /**
     * Tests for each static page on MetaGers website whether it can be reached by navigation
     *
     * @return void
     */
    public function testStartpage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage);
        });
    }

    public function testDatenschutz()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->clickLink("Datenschutz")
                ->waitForLocation("/datenschutz")
                ->on(new Datenschutz);
        });
    }

    public function testHilfe()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->clickLink("Hilfe")
                ->waitForLocation("/hilfe")
                ->on(new Hilfe);
        });
    }

    public function testSpenden()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->clickLink("Spenden")
                ->waitForLocation("/spende")
                ->on(new Spende);
        });
    }

    public function testApp()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->clickLink("MetaGer App")
                ->waitForLocation("/app")
                ->on(new App);
        });
    }

    public function testKontakt()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->click("label#navigationKontakt")
                ->clickLink("Kontakt")
                ->waitForLocation("/kontakt")
                ->on(new Kontakt);
        });
    }

    public function testTeam()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->click("label#navigationKontakt")
                ->clickLink("Team")
                ->waitForLocation("/team")
                ->on(new Team);
        });
    }

    public function testAbout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->click("label#navigationKontakt")
                ->clickLink("Über uns")
                ->waitForLocation("/about")
                ->on(new About);
        });
    }

    public function testImpress()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("@sidebarToggle")
                ->click("@sidebarToggle")
                ->click("label#navigationKontakt")
                ->clickLink("Impressum")
                ->waitForLocation("/impressum")
                ->on(new Impress);
        });
    }
}
