<?php

namespace App\Http\Middleware;

use App\Localization;
use Closure;
use Cookie;
use Faker\Provider\UserAgent;
use LaravelLocalization;
use Illuminate\Http\Request;
use URL;

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
        if ($request->routeIs('loadSettings')) {
            return $next($request);
        }
        if ($request->routeIs("lang-selector") && filter_var($request->input("switch", false), FILTER_VALIDATE_BOOL)) {
            return $next($request);
        }

        // Check for Localization in form of the old two letter country code and redirect to correct URL in that case
        // This can be removed at some point
        if (($redirect = $this->redirectTwoLetterCountryCode($request)) !== null) {
            return $redirect;
        }

        // Check if the locale present in the path is optional
        if (($redirect = $this->verifyPathLocaleNeeded($request)) !== null) {
            return $redirect;
        }

        // Check if the current domain matches the language
        // It's metager.de for everything german and metager.org for everything else
        $lang = Localization::getLanguage();
        $host = $request->getHost();
        if ($lang === "de" && $host === "metager.org") {
            $new_uri = preg_replace("/^(https?:\/\/)metager.org/", "$1metager.de", url()->full());
            $new_uri = $this->migrateSettingsLink($new_uri);
            return redirect($new_uri);
        }

        if (Cookie::has("web_setting_m")) {
            // No locale defined in the path
            // Check if the user defined a permanent language setting matching one of our supported locales
            $setting_locale = str_replace("_", "-", Cookie::get("web_setting_m"));
            $availableLocales = LaravelLocalization::getSupportedLanguagesKeys();
            $current_locale = LaravelLocalization::getCurrentLocale();
            $new_url = preg_replace("/^\/$current_locale\/?/", "/", $request->getRequestUri());

            if (in_array($setting_locale, $availableLocales)) {
                $new_url = LaravelLocalization::getLocalizedUrl($setting_locale, $new_url);
                $redirect_necessary = false;
                if ($current_locale !== $setting_locale) {
                    $redirect_necessary = true;
                }
                // Also redirect if the user is on the wrong URL for the defined setting locale
                if ($host === "metager.de" && strpos($setting_locale, "de") !== 0) {
                    $redirect_necessary = true;
                    $new_url = preg_replace("/^(https?:\/\/)metager.de/", "$1metager.org", $new_url);
                    $new_url = $this->migrateSettingsLink($new_url);
                } else if ($host === "metager.org" && strpos($setting_locale, "de") === 0) {
                    $redirect_necessary = true;
                    $new_url = preg_replace("/^(https?:\/\/)metager.org/", "$1metager.de", $new_url);
                    $new_url = $this->migrateSettingsLink($new_url);
                }
                if ($redirect_necessary) {
                    return redirect($new_url);
                }
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
            "uk" => "en-GB",
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
        if (preg_match("/^[a-z]{2}-[A-Z]{2}$/", $request->segment(1))) {
            $path_locale = $request->segment(1);
        } else {
            $path_locale = "";
        }


        $default_locale = config("app.default_locale");
        $crawler = preg_match('/bot|crawl|slurp|spider|mediapartners/i', $request->header("User-Agent"));
        if (!empty($path_locale) && $default_locale === $path_locale && !$crawler) {
            // The user landed on a URL with path locale although it's his default language
            $path = $request->getRequestUri();
            $new_path = preg_replace("/^\/$path_locale/", "", $path);
            if ($path !== $new_path) {
                return redirect($new_path, 302, ["Vary" => "Accept-Language"]);
            }
        } else if ($crawler && empty($path_locale)) {
            $path = $request->getRequestUri();
            $new_path = "/$default_locale" . $path;
            if ($path !== $new_path) {
                return redirect($new_path, 302, ["Vary" => "Accept-Language"]);
            }
        }

        return null;
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
        foreach (Cookie::get() as $key => $value) {
            $settings[$key] = $value;
        }
        if (!array_key_exists("web_setting_m", $settings)) {
            $settings["web_setting_m"] = str_replace("-", "_", LaravelLocalization::getCurrentLocale());
        }

        $settings["signature"] = hash_hmac("sha256", $settings["redirect_url"] . $settings["expires"], config("app.key"));

        $settings_restore_url = route('loadSettings', $settings, true);
        $settings_restore_url = preg_replace("/^(https?:\/\/)$old_host/", "$1$new_host", $settings_restore_url);

        return $settings_restore_url;
    }

}