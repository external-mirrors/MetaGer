<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Datenschutz extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/datenschutz';
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
            ->waitForText("Datenschutzerklärung")
            ->assertTitle("Datenschutz und Privatsphäre - MetaGer")
            ->switchLanguage("English")
            ->waitForText("Data protection")
            ->assertTitle("Privacy - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Data protection")
            ->assertTitle("Protección de datos y privacidad - MetaGer")
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
