<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;

class StartpageTest extends DuskTestCase
{
    /**
     * Tests for each static page on MetaGers website whether it can be reached by navigation
     *
     * @return void
     */
    public function testStartpage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage);
        });
    }
}
