<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class WebsearchWidget extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/websearch';
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
            ->waitForText("Hier finden Sie ein Metager-Widget für Ihre Webseite.")
            ->assertTitle("Websuche-Widget - MetaGer")
            ->switchLanguage("English")
            ->waitForText("Here you find a Metager-Widget for your website.")
            ->assertTitle("Websearch-Widget - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Aquí encuentra el MetaGer-widget para su sitio web")
            ->assertTitle("Widget para buscar la web - MetaGer")
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
