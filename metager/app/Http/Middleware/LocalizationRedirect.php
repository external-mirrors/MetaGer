<?php

namespace App\Http\Middleware;

use App\Localization;
use App\Localization\LocaleContext;
use App\PrometheusExporter;
use App\SearchSettings;
use Closure;
use Cookie;
use LaravelLocalization;
use Illuminate\Http\Request;

class LocalizationRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Ignore healthchecks
        if ($request->is(['metrics', 'health-check/*'])) {
            return $next($request);
        }

        /**
         * Only ever redirect a *navigation*.
         *
         * Everything below this line answers with a redirect, and several of
         * those redirects cross to the other domain. A browser following that
         * on a page load is the whole point; a `fetch()` following it is not
         * possible at all - our own CSP is `connect-src 'self'`, so the request
         * fails outright, and the calling script sees a rejected promise rather
         * than an answer.
         *
         * That is not hypothetical. The start page's suggest endpoint hit
         * exactly this for any user whose browser language and chosen language
         * disagreed, and because the submit handler waited on that request, the
         * search box stopped working with nothing on screen to explain it.
         *
         * A subresource has no business being relocated to a different locale
         * anyway: whatever locale the page it belongs to resolved is the one
         * that matters, and it is already in the URL the page generated. So the
         * rule is the general one rather than a patch for the suggest route -
         * answer non-navigation requests, never redirect them.
         */
        if (!$this->isNavigation($request)) {
            return $next($request);
        }
        if ($request->routeIs('loadSettings')) {
            return $next($request);
        }
        if ($request->routeIs("lang-selector") && filter_var($request->input("switch", false), FILTER_VALIDATE_BOOL)) {
            return $next($request);
        }

        // Check for Localization in form of the old two letter country code and redirect to correct URL in that case
        // This can be removed at some point
        if (($redirect = $this->redirectTwoLetterCountryCode($request)) !== null) {
            return $this->record("legacy_path", $redirect);
        }

        // Check if the locale present in the path is optional
        if (($redirect = $this->verifyPathLocaleNeeded($request)) !== null) {
            return $this->record("prefix_correction", $redirect);
        }

        // Send the user to the domain their language belongs on, and their
        // stored language to the URL that names it. Both are no-ops once the
        // locale is decoupled from the domain.
        if (($redirect = $this->matchDomainToLanguage($request)) !== null) {
            return $this->record("domain_language", $redirect);
        }

        $host = $request->getHost();

        // Redirect from v2 onion to v3 onion
        if ($host === "b7cxf4dkdsko6ah2.onion") {
            return $this->record("onion", redirect("http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion"));
        }

        return $this->record("served", $next($request));
    }

    /**
     * The two redirects that made the domain and the language the same
     * question — retired, and kept only so `LOCALE_DECOUPLED=false` can put
     * them back while the rollout is watched.
     *
     * They were: a German page on metager.org bounces to metager.de, and a
     * `web_setting_m` naming a language bounces to that language's URL, across
     * the domain boundary if the language and the domain disagreed. Crossing
     * that boundary is what needed [migrateSettingsLink] — separate origins,
     * separate cookie jars — and carrying a user's whole settings jar through
     * a signed URL on every language switch is a lot of machinery to own for a
     * redirect that no longer has a reason to happen.
     *
     * Nothing replaces them. Both domains serve every locale, the locale is in
     * the path when it differs from this browser's default, and `mg_locale`
     * says what that default is — none of which needs the user moved.
     */
    private function matchDomainToLanguage(Request $request)
    {
        if (config("metager.metager.locale.decoupled", true)) {
            return null;
        }

        $host = $request->getHost();

        if (Localization::getLanguage() === "de" && $host === "metager.org") {
            $new_uri = preg_replace("/^(https?:\/\/)metager.org/", "$1metager.de", $this->context()->originalUrl);
            return redirect($this->migrateSettingsLink($new_uri));
        }

        $setting = Cookie::get("web_setting_m") ?? $request->header("web_setting_m");
        if ($setting === null) {
            return null;
        }

        $setting_locale = str_replace("_", "-", $setting);
        if (!in_array($setting_locale, LaravelLocalization::getSupportedLanguagesKeys(), true)) {
            return null;
        }

        // Already free of any locale segment: ResolveLocale took it off before
        // the router ever saw this request.
        $new_url = LaravelLocalization::getLocalizedUrl($setting_locale, $request->getRequestUri());
        $redirect_necessary = LaravelLocalization::getCurrentLocale() !== $setting_locale;

        if ($host === "metager.de" && !str_starts_with($setting_locale, "de")) {
            $redirect_necessary = true;
            $new_url = $this->migrateSettingsLink(
                preg_replace("/^(https?:\/\/)metager.de/", "$1metager.org", $new_url)
            );
        } elseif ($host === "metager.org" && str_starts_with($setting_locale, "de")) {
            $redirect_necessary = true;
            $new_url = $this->migrateSettingsLink(
                preg_replace("/^(https?:\/\/)metager.org/", "$1metager.de", $new_url)
            );
        }

        return $redirect_necessary ? redirect($new_url) : null;
    }

    /**
     * Whether this request is a page load, as opposed to a subresource,
     * `fetch()`/XHR or API call.
     *
     * `Sec-Fetch-Mode` is the authoritative answer and every current browser
     * sends it on every request; it is a forbidden header name, so a page
     * cannot forge it. When it is absent - an older browser, a crawler, curl,
     * or one of our own API clients - we fall back to the two signals that
     * predate it, and otherwise assume a navigation, which keeps the previous
     * behaviour for crawlers (`verifyPathLocaleNeeded()` deliberately pushes
     * them onto a locale-prefixed URL).
     */
    private function isNavigation(Request $request): bool
    {
        $mode = $request->header("Sec-Fetch-Mode");
        if (!empty($mode)) {
            return $mode === "navigate";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        return true;
    }

    /**
     * Some Localizations were set to two letter country codes in the past
     * we switched to 4 letters at some point and created this legacy redirection
     * so old URLs remain working
     *
     * 04.07.2023 Dominik
     */
    private function redirectTwoLetterCountryCode($request)
    {
        $path_locale = $this->context()->pathLocale;
        $legacy_country_codes = [
            "uk" => "en-GB",
            "ie" => "en-GB",
            "es" => "es-ES",
            "at" => "de-AT"
        ];
        if (array_key_exists($path_locale, $legacy_country_codes)) {
            // getLocalizedUrl() drops a leading locale segment of its own
            // accord, legacy two-letter ones included, so the URL as it
            // arrived is exactly the right thing to hand it.
            $new_url = LaravelLocalization::getLocalizedUrl($legacy_country_codes[$path_locale], $this->context()->originalUrl);
            return redirect($new_url);
        }
        return null;
    }

    /**
     * When the user supplies a locale in path (i.e. en-US)
     * We'll verify that the browsers preferred language is not also en-US
     * if it is the user can use a path without a locale since his configured
     * language already is the default language
     * 
     * @param Request $request
     * @return null|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function verifyPathLocaleNeeded(Request $request)
    {
        $context = $this->context();
        $path_locale = preg_match("/^[a-z]{2}-[A-Z]{2}$/", $context->pathLocale) ? $context->pathLocale : "";

        $default_locale = $context->defaultLocale;
        $crawler = preg_match('/bot|crawl|slurp|spider|mediapartners/i', $request->header("User-Agent"));

        // $request->getRequestUri() is the path with the locale already
        // removed, which is precisely the URL the first branch wants to send
        // the user to and the one the second branch wants to prefix.
        if (!empty($path_locale) && $default_locale === $path_locale && !$crawler) {
            // The user landed on a URL with path locale although it's his default language
            return redirect($request->getRequestUri(), 302, ["Vary" => "Accept-Language"]);
        } else if ($crawler && empty($path_locale)) {
            return redirect("/$default_locale" . $request->getRequestUri(), 302, ["Vary" => "Accept-Language"]);
        }

        return null;
    }

    /**
     * Count how this request's locale was answered, and hand the response
     * back unchanged.
     *
     * The point of the counter is the ratio, so the non-redirect case has to
     * be counted too — a redirect count on its own falls just as convincingly
     * when traffic drops. `domain_language` is the series that should go to
     * zero and stay there once `LOCALE_DECOUPLED` is on everywhere; it is the
     * rule this change removed, and the only one whose reappearance would mean
     * the rollout had come undone.
     */
    private function record(string $reason, $response)
    {
        PrometheusExporter::LocaleDecision($reason);

        return $response;
    }

    /** The locale decided for this request, before route matching. */
    private function context(): LocaleContext
    {
        return app(LocaleContext::class);
    }

    /**
     * Generates a URL which migrates all the current settings to the new URL
     * using load-settings and redirecting to target url afterwards
     *
     * @return string
     */
    private function migrateSettingsLink($url)
    {
        $old_host = request()->getHost();
        $new_host = parse_url($url, PHP_URL_HOST);
        if ($old_host === $new_host) {
            return $url;
        }

        // We can include all current cookies in the URL since the load-settings script will filter out the valid ones
        $settings = [
            "redirect_url" => $url,
            "expires" => "" . now()->addMinutes(5)->unix(),
        ];

        // Read out all current settings
        $settings = array_merge($settings, app(SearchSettings::class)->user_settings);

        foreach (\Request::header() as $key => $value) {
            if (is_array($value))
                $value = implode("", $value);
            $key = str_replace("-setting-", "_setting_", $key);
            $key = str_replace("-engine-", "_engine_", $key);
            $key = str_replace("-", "_", $key);
            if (preg_match("/.*_(setting|engine)_.*/", $key)) {
                $settings[$key] = $value;
            }
        }

        foreach (Cookie::get() as $key => $value) {
            if (preg_match("/.*_setting_.*/", $key)) {
                $settings[$key] = $value;
            }
        }

        if (!array_key_exists("web_setting_m", $settings)) {
            $settings["web_setting_m"] = str_replace("-", "_", LaravelLocalization::getCurrentLocale());
        }

        /**
         * The key travels too.
         *
         * Without this, switching language across the domain boundary signed
         * the user out: the loop above collects `*_setting_*` and `*_engine_*`
         * only, so a user's search settings arrived on the new domain intact
         * while the credential that pays for their searches was left behind on
         * the old one. `loadSettings()` has always had a branch for `key` -
         * `SettingsController::index()`'s own migration link sends it - so this
         * was an omission here rather than a policy.
         *
         * Read from the raw transports rather than through the key guard: this
         * is middleware, the guard has not necessarily resolved yet, and all we
         * need is the value to hand on. The resulting URL is HMAC-signed and
         * expires in five minutes (see the `signature` below).
         */
        $user_key = request()->cookie("key") ?? request()->header("key") ?? request()->query("key");
        if (is_string($user_key) && $user_key !== "") {
            $settings["key"] = $user_key;
        }

        $settings["signature"] = hash_hmac("sha256", $settings["redirect_url"] . $settings["expires"], config("app.key"));

        $settings_restore_url = route('loadSettings', $settings, true);
        $settings_restore_url = preg_replace("/^(https?:\/\/)$old_host/", "$1$new_host", $settings_restore_url);

        return $settings_restore_url;
    }

}