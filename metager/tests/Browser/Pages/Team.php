<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Team extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/team';
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
            ->waitForText("geschäftsführender Vorstand")
            ->assertTitle("Team - MetaGer")
            ->switchLanguage("English")
            ->waitForText("CEO")
            ->assertTitle("Team - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Director ejecutivo [CEO]")
            ->assertTitle("Nuestra gente - MetaGer")
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
