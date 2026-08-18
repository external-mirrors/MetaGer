<?php

namespace App;

use App\Localization\LocaleContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use LaravelLocalization;

/**
 * The application-facing view of the request's locale.
 *
 * The decision itself lives in `App\Localization\LocaleContext` and is taken
 * once, by the `ResolveLocale` middleware, before route matching. What used to
 * be `Localization::setLocale()` — a static called from `RouteServiceProvider`
 * to produce a route group prefix — is gone; see LocaleContext for why that
 * shape could not be kept.
 */
class Localization
{
    /** The locale decided for this request, e.g. `en-US`. */
    public static function context(): LocaleContext
    {
        return app(LocaleContext::class);
    }

    /** The URL of the page being served, locale prefix included, without the query string. */
    public static function currentUrl(): string
    {
        return self::context()->currentUrl();
    }

    /** The same, with the query string — what `url()->full()` used to return. */
    public static function currentFullUrl(): string
    {
        return self::context()->originalUrl;
    }

    /**
     * Extracts the language part from our current locale
     *
     * @return string language (i.e. de,en,es,...)
     */
    public static function getLanguage()
    {
        $current_locale = LaravelLocalization::getCurrentLocale();
        if (\preg_match("/^([a-zA-Z]+)/", $current_locale, $matches)) {
            $current_locale = $matches[1];
        }
        return $current_locale;
    }

    /**
     * Extracts the region part from our current locale
     *
     * @return string region (i.e. de,us,...)
     */
    public static function getRegion()
    {
        $current_region = LaravelLocalization::getCurrentLocale();
        if (\preg_match("/([a-zA-Z]+)$/", $current_region, $matches)) {
            $current_region = $matches[1];
        }
        return $current_region;
    }

    /**
     * The locales an `hreflang` alternate should be emitted for: everything we
     * serve except the one being rendered.
     *
     * `getSupportedLocales()` also contains the synthetic `default` entry that
     * exists only to satisfy the package's constructor. Emitted verbatim it
     * produced `<link hreflang="default" href=".../default/en-US">` on every
     * page — not a language tag, and not a URL that resolves.
     *
     * @return list<string>
     */
    public static function getAlternateLocales(): array
    {
        $current = LaravelLocalization::getCurrentLocale();

        return array_values(array_filter(
            array_keys(LaravelLocalization::getSupportedLocales()),
            fn(string $locale): bool => $locale !== "default" && $locale !== $current
        ));
    }

    /**
     * Returns the supported Locales grouped by language and sorted by native name within the group
     */
    public static function getLanguageSelectorLocales()
    {
        $locales = [];

        foreach (LaravelLocalization::getSupportedLocales() as $locale => $locale_details) {
            if (\preg_match("/^([a-zA-Z]+)-/", $locale, $matches)) {
                $locales[$matches[1]][$locale] = $locale_details["native"];
            }
        }

        // Sort languages
        \ksort($locales);

        // Sort locales in the languages
        foreach ($locales as $language => &$tmp_locales) {
            ksort($tmp_locales);
        }

        return $locales;
    }

    /**
     * Whether the URL the client actually asked for carries a valid signature.
     *
     * Not `$request->hasValidSignature()`, and the difference matters:
     * `ResolveLocale` hands the router a request whose locale prefix has been
     * removed, so `$request->url()` is no longer the URL the signature was
     * computed over. `URL::signedRoute()` signs what `route()` produces, and
     * `route()` produces the prefixed URL — so the check has to be made against
     * the URL as it arrived.
     */
    public static function hasValidSignature(): bool
    {
        $original = self::context()->originalUrl;
        if ($original === "") {
            return false;
        }

        return URL::hasValidSignature(Request::create($original));
    }
}
