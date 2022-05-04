<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Spende;
use Tests\DuskTestCase;

class SpendenTest extends DuskTestCase
{
    public function testSpenden()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("Spenden")
                ->waitForLocation("/spende")
                ->on(new Spende);
        });
    }
}