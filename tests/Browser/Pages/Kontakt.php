<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Kontakt extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/kontakt';
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
            ->waitForText("Sicheres Kontaktformular")
            ->assertTitle("Kontakt - MetaGer")
            ->switchLanguage("English")
            ->waitForText("Secure Contact Form")
            ->assertTitle("Contact - MetaGer")
            ->switchLanguage("Español")
            ->waitForText("Formulario de contacto seguro")
            ->assertTitle("Contacto - MetaGer")
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
