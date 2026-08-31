<?php

namespace Tests\Feature;

use App\Localization\MetaGerLocalization;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\LaravelLocalization;
use Tests\TestCase;

/**
 * `getLocalizedURL()` — "the same page, in another locale".
 *
 * Around 150 call sites depend on this one method, so its contract is pinned
 * here rather than inferred from whichever blade happens to be under test.
 *
 * The package's own implementation was replaced for two reasons, and both have
 * a test below. It built its answer through `url()->to()` and
 * `createUrlFromUri()`, which now apply the locale prefix themselves — so every
 * answer came out prefixed twice. And it located the locale to replace by
 * looping over the supported locales against a per-request config mapping,
 * which silently failed on a page that already had a prefix: on `/en-US` the
 * `hreflang` alternates came out as `href=".../da-DK/en-US"`, two locales in
 * one path, on every page we serve.
 */
class LocalizedUrlTest extends TestCase
{
    private function localization(): LaravelLocalization
    {
        return $this->app->make(LaravelLocalization::class);
    }

    private function origin(): string
    {
        return rtrim(config("app.url"), "/");
    }

    /**
     * The binding is the fragile part: nothing fails loudly if a package
     * upgrade re-registers the original class over it — the URLs just go back
     * to being wrong.
     */
    public function testTheContainerHandsOutOurImplementation(): void
    {
        $this->assertInstanceOf(
            MetaGerLocalization::class,
            $this->localization(),
            "AppServiceProvider's binding has been lost, so getLocalizedURL() is the package's again."
        );
    }

    public function testAPathIsReturnedUnderTheRequestedLocale(): void
    {
        $this->assertSame(
            $this->origin() . "/es-ES/about",
            $this->localization()->getLocalizedURL("es-ES", "/about"),
        );
    }

    /** `hideDefaultLocaleInURL`: the locale that needs no prefix does not get one. */
    public function testTheDefaultLocaleIsNotNamed(): void
    {
        $this->assertSame(
            $this->origin() . "/about",
            $this->localization()->getLocalizedURL("en-US", "/about"),
        );
    }

    /**
     * Except for an `hreflang` alternate, which has to name every locale
     * explicitly or two of them claim the same URL.
     */
    public function testForceDefaultLocationNamesItAnyway(): void
    {
        $this->assertSame(
            $this->origin() . "/en-US/about",
            $this->localization()->getLocalizedURL("en-US", "/about", [], true),
        );
    }

    /**
     * The `hreflang` bug in the form it actually shipped in: a URL that already
     * carries a locale must come back carrying exactly one.
     */
    public function testALocaleAlreadyInThePathIsReplacedRatherThanPrepended(): void
    {
        $this->assertSame(
            $this->origin() . "/da-DK/about",
            $this->localization()->getLocalizedURL("da-DK", $this->origin() . "/en-US/about", [], true),
        );
    }

    /**
     * A retired two-letter prefix is not a locale and is not replaced — it is
     * part of the path now, and prefixing is all that happens to it.
     *
     * The counterpart of `LocalizedRoutingTest`'s own retirement test, on the
     * generating side: nothing may quietly keep treating `/at` as German
     * Austria while the routing half has stopped.
     */
    public function testARetiredTwoLetterPrefixIsJustPath(): void
    {
        $this->assertSame(
            $this->origin() . "/de-AT/at/about",
            $this->localization()->getLocalizedURL("de-AT", $this->origin() . "/at/about"),
        );
    }

    /** `false` is the package's way of asking for the URL with no locale at all. */
    public function testFalseStripsTheLocaleEntirely(): void
    {
        $this->assertSame(
            $this->origin() . "/about",
            $this->localization()->getLocalizedURL(false, $this->origin() . "/es-ES/about"),
        );
    }

    /**
     * Several call sites move a URL between metager.de and metager.org and then
     * localize it; the host they named must survive.
     */
    public function testAnAbsoluteUrlKeepsItsOwnHost(): void
    {
        $this->assertSame(
            "https://metager.org/es-ES/about",
            $this->localization()->getLocalizedURL("es-ES", "https://metager.org/about"),
        );
    }

    /** Query strings and fragments are carried, not re-encoded or dropped. */
    public function testTheQueryAndFragmentSurvive(): void
    {
        $this->assertSame(
            $this->origin() . "/es-ES/hilfe?a=1&b=2#h-bangs",
            $this->localization()->getLocalizedURL("es-ES", "/hilfe?a=1&b=2#h-bangs"),
        );
    }

    /** The root of the site, which is the one path with nothing left after the locale. */
    public function testTheRootPathBecomesTheBareLocale(): void
    {
        $this->assertSame(
            $this->origin() . "/es-ES",
            $this->localization()->getLocalizedURL("es-ES", "/"),
        );
    }

    /**
     * This method is the one every sidebar link, the logo, and the rest of
     * the nav are built with — none of them go through `route()`/`to()`, so
     * none of them pass through `CookieCarryingUrlGenerator`. A cookie-blind
     * visitor's key has to be carried here explicitly (CookieSupport::
     * carryIntoUrl(), called at the end of getLocalizedURL()) or the entire
     * sidebar silently signs them out one click after landing on any page.
     */
    public function testTheKeyIsCarriedForACookieBlindVisitor(): void
    {
        $key = "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456";
        $this->app->instance('request', Request::create($this->origin() . "/?key=" . $key));

        $this->assertSame(
            $this->origin() . "/hilfe?key=" . $key,
            $this->localization()->getLocalizedURL(null, "/hilfe"),
        );
    }

    public function testTheKeyIsNotCarriedWhenTheCookieDidArrive(): void
    {
        $key = "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456";
        $request = Request::create($this->origin() . "/?key=" . $key);
        $request->cookies->set("key", $key);
        $this->app->instance('request', $request);

        $this->assertSame(
            $this->origin() . "/hilfe",
            $this->localization()->getLocalizedURL(null, "/hilfe"),
        );
    }

    /**
     * And the alternates a page actually emits: one locale each, no synthetic
     * `default` entry, and never the locale the page is already in.
     */
    public function testTheAlternatesOnAPrefixedPageEachNameOneLocale(): void
    {
        $page = $this->get("/es-ES/about", ["Sec-Fetch-Mode" => "navigate"]);
        $page->assertOk();

        preg_match_all(
            '/<link rel="alternate" hreflang="([^"]+)"\s+href="([^"]+)"/',
            $page->getContent(),
            $matches,
            PREG_SET_ORDER
        );

        $this->assertNotEmpty($matches, "The page emitted no hreflang alternates at all.");

        foreach ($matches as [, $locale, $href]) {
            $this->assertNotSame("default", $locale, "'default' is not a language tag.");
            $this->assertNotSame("es-ES", $locale, "A page must not list itself as an alternate.");
            $this->assertSame(
                $this->origin() . "/$locale/about",
                $href,
                "The alternate for $locale did not name exactly one locale."
            );
        }
    }
}
