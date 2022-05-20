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
            ->assertSee("Gemeinnütziger Verein")
            ->assertSee("Vielfältig & Frei")
            ->assertSee("100% Ökostrom")
            ->assertSee("Jetzt MetaGer installieren")
            ->switchLanguage("English")
            ->waitForText("Guaranteed Privacy", 1)
            ->assertTitle('MetaGer: Privacy Protected Search & Find')
            ->assertSee("Run by a Nonprofit Organization")
            ->assertSee("Diverse & Free")
            ->assertSee("100% Renewable Energy")
            ->assertSee("Install MetaGer Now")
            ->switchLanguage("Español")
            ->waitForText("Privacidad garantizada", 1)
            ->assertTitle('MetaGer: Buscar & encontrar seguro, proteger la privacidad')
            ->assertSee("Organización sin ánimo de lucro")
            ->assertSee("Diversos y libres")
            ->assertSee("100% electricidad verde")
            ->assertSee("Instale MetaGer ahora")
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
