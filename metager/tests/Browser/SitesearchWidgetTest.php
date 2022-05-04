<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\SitesearchWidget;
use Tests\DuskTestCase;

class SitesearchWidgetTest extends DuskTestCase
{

    public function testSitesearchWidget()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("Widget")
                ->waitForLocation("/widget")
                ->clickLink("Suche nur auf einer Domain")
                ->waitForLocation("/sitesearch/")
                ->on(new SitesearchWidget);
        });
    }
}