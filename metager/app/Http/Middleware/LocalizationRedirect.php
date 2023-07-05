<?php

namespace App\Http\Middleware;

use App\Localization;
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

        // Check for Localization in form of the old two letter country code and redirect to correct URL in that case
        // This can be removed at some point
        if (($redirect = $this->redirectTwoLetterCountryCode($request)) !== null) {
            return $redirect;
        }

        // Check if the current domain matches the language
        // It's metager.de for everything german and metager.org for everything else
        $lang = Localization::getLanguage();
        $host = $request->getHost();
        if ($lang === "de" && $host === "metager.org") {
            $new_uri = "https://metager.de" . request()->getRequestUri();
            return redirect($new_uri);
        }

        if (Cookie::has("web_setting_m") && !$request->routeIs("lang-selector")) {
            // No locale defined in the path
            // Check if the user defined a permanent language setting matching one of our supported locales
            $setting_locale = str_replace("_", "-", Cookie::get("web_setting_m"));
            $availableLocales = LaravelLocalization::getSupportedLanguagesKeys();

            if (config("app.default_locale", $setting_locale) !== $setting_locale && in_array($setting_locale, $availableLocales)) {
                $new_url = LaravelLocalization::getLocalizedUrl($setting_locale, url()->full());
                if ($host === "metager.de" && $lang === "en") {
                    $new_url = str_replace("https://metager.de", "https://metager.org", $new_url);
                }
                return redirect($new_url);
            }
        }

        // Check if the locale present in the path is optional
        if (preg_match("/^[a-z]{2}-[A-Z]{2}$/", $request->segment(1))) {
            if (($redirect = $this->verifyPathLocaleNeeded($request)) !== null) {
                return $redirect;
            }
        }

        // Redirect from v2 onion to v3 onion
        if ($host === "b7cxf4dkdsko6ah2.onion") {
            return redirect("http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion");
        }

        return $next($request);
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
        $path_locale = $request->segment(1);
        $legacy_country_codes = [
            "uk" => "en-UK",
            "ie" => "en-IE",
            "es" => "es-ES",
            "at" => "de-AT"
        ];
        if (array_key_exists($path_locale, $legacy_country_codes)) {
            $old_url = str_replace("/" . $path_locale, "", url()->full());
            $new_url = LaravelLocalization::getLocalizedUrl($legacy_country_codes[$path_locale], $old_url);
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
        $path_locale = $request->segment(1); // We already verified that this is indeed a locale within the path

        $preferred_locale = Localization::GET_PREFERRED_LOCALE();

        if ($preferred_locale === $path_locale && in_array(str_replace("_", "-", Cookie::get("web_setting_m", "")), ["", $preferred_locale])) {
            return redirect(LaravelLocalization::getNonLocalizedURL(url()->full()));
        }

        return null;
    }

}