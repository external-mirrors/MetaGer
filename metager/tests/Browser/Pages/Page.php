<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

abstract class Page extends BasePage
{
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements()
    {
        return [
            '@sidebarToggle' => 'label.sidebar-opener[for=sidebarToggle]',
            '@languageDropdown' => '#navigationSprache',
        ];
    }

    public function switchLanguage(Browser $browser, $language)
    {
        $browser->waitFor("@sidebarToggle")
            ->click("@sidebarToggle")
            ->click("@languageDropdown")
            ->clickLink($language);
    }
}
