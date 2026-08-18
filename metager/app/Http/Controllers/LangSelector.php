<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Vite;
use App\Localization\LocaleContext;
use Cookie;
use Illuminate\Http\Request;
use LaravelLocalization;
use URL;

class LangSelector extends Controller
{
    public function index(Request $request)
    {
        // Check if a previous URL is given that we can offer a back button for
        $previous = request()->input("previous_url", URL::previous());

        $allowed_hosts = [
            "metager.de",
            "metager.org"
        ];

        $components = parse_url($previous);
        $previous_url = null; // URL for the back button
        if (is_array($components) && array_key_exists("host", $components)) {
            $host = $components["host"];
            $current_host = request()->getHost();
            $path = isset($components["path"]) ? $components["path"] : "/";
            $path .= isset($components["query"]) ? "?" . $components["query"] : "";
            $path = preg_replace("/^\/[a-z]{2}-[A-Z]{2}/", "", $path);
            if (empty($path)) {
                $path = "/";
            }
            if (($host === $current_host || in_array($current_host, $allowed_hosts)) && preg_match("/^http(s)?:\/\//", $previous)) { // only if the host of that URL matches the current host
                $previous_url = LaravelLocalization::getLocalizedUrl(null, $path);
            }
        }

        if ($redirect = $this->checkUserSwitchingLanguage($request)) {
            return $redirect;
        }


        return view('lang-selector')
            ->with("previous_url", $previous_url)
            ->with("title", trans("titles.lang-selector"))
            ->with("js", [Vite::asset('resources/js/lang.js')])
            ->with('css', [Vite::asset('resources/less/metager/pages/lang-selector.less')]);
    }

    /**
     * Checks if the user is switching language with this request
     * Will update a language setting cookie to persist the setting in the browser
     *
     * @return null|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     **/
    private function checkUserSwitchingLanguage(Request $request)
    {
        // User is not switching the language
        if (!filter_var($request->input("switch", false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        // The locale the user picked, which reached us as the path prefix of
        // /{locale}/lang?switch=1. Read from the resolved context rather than
        // from segment(1): ResolveLocale strips the segment before routing, so
        // by now the path is just /lang.
        $path_locale = app(LocaleContext::class)->pathLocale;
        if (!preg_match("/^[a-z]{2}-[A-Z]{2}$/", $path_locale) || !in_array($path_locale, LaravelLocalization::getSupportedLanguagesKeys())) {
            $path_locale = null;
        }
        /**
         * The language selector writes the language cookie, and only that.
         *
         * It used to write `web_setting_m`, which is the web fokus's *market*
         * filter — so choosing to read MetaGer in Spanish also silently
         * narrowed every search to Spain, and choosing a market silently
         * changed the interface. The two are separate settings again: the
         * market still follows the interface locale by default, because
         * `SearchSettings::loadParameterFilter()` derives that filter's default
         * from the current locale, but it does so per request and the user can
         * override it without their language moving.
         */
        $secure = !app()->environment("local");
        $locale_cookie = LocaleContext::cookieName();
        if (empty($path_locale)) {
            // Path locale might not be present if the user is switching to the default language
            // of the browser
            Cookie::queue(Cookie::forget($locale_cookie, "/", null));
            $new_locale = config("app.default_locale");
        } else {
            Cookie::queue(Cookie::forever($locale_cookie, $path_locale, "/", null, $secure, false));
            $new_locale = $path_locale;
        }

        $url = LaravelLocalization::getLocalizedUrl($new_locale, "/lang?" . http_build_query($request->except("switch")), [], true);
        return redirect($url);
    }
}