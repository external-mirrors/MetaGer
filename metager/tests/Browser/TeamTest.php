<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Team;
use Tests\DuskTestCase;

class TeamTest extends DuskTestCase
{

    public function testTeam()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label#navigationKontakt")
                ->clickLink("Team")
                ->waitForLocation("/team")
                ->on(new Team);
        });
    }
}