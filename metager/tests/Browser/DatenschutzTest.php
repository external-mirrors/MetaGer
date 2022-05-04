<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Datenschutz;
use Tests\DuskTestCase;

class DatenschutzTest extends DuskTestCase
{
    public function testDatenschutz()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->clickLink("Datenschutz")
                ->waitForLocation("/datenschutz")
                ->on(new Datenschutz);
        });
    }
}