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
            ->waitForText("Was MetaGer auszeichnet")
            ->assertTitle("Über Uns - MetaGer")
            ->switchLanguage("English")
            ->waitForText("MetaGer - Characteristic qualities")
            ->assertTitle("About Us - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Was MetaGer auszeichnet")
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
