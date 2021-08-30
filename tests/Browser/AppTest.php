<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\App;
use Tests\DuskTestCase;

class AppTest extends DuskTestCase
{

    public function testApp()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("MetaGer App")
                ->waitForLocation("/app")
                ->on(new App);
        });
    }
}