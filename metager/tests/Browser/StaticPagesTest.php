<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\About;
use Tests\Browser\Pages\App;
use Tests\Browser\Pages\Datenschutz;
use Tests\Browser\Pages\Hilfe;
use Tests\Browser\Pages\Impress;
use Tests\Browser\Pages\Kontakt;
use Tests\Browser\Pages\Plugin;
use Tests\Browser\Pages\SitesearchWidget;
use Tests\Browser\Pages\Spende;
use Tests\Browser\Pages\Team;
use Tests\Browser\Pages\WebsearchWidget;
use Tests\Browser\Pages\Widget;
use Tests\DuskTestCase;

class StaticPagesTest extends DuskTestCase
{
    /**
     * Tests for each static page on MetaGers website whether it can be reached by navigation
     *
     * @return void
     */
    public function testStartpage()
    {
        // Startpage
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage);
        });
    }

    public function testAbout()
    {
        // About
        $this->browse(function (Browser $browser) {
            $browser->visit(new About);
        });
    }

    public function testApp()
    {
        // App
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("MetaGer App")
                ->waitForLocation("/de-DE/app")
                ->on(new App);
        });
    }

    public function testDatenschutz()
    {
        // Datenschutz
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("Datenschutz")
                ->waitForLocation("/de-DE/datenschutz")
                ->on(new Datenschutz);
        });
    }

    public function testHilfe()
    {
        // Hilfe
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("Hilfe")
                ->waitForLocation("/de-DE/hilfe")
                ->on(new Hilfe);
        });
    }

    public function testImpressum()
    {
        // Impressum
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label#navigationKontakt")
                ->clickLink("Impressum")
                ->waitForLocation("/de-DE/impressum")
                ->on(new Impress);
        });
    }

    public function testKontakt()
    {
        // Kontakt
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label#navigationKontakt")
                ->clickLink("Kontakt")
                ->waitForLocation("/de-DE/kontakt")
                ->on(new Kontakt);
        });
    }

    public function testPlugin()
    {
        // Plugin
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("MetaGer Plugin")
                ->waitForLocation("/de-DE/plugin")
                ->on(new Plugin);
        });
    }

    public function testSpenden()
    {
        //Spenden
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("Spenden")
                ->waitForLocation("/de-DE/spende")
                ->on(new Spende);
        });
    }

    public function testTeam()
    {
        // Team
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label#navigationKontakt")
                ->clickLink("Team")
                ->waitForLocation("/de-DE/team")
                ->on(new Team);
        });
    }

    public function testWidget()
    {
        // Widget
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("Widget")
                ->waitForLocation("/de-DE/widget")
                ->on(new Widget);
        });
    }


    public function testWebsearchWidget()
    {
        // Websearch Widget
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("Widget")
                ->waitForLocation("/de-DE/widget")
                ->clickLink("Suche im Web")
                ->waitForLocation("/de-DE/websearch/")
                ->on(new WebsearchWidget);
        });
    }

    public function testSitesearchWidget()
    {
        // Sitesearch Widget
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("Widget")
                ->waitForLocation("/de-DE/widget")
                ->clickLink("Suche nur auf einer Domain")
                ->waitForLocation("/de-DE/sitesearch/")
                ->on(new SitesearchWidget);
        });
    }

}