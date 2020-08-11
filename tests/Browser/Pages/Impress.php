<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Impress extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/impressum';
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
            ->waitForText("Haftungshinweis:")
            ->assertTitle("Impressum - MetaGer")
            ->switchLanguage("English")
            ->waitForText("Liability Note:")
            ->assertTitle("Site Notice - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Exención de responsabilidad")
            ->assertTitle("Aviso legal - MetaGer")
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
