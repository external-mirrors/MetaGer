<?php

namespace App\Localization;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
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
 * The rules themselves changed next, and this is where. The interface locale
 * is now its own concern with its own storage — the `mg_locale` cookie — read
 * in one fixed order that every MetaGer codebase implements the same way:
 *
 *   1. an explicit statement by the client: `?lang=` or the `MG-Locale` header
 *   2. the URL path prefix
 *   3. the `mg_locale` cookie
 *   4. `Accept-Language`
 *   5. the host — `de-DE` on metager.de, `en-US` everywhere else
 *
 * The host appears only at step 5, and reaching it never produces a redirect:
 * both domains serve every locale. That is what retires the two rules this
 * class used to carry — `metager.de` discounting a non-German
 * `Accept-Language`, and the language deciding which domain you belong on —
 * along with the cross-domain settings hand-off that existed to survive them.
 *
 * `web_setting_m` is a search filter again, and only that. It is still read at
 * step 3 when no `mg_locale` exists, because that is where everyone's language
 * currently lives; `ResolveLocale` writes the new cookie the first time it sees
 * such a request, so each browser passes through that branch once.
 *
 * Reversible from the environment while the redirect rate is watched:
 * `LOCALE_DECOUPLED=false` restores the previous behaviour wholesale
 * ([resolveWithHostRules]).
 */
final class LocaleContext
{
    /** Paths answered before any locale work — scraped continuously, never rendered. */
    private const UNLOCALIZED_PATHS = ["metrics", "health-check/*", "up"];

    private function __construct(
        /** The locale this request renders in, e.g. `en-US`. */
        public readonly string $locale,
        /** The locale that needs no path prefix on this request. */
        public readonly string $defaultLocale,
        /** Translation fallback — the language of [locale], e.g. `en` for `en-US`. */
        public readonly string $fallbackLanguage,
        /** The locale segment actually present in the URL, or `""`. */
        public readonly string $pathLocale,
        /** The request URL exactly as it arrived, prefix included. */
        public readonly string $originalUrl,
    ) {
    }

    /**
     * The one region we treat as a language's home, for the many inputs that
     * name a language without one — an `Accept-Language` of `de`, a client
     * sending `?lang=es`.
     *
     * The one copy: `SettingsController::LANG_TO_LOCALE` is now this constant,
     * and the app's `HOME_MARKET` (`src/search/market.ts`) is checked against
     * it by the shared `tests/Fixtures/locale-cases.json`. Kept here rather
     * than in config because it is a property of the translations we ship,
     * not something an operator sets. `docs/locale-contract.md` §4.
     */
    public const HOME_REGION = [
        "ca" => "ca-ES",
        "da" => "da-DK",
        "de" => "de-DE",
        "en" => "en-US",
        "es" => "es-ES",
        "fi" => "fi-FI",
        "fr" => "fr-FR",
        "it" => "it-IT",
        "nl" => "nl-NL",
        "pl" => "pl-PL",
        "pt" => "pt-PT",
        "sv" => "sv-SE",
    ];

    /**
     * The locale for a request: stated, then path, then cookie, then
     * `Accept-Language`, then the host.
     */
    public static function resolve(Request $request): self
    {
        $host = $request->getHost();
        $host_locale = $host === "metager.de" ? "de-DE" : "en-US";

        if ($request->is(self::UNLOCALIZED_PATHS)) {
            return new self($host_locale, $host_locale, self::languageOf($host_locale), "", $request->fullUrl());
        }

        $segment = (string) $request->segment(1);

        if (!self::decoupled()) {
            return self::resolveWithHostRules($request, $host, $host_locale, $segment);
        }

        $path_locale = self::isLocaleSegment($segment) ? $segment : "";

        $stated = self::statedLocale($request);

        /**
         * What an unprefixed URL renders as — which is also what
         * `LocalizationRedirect` uses to decide that a prefix is redundant.
         * Deliberately excludes the path: a link to `/fi-FI/about` must not
         * make Finnish this browser's default for everything else.
         */
        $default_locale = $stated
            ?? self::cookieLocale($request)
            ?? self::preferredLocale($request, $host_locale);

        $locale = $stated ?? ($path_locale !== "" ? $path_locale : $default_locale);

        /**
         * Translations fall back along the locale, not along the host.
         * `lang/` ships both `en-US` and `en`, so a string missing from the
         * regional catalogue is answered by its language's — which is the
         * relationship the fallback is for. The host used to supply this,
         * which meant a missing English string on metager.de rendered German.
         */
        return new self($locale, $default_locale, self::languageOf($locale), $path_locale, $request->fullUrl());
    }

    /** Whether the locale is decoupled from the domain — `LOCALE_DECOUPLED`. */
    private static function decoupled(): bool
    {
        return (bool) config("metager.metager.locale.decoupled", true);
    }

