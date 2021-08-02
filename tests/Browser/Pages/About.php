<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class About extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/about';
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
            ->waitForText("Wofür wir stehen")
            ->assertTitle("Über Uns - MetaGer")
            ->switchLanguage("English")
            ->waitForText("What we stand for")
            ->assertTitle("About Us - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Wofür wir stehen")
            ->assertTitle("Sobre nosotros - MetaGer")
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
