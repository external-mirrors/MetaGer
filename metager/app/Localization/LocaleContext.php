<?php

namespace App\Localization;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelLocalization;

/**
 * One request, one locale, decided once.
 *
 * This used to be `Localization::setLocale()`, called from
 * `RouteServiceProvider` as the *prefix of a route group* — which meant the
 * route table itself was a function of the incoming request. Three consequences
 * followed from that one fact, and all three are the reason this class exists:
 *
 *  - `php artisan route:cache` could not be used at all, because there is no
 *    single table to cache. The deploy pipeline has been paying for that on
 *    every request since.
 *  - Which URL `route()` produced depended on *which file* the route was
 *    declared in, because only the files loaded inside a prefixed group got the
 *    prefix. That is the defect that killed the start page's search box.
 *  - Nothing could be tested in-process: a feature test boots once, from a
 *    synthetic request, so `/en-US/about` simply did not exist as a route.
 *
 * Now the locale is resolved by `ResolveLocale` before route matching, the
 * prefix is stripped from the request, and it is put back on the way out by the
 * `URL::formatPathUsing` hook in `AppServiceProvider`. The route table is a
 * constant, and every generated URL carries the prefix by construction rather
 * than by remembering to ask for it.
 *
 * The resolution *rules* below are deliberately the ones this application has
 * always used, host exceptions and all — moving them and changing them in one
 * step would leave nothing to compare against. Changing them (`mg_locale`,
 * honouring `Accept-Language` on every host, no cross-domain redirects) is the
 * next step, and it happens here, in this one method.
 */
final class LocaleContext
{
    /**
     * Locales we handed out as two-letter path segments before switching to
     * BCP-47 in July 2023. `LocalizationRedirect` bounces them to the canonical
     * four-letter form; they are listed here so that a URL carrying one is
     * still recognised as *having* a locale segment, and the segment is
     * stripped rather than being taken for the first path component.
     */
    public const LEGACY_PATH_LOCALES = [
        "uk" => "en-GB",
        "ie" => "en-IE",
        "es" => "es-ES",
        "at" => "de-AT",
    ];

    /** Paths answered before any locale work — scraped continuously, never rendered. */
    private const UNLOCALIZED_PATHS = ["metrics", "health-check/*", "up"];

    private function __construct(
        /** The locale this request renders in, e.g. `en-US`. */
        public readonly string $locale,
        /** The locale that needs no path prefix on this request. */
        public readonly string $defaultLocale,
        /** Translation fallback — the language of the *host*, not of [locale]. */
        public readonly string $fallbackLanguage,
        /** The locale segment actually present in the URL, or `""`. */
        public readonly string $pathLocale,
        /** The request URL exactly as it arrived, prefix included. */
        public readonly string $originalUrl,
    ) {
    }

    /**
     * The locale for a request, by the order: URL path segment, then the
     * `Accept-Language` guess, then the host's own default.
     */
    public static function resolve(Request $request): self
    {
        $host = $request->getHost();
        $host_locale = $host === "metager.de" ? "de-DE" : "en-US";
        $host_language = $host === "metager.de" ? "de" : "en";

        if ($request->is(self::UNLOCALIZED_PATHS)) {
            return new self($host_locale, $host_locale, $host_language, "", $request->fullUrl());
        }

        $segment = (string) $request->segment(1);
        $guess = self::preferredLocale($request, $host_locale);

        /**
         * There is a lot of traffic on metager.de from browsers announcing
         * `en-US`, and we do not know whether that is a misconfigured
         * user-agent or a genuine language preference, so on that one host the
         * guess is only trusted when it agrees with the host anyway. This is
         * the rule the next step removes.
         */
        $guess_trusted = $host !== "metager.de" || str_starts_with($guess, "de");

        $default_locale = $guess_trusted ? $guess : $host_locale;

        if (self::isLocaleSegment($segment)) {
            return new self($segment, $default_locale, $host_language, $segment, $request->fullUrl());
        }

        if (array_key_exists($segment, self::LEGACY_PATH_LOCALES)) {
            $locale = $guess_trusted ? $guess : self::LEGACY_PATH_LOCALES[$segment];
            return new self($locale, $default_locale, $host_language, $segment, $request->fullUrl());
        }

        return new self($default_locale, $default_locale, $host_language, "", $request->fullUrl());
    }

    /**
     * This request's URL without its query string, locale prefix included.
     *
     * `url()->full()` and `Request::url()` cannot answer this any more: they
     * read the request the router was given, and `ResolveLocale` took the
     * prefix off that one. Anything building a *link back to this page* — the
     * settings screen's return URL, an Atom feed's self link — wants the URL
     * the user is actually on.
     */
    public function currentUrl(): string
    {
        return strtok($this->originalUrl, "?") ?: $this->originalUrl;
    }