    /**
     * The pre-decoupling rules, kept whole so the rollout can be undone from
     * the environment rather than by deploying. `metager.de` discounts an
     * `Accept-Language` it does not agree with, and the host supplies both the
     * fallback locale and the translation fallback language.
     *
     * Deleted together with the config flag once the redirect rate has held.
     */
    private static function resolveWithHostRules(
        Request $request,
        string $host,
        string $host_locale,
        string $segment,
    ): self {
        $host_language = $host === "metager.de" ? "de" : "en";
        $guess = self::preferredLocale($request, $host_locale);
        $guess_trusted = $host !== "metager.de" || str_starts_with($guess, "de");
        $default_locale = $guess_trusted ? $guess : $host_locale;

        if (self::isLocaleSegment($segment)) {
            return new self($segment, $default_locale, $host_language, $segment, $request->fullUrl());
        }

        return new self($default_locale, $default_locale, $host_language, "", $request->fullUrl());
    }

    /**
     * A locale the client stated outright: `?lang=`, or the `MG-Locale`
     * header for a client that cannot put it in the URL.
     *
     * This is the entry point for everything that is not a browser — the
     * mobile app, the WebExtension — and it outranks the path because such a
     * client knows its own language, whereas a path prefix may be whatever
     * happened to be in the link it was handed.
     */
    private static function statedLocale(Request $request): ?string
    {
        foreach ([$request->query("lang"), $request->header("MG-Locale")] as $stated) {
            if (is_string($stated) && ($locale = self::normalize($stated)) !== null) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * The locale this browser has stored.
     *
     * `web_setting_m` is consulted only while `mg_locale` is missing, and only
     * because it is where every existing user's language currently lives — it
     * is a market filter that was doubling as the interface language, which is
     * the conflation this whole change exists to end. `ResolveLocale` writes
     * `mg_locale` on any request that lands here, so a browser takes this
     * branch once and then stops.
     */
    private static function cookieLocale(Request $request): ?string
    {
        // The header is how the WebExtension stores settings — it keeps them
        // itself and replays them, rather than letting us set cookies.
        $stored = $request->cookie(self::cookieName()) ?? $request->header(self::cookieName());
        if (is_string($stored) && ($locale = self::normalize($stored)) !== null) {
            return $locale;
        }

        $legacy = $request->cookie("web_setting_m") ?? $request->header("web_setting_m");

        return is_string($legacy) ? self::normalize($legacy) : null;
    }

    /** The name of the interface-locale cookie. */
    public static function cookieName(): string
    {
        return (string) config("metager.metager.locale.cookie", "mg_locale");
    }

    /**
     * `$value` as a locale we actually serve, or `null`.
     *
     * Accepts both separators because the inputs disagree — `mg_locale` and
     * the URL are hyphenated BCP-47, `web_setting_m` is underscored — and a
     * bare language, which is what an `Accept-Language`-shaped `?lang=de`
     * gives us.
     */
    private static function normalize(string $value): ?string
    {
        $value = str_replace("_", "-", trim($value));
        if ($value === "" || $value === "default") {
            return null;
        }

        if (in_array($value, LaravelLocalization::getSupportedLanguagesKeys(), true)) {
            return $value;
        }

        return self::HOME_REGION[strtolower($value)] ?? null;
    }

    /** The language part of a locale — `en-US` becomes `en`. */
    private static function languageOf(string $locale): string
    {
        return explode("-", $locale)[0];
    }

    /**
     * Persists this request's locale in `mg_locale`, if the browser has no
     * such cookie yet.
     *
     * Two callers, one reason each, and both write [defaultLocale] rather than
     * [locale]: what is being recorded is what this browser should get for an
     * unprefixed URL, not the prefix of whichever link it happened to follow.
     *
     *  - `ResolveLocale`, when the language was found in `web_setting_m` — the
     *    one-time migration onto the new cookie.
     *  - `SettingsController::enableFilter()`, when the user changes the market
     *    filter, so that the value stops being ambiguous before it can be
     *    misread as a language by the branch above.
     */
    public function persistCookie(): void
    {
        Cookie::queue(Cookie::forever(
            self::cookieName(),
            $this->defaultLocale,
            "/",
            null,
            !App::environment("local"),
            false,
        ));
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

    /** `$path` without a leading locale segment. */
    public static function withoutLocaleSegment(string $path): string
    {
        if (!preg_match("~^/([^/?#]+)~", $path, $matches)) {
            return $path;
        }
        $segment = $matches[1];
        if (!self::isLocaleSegment($segment)) {
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

        /**
         * Bare language tags the header is far more likely to carry than a
         * regional one, mapped to the region we treat as their home, plus the
         * one region code browsers get wrong.
         *
         * Offered for every language we ship rather than for German, English
         * and Spanish alone: a browser asking for `fr` used to match nothing
         * here and fall through to the host default, which on metager.de meant
         * German. Honouring the header on every host is only half the change
         * if the header can only be honoured in three languages.
         */
        $two_letter_locales = ["en_UK" => "en_GB"];
        foreach (self::HOME_REGION as $language => $locale) {
            $two_letter_locales[$language] = str_replace("-", "_", $locale);
        }
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
