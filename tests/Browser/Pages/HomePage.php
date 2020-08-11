<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class HomePage extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        # German
        $browser->assertPathIs($this->url())
            ->waitForText("Garantierte Privatsphäre", 1)
            ->assertTitle('MetaGer - Mehr als eine Suchmaschine')
            ->assertSee("Vielfältig & Frei")
            ->assertSee("100% Ökostrom")
            ->assertSee("Gemeinnütziger Verein")
            ->switchLanguage("English")
            ->waitForText("Guaranteed Privacy", 1)
            ->assertTitle('MetaGer: Privacy Protected Search & Find')
            ->assertSee("Diverse & free")
            ->assertSee("100 % renewable energy")
            ->assertSee("Nonprofit organization")
            ->switchLanguage("Español")
            ->waitForText("Privacidad garantizada", 1)
            ->assertTitle('MetaGer: Buscar & encontrar seguro, proteger la privacidad')
            ->assertSee("Diversa y libre")
            ->assertSee("Energía 100% renovable")
            ->assertSee("Organización sin ánimo de lucro")
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
            '@sidebarToggle' => 'label.sidebar-opener[for=sidebarToggle]',
        ];
    }
}