    /** A context for anything running without a real request — console, queue. */
    public static function neutral(): self
    {
        return new self("en-US", "en-US", "en", "", "");
    }

    /** Installs this locale into the framework: translations, Carbon, the package. */
    public function apply(): void
    {
        config(["app.locale" => $this->locale, "app.default_locale" => $this->defaultLocale]);
        App::setLocale($this->locale);
        App::setFallbackLocale($this->fallbackLanguage);
        LaravelLocalization::setLocale($this->locale);
    }

    /**
     * What every generated path is prefixed with — `""` when this request's
     * locale is the one the URL does not need to name.
     */
    public function urlPrefix(): string
    {
        return $this->prefixFor($this->locale);
    }

    /**
     * The prefix a URL in `$locale` carries.
     *
     * `$force` is what an `hreflang` alternate needs: those must name every
     * locale explicitly, the default one included, or two locales end up
     * claiming the same URL.
     */
    public function prefixFor(string $locale, bool $force = false): string
    {
        if ($locale === "" || $locale === "default") {
            return "";
        }
        if (!$force && $locale === $this->defaultLocale) {
            return "";
        }
        return "/" . $locale;
    }

    /**
     * The same request with the locale segment taken off the front, so that
     * route matching sees a URL the static route table can actually contain.
     *
     * Built by replacing `REQUEST_URI` and re-deriving: Symfony caches
     * `requestUri`, `pathInfo`, `baseUrl` and friends, and `duplicate()` is the
     * one supported way to reset all of them at once. It rebuilds the header
     * bag from the server array while it is at it, which would drop any header
     * set programmatically rather than received, so the original bag is put
     * back afterwards.
     */
    public function stripLocalePrefix(Request $request): Request
    {
        if ($this->pathLocale === "") {
            return $request;
        }

        $uri = $request->getRequestUri();
        $stripped = preg_replace(
            "~^/" . preg_quote($this->pathLocale, "~") . "(?=[/?#]|$)~",
            "",
            $uri,
            1
        );
        if ($stripped === null || $stripped === $uri) {
            return $request;
        }
        if ($stripped === "" || !str_starts_with($stripped, "/")) {
            $stripped = "/" . $stripped;
        }

        $server = $request->server->all();
        $server["REQUEST_URI"] = $stripped;

        $duplicate = $request->duplicate(null, null, null, null, null, $server);
        $duplicate->headers->replace($request->headers->all());

        return $duplicate;
    }

    /** Whether `$segment` names a locale we serve. */
    public static function isLocaleSegment(string $segment): bool
    {
        if ($segment === "" || $segment === "default") {
            return false;
        }
        return preg_match("/^[a-z]{2}-[A-Z]{2}$/", $segment) === 1
            || in_array($segment, LaravelLocalization::getSupportedLanguagesKeys(), true);
    }

    /** `$path` without a leading locale segment, whether current-style or legacy. */
    public static function withoutLocaleSegment(string $path): string
    {
        if (!preg_match("~^/([^/?#]+)~", $path, $matches)) {
            return $path;
        }
        $segment = $matches[1];
        if (!self::isLocaleSegment($segment) && !array_key_exists($segment, self::LEGACY_PATH_LOCALES)) {
            return $path;
        }
        $stripped = substr($path, strlen($matches[0]));
        return $stripped === "" ? "/" : $stripped;
    }

    /**
     * The best match between what the browser asked for and what we serve.
     *
     * `$default` is put at the front of the candidate list so that it wins any
     * tie — `getPreferredLanguage()` falls back to the first entry when the
     * header names nothing we have.
     */
    private static function preferredLocale(Request $request, string $default): string
    {
        $default = str_replace("-", "_", $default);

        $regional_locales = [];
        foreach (LaravelLocalization::getSupportedLocales() as $locale_data) {
            $regional_locales[] = $locale_data["regional"];
        }

        // Bare language tags the header is far more likely to carry than a
        // regional one, mapped to the region we treat as their home.
        $two_letter_locales = [
            "de" => "de_DE",
            "en" => "en_US",
            "es" => "es_ES",
            "en_UK" => "en_GB",
        ];
        $regional_locales = array_merge(array_keys($two_letter_locales), $regional_locales);

        $regional_locales = array_values(array_diff($regional_locales, [$default]));
        array_unshift($regional_locales, $default);

        $preferred = $request->getPreferredLanguage($regional_locales);

        if (array_key_exists($preferred, $two_letter_locales)) {
            $preferred = $two_letter_locales[$preferred];
        }

        return str_replace("_", "-", (string) $preferred);
    }
}
