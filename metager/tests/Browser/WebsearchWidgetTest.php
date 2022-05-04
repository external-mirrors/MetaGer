<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\WebsearchWidget;
use Tests\DuskTestCase;

class WebsearchWidgetTest extends DuskTestCase
{

    public function testWebsearchWidget()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("Widget")
                ->waitForLocation("/widget")
                ->clickLink("Suche im Web")
                ->waitForLocation("\/websearch\/")
                ->on(new WebsearchWidget);
        });
    }
}