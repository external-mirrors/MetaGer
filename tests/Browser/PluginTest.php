<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Plugin;
use Tests\DuskTestCase;

class PluginTest extends DuskTestCase
{
    public function testPlugin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/")
                ->waitFor("label.sidebar-opener[for=sidebarToggle]")
                ->click("label.sidebar-opener[for=sidebarToggle]")
                ->click("label[for=servicesToggle]")
                ->clickLink("MetaGer Plugin")
                ->waitForLocation("/plugin")
                ->on(new Plugin);
        });
    }
}