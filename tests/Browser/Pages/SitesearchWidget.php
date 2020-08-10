<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class SitesearchWidget extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/sitesearch/';
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
            ->waitForText("Hier finden Sie ein Metager-Widget für Ihre Webseite.")
            ->assertTitle("Sitesearch-Widget - MetaGer")
            ->type("site", "https://metager.de")
            ->press("Generieren")
            ->waitForLocation("/sitesearch")
            ->waitForText("Hier finden Sie ein Metager-Widget für Ihre Webseite.")
            ->assertTitle("Sitesearch-Widget - MetaGer")
            ->visit($this->url())
            ->switchLanguage("English")
            ->waitForText("Here you find a Metager-Widget for your website.")
            ->assertTitle("Sitesearch-Widget - MetaGer")
            ->type("site", "https://metager.de")
            ->press("Generate");
        $location = "/en/sitesearch";
        if (env("APP_ENV", "") === "production") {
            $location = "/sitesearch";
        }
        $browser->waitForLocation($location)
            ->waitForText("Here you find a Metager-Widget for your website.")
            ->assertTitle("Sitesearch-Widget - MetaGer")
            ->visit($this->url())
            ->switchLanguage("Español")
            ->waitForText("Hier finden Sie ein Metager-Widget für Ihre Webseite.")
            ->assertTitle("Sitesearch-Widget - MetaGer")
            ->type("site", "https://metager.de")
            ->press("Generar");
        $location = "/es/sitesearch";
        if (env("APP_ENV", "") === "production") {
            $location = "/sitesearch";
        }
        $browser->waitForLocation($location)
            ->waitForText("Hier finden Sie ein Metager-Widget für Ihre Webseite.")
            ->assertTitle("Sitesearch-Widget - MetaGer")
            ->visit($this->url())
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
