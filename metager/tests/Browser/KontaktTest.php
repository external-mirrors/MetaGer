<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Kontakt;
use Tests\DuskTestCase;

class KontaktTest extends DuskTestCase
{
    public function testKontakt()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label#navigationKontakt")
                ->clickLink("Kontakt")
                ->waitForLocation("/kontakt")
                ->on(new Kontakt);
        });
    }
}