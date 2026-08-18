<?php

namespace Tests\Feature;

use App\Localization\LocaleContext;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * What locale a request gets, and what is left of its path afterwards.
 *
 * Characterization, deliberately: these are the rules this application has
 * always applied, moved out of `Localization::setLocale()` without being
 * changed, so that moving them could be verified against something. Two of them
 * are on the list to go:
 *
 *  - `metager.de` discounts an `Accept-Language` that is not German, because we
 *    have never established whether the `en-US` traffic on that host is a real
 *    preference or a misconfigured user-agent. The plan is to honour the header
 *    on every host.
 *  - The host supplies the fallback at all. Once the locale lives in its own
 *    `mg_locale` cookie, the domain stops being an input.
 *
 * When those change, these tests are meant to fail and be rewritten — that is
 * what they are for.
 */
class LocaleResolutionTest extends TestCase
{
    private function resolve(string $url, ?string $acceptLanguage = null): LocaleContext
    {
        $request = Request::create($url);
        if ($acceptLanguage !== null) {
            $request->headers->set("Accept-Language", $acceptLanguage);
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
            "English browser on the German domain is kept German"
                => ["http://metager.de/", "en-US,en;q=0.9", "de-DE", "de-DE", ""],
            "English browser elsewhere"
                => ["http://metager.org/", "en-US,en;q=0.9", "en-US", "en-US", ""],
            "German browser elsewhere"
                => ["http://metager.org/", "de-DE,de;q=0.9", "de-DE", "de-DE", ""],
            "a language we serve but no browser named"
                => ["http://metager.org/fi-FI/about", "en-US,en;q=0.9", "fi-FI", "en-US", "fi-FI"],
            "the path outranks the header"
                => ["http://metager.org/en-US/about", "de-DE,de;q=0.9", "en-US", "de-DE", "en-US"],
            "and outranks the host exception too"
                => ["http://metager.de/es-ES/about", "en-US,en;q=0.9", "es-ES", "de-DE", "es-ES"],
            "a bare language tag maps to its home region"
                => ["http://metager.org/", "es", "es-ES", "es-ES", ""],
            "nothing recognisable falls back to the host"
                => ["http://metager.org/", "kl-GL", "en-US", "en-US", ""],
            "a legacy two-letter segment is still a locale segment"
                => ["http://metager.de/at/about", "de-DE,de;q=0.9", "de-DE", "de-DE", "at"],
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
