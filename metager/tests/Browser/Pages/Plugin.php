<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Plugin extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/plugin';
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
            ->waitForText("MetaGer zu Firefox hinzufügen")
            ->assertTitle("Plugin - MetaGer")
            ->switchLanguage("English")
            ->waitForText("Add MetaGer to your Firefox")
            ->assertTitle("Plugin - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Añadir MetaGer a Firefox")
            ->assertTitle("Plugin - MetaGer")
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
