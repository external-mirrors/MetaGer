<?php

namespace Tests\Feature;

use App\Localization\LocaleContext;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * What locale a request gets, and what is left of its path afterwards.
 *
 * The resolution order, which every MetaGer codebase is meant to implement
 * identically: an explicit `?lang=`/`MG-Locale`, the URL path prefix, the
 * `mg_locale` cookie, `Accept-Language`, and only then the host.
 *
 * These were characterization tests for the rules that came before, and they
 * said the two rules on the list to go would make them fail. Both did:
 *
 *  - `metager.de` no longer discounts an `Accept-Language` that is not German.
 *    An English browser on the German domain gets English.
 *  - The host is no longer an input except as the final fallback, and reaching
 *    it never moves anyone: both domains serve every locale.
 *
 * What is asserted here is the *decision*. That no redirect follows from it is
 * `InterfaceLocaleCookieTest`'s subject.
 */
class LocaleResolutionTest extends TestCase
{
    /** @param array<string, string> $cookies */
    private function resolve(string $url, ?string $acceptLanguage = null, array $cookies = []): LocaleContext
    {
        $request = Request::create($url);
        if ($acceptLanguage !== null) {
            $request->headers->set("Accept-Language", $acceptLanguage);
        }
        foreach ($cookies as $name => $value) {
            $request->cookies->set($name, $value);
        }

        return LocaleContext::resolve($request);
    }

    /**
     * @return array<string, array{0: string, 1: string|null, 2: string, 3: string, 4: string}>
     *   name => [url, Accept-Language, locale, locale needing no prefix, path segment]
     */
    public static function cases(): array
    {
        return [
            "German browser on the German domain"
                => ["http://metager.de/", "de-DE,de;q=0.9", "de-DE", "de-DE", ""],
            "English browser on the German domain is now believed"
                => ["http://metager.de/", "en-US,en;q=0.9", "en-US", "en-US", ""],
            "English browser elsewhere"
                => ["http://metager.org/", "en-US,en;q=0.9", "en-US", "en-US", ""],
            "German browser elsewhere, and it stays there"
                => ["http://metager.org/", "de-DE,de;q=0.9", "de-DE", "de-DE", ""],
            "a language we serve but no browser named"
                => ["http://metager.org/fi-FI/about", "en-US,en;q=0.9", "fi-FI", "en-US", "fi-FI"],
            "the path outranks the header"
                => ["http://metager.org/en-US/about", "de-DE,de;q=0.9", "en-US", "de-DE", "en-US"],
            "and the host has no exception left to outrank"
                => ["http://metager.de/es-ES/about", "en-US,en;q=0.9", "es-ES", "en-US", "es-ES"],
            "a bare language tag maps to its home region"
                => ["http://metager.org/", "es", "es-ES", "es-ES", ""],
            "every language we ship is negotiable, not just three"
                => ["http://metager.org/", "fr", "fr-FR", "fr-FR", ""],
            "nothing recognisable falls back to the host"
                => ["http://metager.org/", "kl-GL", "en-US", "en-US", ""],
            "and the German domain is still the German fallback"
                => ["http://metager.de/", "kl-GL", "de-DE", "de-DE", ""],
            "a legacy two-letter segment names the locale it stood for"
                => ["http://metager.de/at/about", "de-DE,de;q=0.9", "de-AT", "de-DE", "at"],
        ];
    }

    #[DataProvider("cases")]
    public function testTheLocaleIsResolvedFromHostPathAndHeader(
        string $url,
        ?string $acceptLanguage,
        string $locale,
        string $defaultLocale,
        string $pathLocale,
    ): void {
        $context = $this->resolve($url, $acceptLanguage);

        $this->assertSame($locale, $context->locale, "wrong locale");
        $this->assertSame($defaultLocale, $context->defaultLocale, "wrong unprefixed locale");
        $this->assertSame($pathLocale, $context->pathLocale, "wrong path segment");
    }

    /**
     * The cookie outranks the header, because it is the user saying so rather
     * than their browser guessing on their behalf — but not the path, which is
     * the link they just followed.
     */
    public function testTheStoredLocaleOutranksTheHeaderButNotThePath(): void
    {
        $stored = $this->resolve("http://metager.de/", "de-DE,de;q=0.9", ["mg_locale" => "es-ES"]);
        $this->assertSame("es-ES", $stored->locale);
        $this->assertSame("es-ES", $stored->defaultLocale, "an unprefixed URL renders the stored locale");

        $followed = $this->resolve("http://metager.de/fi-FI/about", "de-DE,de;q=0.9", ["mg_locale" => "es-ES"]);
        $this->assertSame("fi-FI", $followed->locale, "the link the user followed wins the page");
        $this->assertSame("es-ES", $followed->defaultLocale, "but it does not change what they store");
    }

    /**
     * Until `mg_locale` exists, the language is read out of `web_setting_m`,
     * because that is where every existing user's language currently is. Once
     * it exists the old cookie is a market filter again and nothing else — the
     * whole point of the change.
     */
    public function testTheOldSettingIsReadAsALanguageOnlyUntilTheNewCookieExists(): void
    {
        $migrating = $this->resolve("http://metager.org/", "en-US,en;q=0.9", ["web_setting_m" => "fr_FR"]);
        $this->assertSame("fr-FR", $migrating->locale, "an existing user keeps their language");

        $migrated = $this->resolve("http://metager.org/", "en-US,en;q=0.9", [
            "mg_locale" => "en-US",
            "web_setting_m" => "fr_FR",
        ]);
        $this->assertSame(
            "en-US",
            $migrated->locale,
            "Once the language has a cookie of its own, choosing a French market must not make the interface French."
        );
    }

