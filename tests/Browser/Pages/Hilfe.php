<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Hilfe extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/hilfe';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url())
            ->waitForText("MetaGer - Hilfe")
            ->assertTitle("Hilfe - MetaGer")
            ->switchLanguage("English")
            ->waitForText("MetaGer Help")
            ->assertTitle("Help - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Ayuda de MetaGer")
            ->assertTitle("Ayuda - MetaGer")
            ->switchLanguage("Deutsch");

    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}
