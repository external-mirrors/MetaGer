<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Spende extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/spende';
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
            ->waitForText("Ihre Spende")
            ->assertTitle("Spenden - MetaGer")
            ->switchLanguage("English")
            ->waitForText("Your Donation")
            ->assertTitle("Donation - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Su donación")
            ->assertTitle("Donaciones - MetaGer")
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
