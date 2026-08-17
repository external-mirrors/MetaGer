<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Locale-prefixed URLs serve the right language.
 *
 * This is the one part of the old static-page Dusk suite that genuinely needs a
 * server: routes are registered inside a group whose prefix comes from
 * Localization::setLocale(), evaluated per boot from request()->segment(1). Under
 * `artisan test` the console kernel fixes that request from config('app.url'), so
 * the whole feature suite runs as a single locale and cannot reach /de-DE/about
 * at all. A real request to a real FPM can.
 *
 * Deliberately a few representative locales rather than the full 13-page x
 * 19-locale matrix the old suite walked: that matrix was really asserting that
 * translation keys resolve, which
 * Tests\Feature\StaticPagesTest::testEveryLanguageDefinesThePageTranslations now
 * does without a browser.
 */
class LocalizedRoutingTest extends DuskTestCase
{
    /**
     * Routing does not depend on scripting, so prove that at the same time.
     *
     * @var array<string, bool>
     */
    protected array $driverPreferences = [
        "javascript.enabled" => false,
    ];

    public function testGermanPrefixServesGermanContent(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/de-DE/about")
                ->assertTitle(trans("titles.about", [], "de"))
                ->assertSee(trans("about.head.3", [], "de"));
        });
    }

    public function testBritishEnglishPrefixServesEnglishContent(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/en-GB/about")
                ->assertTitle(trans("titles.about", [], "en"))
                ->assertSee(trans("about.head.3", [], "en"));
        });
    }

    public function testSpanishPrefixServesSpanishContent(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/es-ES/about")
                ->assertTitle(trans("titles.about", [], "es"))
                ->assertSee(trans("about.head.3", [], "es"));
        });
    }

    /**
     * The legacy two-letter country codes still redirect to their four-letter
     * locale (LocalizationRedirect::redirectTwoLetterCountryCode).
     */
    public function testLegacyTwoLetterCountryCodeRedirects(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/es/about")
                ->waitForLocation("/es-ES/about")
                ->assertTitle(trans("titles.about", [], "es"));
        });
    }
}
