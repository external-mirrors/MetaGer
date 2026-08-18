<?php

namespace App\Localization;

use Mcamara\LaravelLocalization\Exceptions\UnsupportedLocaleException;
use Mcamara\LaravelLocalization\LaravelLocalization;

/**
 * `getLocalizedURL()`, reimplemented as the two-line operation it actually is.
 *
 * There are around 150 call sites across the blades and controllers, so this is
 * the one place where "the same path, in a different locale" is defined. The
 * package's own version could not be kept for two reasons:
 *
 *  - It builds its answer with `$this->url->to($url)` and again with
 *    `createUrlFromUri()`, both of which now run through
 *    `AppServiceProvider`'s `URL::formatPathUsing` hook — so every localized
 *    URL would come out with the prefix applied twice.
 *  - It located the locale to replace by looping over the supported locales
 *    against a *mapping* configured per request, and got it wrong whenever the
 *    request already had a prefix: on `/en-US` the alternates came out as
 *    `hreflang="da-DK" href=".../da-DK/en-US"`, two locales in one path, on
 *    every page we serve.
 *
 * The rule here instead: take the path, drop a locale segment if it has one,
 * put the requested one on. `ResolveLocale` has already removed the prefix from
 * the incoming request, so "the current URL" is the bare path to begin with and
 * the strip is a safety net rather than the mechanism.
 *
 * This also retires the memoisation this class was originally created for. That
 * existed because `extractAttributes()` walked all 132 routes on every one of
 * the ~50 localized links on a result page; nothing below consults the router
 * at all, so there is no longer a scan to memoise.
 */
class MetaGerLocalization extends LaravelLocalization
{
    /**
     * @param string|bool|null $locale Locale to adapt to, `false` to remove the locale entirely
     * @param string|false|null $url URL to adapt; the current URL when empty
     * @param array $attributes Unused — this application has no translated routes
     * @param bool $forceDefaultLocation Name the locale even when it is the one that needs no prefix
     * @return string
     */
    public function getLocalizedURL($locale = null, $url = null, $attributes = [], $forceDefaultLocation = false)
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }

        if ($locale !== false && !$this->checkLocaleInSupportedLocales($locale)) {
            throw new UnsupportedLocaleException("Locale '" . $locale . "' is not in the list of supported locales.");
        }

        if (empty($url)) {
            // The URL as it arrived, from the resolved context rather than from
            // $this->request: this object is a singleton constructed the first
            // time anything asks for a locale, which can be before ResolveLocale
            // has swapped the container's request for the real one — and a stale
            // request here means every hreflang alternate on the page points at
            // the site root instead of at the page.
            $url = app(LocaleContext::class)->originalUrl;
        }

        $parsed = parse_url((string) $url);
        if ($parsed === false) {
            $parsed = [];
        }

        $suffix = (isset($parsed["query"]) ? "?" . $parsed["query"] : "")
            . (isset($parsed["fragment"]) ? "#" . $parsed["fragment"] : "");

        // An absolute URL keeps the host it names — several call sites move a
        // URL between metager.de and metager.org and must not have it put back.
        if (isset($parsed["host"])) {
            $root = ($parsed["scheme"] ?? "https") . "://" . $parsed["host"]
                . (isset($parsed["port"]) ? ":" . $parsed["port"] : "");
        } else {
            $root = $this->url->formatRoot($this->url->formatScheme(null));
        }

        $path = LocaleContext::withoutLocaleSegment("/" . ltrim($parsed["path"] ?? "", "/"));

        $prefix = $locale === false
            ? ""
            : app(LocaleContext::class)->prefixFor((string) $locale, (bool) $forceDefaultLocation);

        return rtrim(rtrim($root, "/") . $prefix . rtrim($path, "/"), "/") . $suffix;
    }
}
