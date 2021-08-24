<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Widget;
use Tests\DuskTestCase;

class WidgetTest extends DuskTestCase
{
    public function testWidget()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("Widget")
                ->waitForLocation("/widget")
                ->on(new Widget);
        });
    }
}