<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class App extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/app';
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
            ->waitForText("Diese App bringt die volle Power unserer Suchmaschine auf ihr Smartphone.")
            ->assertTitle("Apps - MetaGer")
            ->switchLanguage("English")
            ->waitForText("This App brings the full Metager power to your smartphone.")
            ->assertTitle("Apps - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Diese App bringt die volle Power unserer Suchmaschine auf ihr Smartphone.")
            ->assertTitle("Aplicaciones - MetaGer")
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
