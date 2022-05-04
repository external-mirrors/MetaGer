<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Widget extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/widget';
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
            ->waitForText("MetaGer zum Einbau in Ihre Webseite. Wählen Sie dafür aus, wo gesucht werden soll:")
            ->assertTitle("MetaGer Widget")
            ->switchLanguage("English")
            ->waitForText("MetaGer for usage on your website. Please choose the scope of your widget:")
            ->assertTitle("MetaGer Widget")
            ->switchLanguage("Español")
            ->waitForText("MetaGer para la integración en su sitio web. Para hacer esto, seleccione dónde buscar:")
            ->assertTitle("MetaGer Widget")
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
