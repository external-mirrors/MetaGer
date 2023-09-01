<?php

namespace App\Http\Controllers;

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
            ->with('css', [mix('css/lang-selector.css')]);
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

        // Parse the new locale from the request
        $path_locale = $request->segment(1);
        if (!preg_match("/^[a-z]{2}-[A-Z]{2}$/", $path_locale) || !in_array($path_locale, LaravelLocalization::getSupportedLanguagesKeys())) {
            $path_locale = null;
        }
        if (empty($path_locale)) {
            // Path locale might not be present if the user is switching to the default language
            // of the browser
            Cookie::queue(Cookie::forget("web_setting_m", "/", null));
            $new_locale = config("app.default_locale");
        } else {
            $secure = !app()->environment("local");
            Cookie::queue(Cookie::forever("web_setting_m", str_replace("-", "_", $path_locale), "/", null, $secure, true));
            $new_locale = $path_locale;
        }

        $url = LaravelLocalization::getLocalizedUrl($new_locale, "/lang?" . http_build_query($request->except("switch")), [], true);
        return redirect($url);
    }
}