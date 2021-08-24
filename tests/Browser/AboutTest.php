<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\About;
use Tests\DuskTestCase;

class AboutTest extends DuskTestCase
{

    public function testAbout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label#navigationKontakt")
                ->clickLink("Über uns")
                ->waitForLocation("/about")
                ->on(new About);
        });
    }
}