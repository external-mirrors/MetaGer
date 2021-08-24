<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Impress;
use Tests\DuskTestCase;

class ImpressumTest extends DuskTestCase
{

    public function testImpress()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label#navigationKontakt")
                ->clickLink("Impressum")
                ->waitForLocation("/impressum")
                ->on(new Impress);
        });
    }
}