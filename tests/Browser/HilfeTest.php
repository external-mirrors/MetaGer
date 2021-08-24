<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Hilfe;
use Tests\DuskTestCase;

class HilfeTest extends DuskTestCase
{
    public function testHilfe()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("Hilfe")
                ->waitForLocation("/hilfe")
                ->on(new Hilfe);
        });
    }
}