<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * A locale-prefixed URL serves that locale, and the legacy prefixes still land.
 *
 * This used to be a Dusk test, and only because it had to be: the locale was a
 * route group prefix evaluated while routes were registered, so under
 * `artisan test` the whole suite ran as the single locale of the console
 * kernel's synthetic request and `/de-DE/about` was not a route that existed.
 * A real request to a real FPM was the only way to reach it.
 *
 * `ResolveLocale` decides the locale per request instead, so the same coverage
 * runs in-process in milliseconds. Nothing here needs a browser: it is a URL
 * going in and a language coming out.
 */
class LocalizedRoutingTest extends TestCase
{
    /** @return array<string, array{0: string, 1: string}> */
    public static function locales(): array
    {
        return [
            "German" => ["de-DE", "de"],
            "British English" => ["en-GB", "en"],
            "Spanish" => ["es-ES", "es"],
        ];
    }

    #[DataProvider("locales")]
    public function testAPrefixServesThatLanguage(string $locale, string $language): void
    {
        $page = $this->get("/$locale/about", ["Sec-Fetch-Mode" => "navigate"]);

        $page->assertOk();
        $page->assertSee(trans("titles.about", [], $language), false);
        $page->assertSee(trans("about.head.3", [], $language), false);
    }

    /** The `<html lang>` a screen reader and a search engine both read. */
    public function testThePrefixIsDeclaredAsTheDocumentLanguage(): void
    {
        $this->get("/es-ES/about", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSee('<html lang="es-ES"', false);
    }

    /**
     * Two-letter prefixes were what we handed out before switching to BCP-47 in
     * July 2023; `LocalizationRedirect::redirectTwoLetterCountryCode()` keeps
     * those URLs working.
     */
    public function testALegacyTwoLetterPrefixRedirectsToItsLocale(): void
    {
        $this->get("/es/about", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect(rtrim(config("app.url"), "/") . "/es-ES/about");
    }

    /**
     * `hideDefaultLocaleInURL`: a URL that names the locale the request would
     * have resolved to anyway is redirected to the bare one, so that a page has
     * a single canonical address.
     */
    public function testTheDefaultLocaleIsRedirectedOutOfThePath(): void
    {
        $this->get("/en-US/about", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect("/about");
    }
}
