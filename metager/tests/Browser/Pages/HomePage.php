<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;
use LaravelLocalization;

class HomePage extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url($locale = null)
    {
        return LaravelLocalization::getLocalizedUrl($locale, "/");
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $locale_data) {
            $url = $this->url($locale);
            $lang = \preg_replace("/^([a-zA-Z]+)-.*/", "$1", $locale);

            if (!file_exists(lang_path($lang))) {
                continue;
            }

            $browser->visit($url)
                ->waitForText(trans("mg-story.privacy.title", [], $lang))
                ->assertTitle(trans("titles.index", [], $lang))
                ->assertSee(trans("mg-story.ngo.title", [], $lang))
                ->assertSee(trans("mg-story.diversity.title", [], $lang))
                ->assertSee(trans("mg-story.eco.title", [], $lang))
                ->assertSee(trans("index.plugin", [], $lang));
        }
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