    /**
     * A client that is not a browser states its locale outright, and is
     * believed over everything: it knows its own language, whereas a path
     * prefix is whatever was in the link it was handed.
     */
    public function testAClientCanStateItsLocale(): void
    {
        $request = Request::create("http://metager.de/en-US/about?lang=es");
        $request->headers->set("Accept-Language", "de-DE,de;q=0.9");
        $this->assertSame("es-ES", LocaleContext::resolve($request)->locale, "?lang= outranks the path");

        $header = Request::create("http://metager.de/about");
        $header->headers->set("Accept-Language", "de-DE,de;q=0.9");
        $header->headers->set("MG-Locale", "nl-NL");
        $this->assertSame("nl-NL", LocaleContext::resolve($header)->locale);

        $nonsense = Request::create("http://metager.org/about?lang=klingon");
        $this->assertSame("en-US", LocaleContext::resolve($nonsense)->locale, "an unusable value is ignored, not fatal");
    }

    /**
     * Translations fall back to the locale's own language. The host used to
     * supply this, which meant a missing English string on metager.de was
     * answered in German.
     */
    public function testTranslationsFallBackAlongTheLocaleNotTheHost(): void
    {
        $this->assertSame("en", $this->resolve("http://metager.de/en-US/about")->fallbackLanguage);
        $this->assertSame("ca", $this->resolve("http://metager.org/ca-ES/about")->fallbackLanguage);
    }

    /**
     * The kill switch puts the old rules back whole — it is what the rollout
     * is reversed with, so it has to be known to work rather than assumed.
     */
    public function testTheOldRulesCanBeRestoredFromConfiguration(): void
    {
        config(["metager.metager.locale.decoupled" => false]);

        $this->assertSame(
            "de-DE",
            $this->resolve("http://metager.de/", "en-US,en;q=0.9")->locale,
            "With the flag off, metager.de discounts a non-German Accept-Language again."
        );
    }

    /**
     * A URL is prefixed exactly when leaving the prefix off would resolve to
     * something else — which is what `hideDefaultLocaleInURL` means.
     */
    public function testOnlyANonDefaultLocaleIsNamedInGeneratedUrls(): void
    {
        $this->assertSame("", $this->resolve("http://metager.de/", "de-DE")->urlPrefix());
        $this->assertSame("/fi-FI", $this->resolve("http://metager.org/fi-FI/x", "en-US")->urlPrefix());
    }

    /**
     * The scraped endpoints skip the whole business: they are answered
     * thousands of times an hour and never render a translated word.
     */
    public function testHealthAndMetricsEndpointsSkipNegotiation(): void
    {
        $context = $this->resolve("http://metager.org/metrics", "de-DE,de;q=0.9");

        $this->assertSame("en-US", $context->locale, "the host default, not the negotiated one");
        $this->assertSame("", $context->pathLocale);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function paths(): array
    {
        return [
            "a page" => ["http://metager.org/en-US/about", "/about"],
            "with a query string" => ["http://metager.org/en-US/about?a=1&b=2", "/about?a=1&b=2"],
            "the site root" => ["http://metager.org/en-US", "/"],
            "the site root with a slash" => ["http://metager.org/en-US/", "/"],
            "the site root with a query" => ["http://metager.org/en-US?a=1", "/?a=1"],
            "a legacy segment" => ["http://metager.org/at/about", "/about"],
            "a path that merely starts alike" => ["http://metager.org/en-USA/about", "/en-USA/about"],
        ];
    }

    /**
     * The route table contains no locale, so the segment has to come off before
     * matching. Nothing downstream — the router, `$request->path()`,
     * `LocalizationRedirect` — ever sees it.
     */
    #[DataProvider("paths")]
    public function testTheLocaleSegmentIsRemovedFromTheRequest(string $url, string $expected): void
    {
        $request = Request::create($url);
        $stripped = LocaleContext::resolve($request)->stripLocalePrefix($request);

        $this->assertSame($expected, $stripped->getRequestUri());
    }

    /** Whatever else changes, the request must arrive intact. */
    public function testStrippingPreservesTheRestOfTheRequest(): void
    {
        $request = Request::create("http://metager.org/en-US/meta/meta.ger3?eingabe=kaffee", "POST", ["focus" => "web"]);
        $request->headers->set("Sec-Fetch-Mode", "navigate");
        $request->cookies->set("key", "the-users-key");

        $stripped = LocaleContext::resolve($request)->stripLocalePrefix($request);

        $this->assertSame("/meta/meta.ger3?eingabe=kaffee", $stripped->getRequestUri());
        $this->assertSame("POST", $stripped->method());
        $this->assertSame("kaffee", $stripped->query("eingabe"));
        $this->assertSame("web", $stripped->input("focus"));
        $this->assertSame("navigate", $stripped->header("Sec-Fetch-Mode"));
        $this->assertSame("the-users-key", $stripped->cookie("key"));
    }

    /** A request with no locale segment is handed on untouched. */
    public function testAnUnprefixedRequestIsNotRebuilt(): void
    {
        $request = Request::create("http://metager.org/about");

        $this->assertSame($request, LocaleContext::resolve($request)->stripLocalePrefix($request));
    }
}
