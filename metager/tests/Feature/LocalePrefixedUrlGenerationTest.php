<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The route table names no locale, and every generated URL carries one anyway.
 *
 * Those two halves are the whole of the arrangement. `ResolveLocale` strips the
 * locale segment before route matching; `AppServiceProvider`'s
 * `URL::formatPathUsing` hook puts it back on the way out. Between them the
 * router sees one constant table — which is what makes `route:cache` possible —
 * and no call site has to remember to localize anything.
 *
 * What this replaces is worth stating, because the shape of the old bug is the
 * reason to keep testing for it: the locale used to be a *route group prefix*,
 * evaluated per request while routes were being registered. Files loaded inside
 * a prefixed group got the prefix; files loaded anywhere else did not. When
 * `bootstrap/app.php` named `routes/web.php` a second time in
 * `->withRouting(web: …)`, that unprefixed copy won the name lookup, and
 * `route('suggest')` came out as `/suggest` on an English page while
 * `route('settings')` — one line further down the same Blade file, but declared
 * in `routes/cookie.php` — correctly came out as `/en-US/meta/settings`. The
 * visible symptom was the start page's search box refusing to submit, because
 * the unprefixed suggest URL was answered with a cross-origin redirect that
 * `fetch()` cannot follow under our own `connect-src 'self'`.
 *
 * These are plain feature tests. They could not be, before: routes were
 * registered once per boot from the console kernel's synthetic request, so
 * `/es-ES/about` did not exist in-process and the coverage had to be a Dusk
 * test driving a real FPM.
 */
class LocalePrefixedUrlGenerationTest extends TestCase
{
    /** The origin this suite's requests are answered on, per `config('app.url')`. */
    private function origin(): string
    {
        return rtrim(config("app.url"), "/");
    }


    /**
     * Duplicates are invisible through `getRoutesByName()` — that lookup is
     * keyed by name, so a second registration simply replaces the first and
     * everything still resolves, just to the wrong URL. Counting the raw
     * collection is what actually sees it.
     */
    public function testNoRouteNameIsRegisteredMoreThanOnce(): void
    {
        $names = [];
        foreach (Route::getRoutes() as $route) {
            if (($name = $route->getName()) !== null) {
                $names[] = $name;
            }
        }

        $duplicates = array_keys(array_filter(
            array_count_values($names),
            fn(int $count): bool => $count > 1
        ));
        sort($duplicates);

        $this->assertSame(
            [],
            $duplicates,
            "These route names are registered more than once, so which URL route() produces "
                . "depends on registration order. The usual cause is a route file named both in "
                . "bootstrap/app.php's withRouting() and in RouteServiceProvider::map()."
        );
    }

    /**
     * No registered URI may begin with a locale.
     *
     * This is the property `route:cache` needs: one table, the same for every
     * request. A group prefix that comes back to life anywhere would show up
     * here rather than as a cache file that is silently right for one locale
     * and wrong for the other eighteen.
     */
    public function testTheRouteTableContainsNoLocaleSegment(): void
    {
        $localized = [];
        foreach (Route::getRoutes() as $route) {
            $first = explode("/", $route->uri())[0];
            if (preg_match("/^[a-z]{2}-[A-Z]{2}$/", $first)) {
                $localized[] = $route->uri();
            }
        }

        $this->assertSame(
            [],
            $localized,
            "The route table depends on the request again, so it cannot be cached."
        );
    }

    /**
     * The outcome that matters, across the two route files that used to
     * disagree: `lang-selector` lives in `routes/web.php`, `settings` in
     * `routes/cookie.php`.
     */
    public function testAPrefixedPageGeneratesPrefixedUrls(): void
    {
        $page = $this->get("/es-ES/about", ["Sec-Fetch-Mode" => "navigate"]);

        $page->assertOk();
        $page->assertSee('href="' . $this->origin() . '/es-ES/lang"', false);
        $page->assertSee($this->origin() . "/es-ES/meta/settings", false);
    }

    /**
     * Assets are the deliberate exception. `UrlGenerator::asset()` builds from
     * the root without calling `format()`, so the hook never sees it — which is
     * what we want, because `/build/…` is served by nginx and exists at exactly
     * one path.
     */
    public function testAssetUrlsAreNeverPrefixed(): void
    {
        $page = $this->get("/es-ES/about", ["Sec-Fetch-Mode" => "navigate"]);

        $page->assertOk();
        $page->assertDontSee("/es-ES/build/", false);
        $page->assertSee('href="/build/', false);
    }

    /**
     * A link back to the current page has to keep the prefix too. It cannot
     * come from `url()->full()` any more — that reads the request the router
     * was handed, which is the one with the locale already removed.
     */
    public function testTheReturnUrlOnThisPageKeepsItsPrefix(): void
    {
        $page = $this->get("/es-ES/about", ["Sec-Fetch-Mode" => "navigate"]);

        $page->assertOk();
        $page->assertSee(urlencode($this->origin() . "/es-ES/about"), false);
    }

    /**
     * The other half of `hideDefaultLocaleInURL`: the locale a request would
     * have resolved to anyway is not named in the URL. A fix that prefixed
     * unconditionally would change every canonical URL we have.
     */
    public function testTheDefaultLocaleStaysUnprefixed(): void
    {
        $page = $this->get("/about", ["Sec-Fetch-Mode" => "navigate"]);

        $page->assertOk();
        $page->assertSee('href="' . $this->origin() . '/lang"', false);
        $page->assertDontSee("/en-US/lang", false);
    }
}
