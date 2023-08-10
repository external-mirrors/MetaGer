<?php

namespace App\Http\Controllers;

use App\Localization;
use \App\MetaGer;
use App\Models\Authorization\Authorization;
use App\Models\Configuration\Searchengines;
use App\Models\DisabledReason;
use App\SearchSettings;
use Cookie;
use \Illuminate\Http\Request;
use LaravelLocalization;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $settings = app(SearchSettings::class);
        $sumas = app(Searchengines::class)->getSearchEnginesForFokus();
        $fokus = $settings->fokus;
        $fokusName = trans('index.foki.' . $fokus);

        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));


        # Parse the Parameter Filter
        $filters = $settings->parameterFilter;

        $filteredSumas = false;
        foreach ($langFile->filter->{"parameter-filter"} as $name => $filter) {
            $values = $filter->values;
            foreach ($sumas as $name => $suma) {
                if ($suma->configuration->disabled && $suma->configuration->disabledReason === DisabledReason::INCOMPATIBLE_FILTER) {
                    $filteredSumas = true;
                }
            }
        }

        $authorization = app(Authorization::class);
        $url = $request->input('url', '');

        // Check if any setting is active
        $cookies = Cookie::get();
        $settingActive = false;
        foreach ($cookies as $key => $value) {
            if (stripos($key, $fokus . "_engine_") === 0 || stripos($key, $fokus . "_setting_") === 0 || strpos($key, $fokus . '_blpage') === 0 || $key === 'dark_mode' || $key === 'new_tab' || $key === 'zitate' || $key === 'self_advertisements' || $key === 'suggestions') {
                $settingActive = true;
            }
        }

        # Reading cookies for black list entries
        $blacklist = [];
        foreach ($cookies as $key => $value) {
            if (preg_match('/_blpage[0-9]+$/', $key) === 1 && stripos($key, $fokus) !== false) {
                $blacklist[] = $value;
            } elseif (preg_match('/_blpage$/', $key) === 1 && stripos($key, $fokus) !== false) {
                $blacklist = array_merge($blacklist, explode(",", $value));
            }
        }

        $blacklist = array_unique($blacklist);
        sort($blacklist);

        # Generating link with set cookies
        $cookieLink = route('loadSettings', $cookies);

        return view('settings.index')
            ->with('title', trans('titles.settings', ['fokus' => $fokusName]))
            ->with('fokus', $settings->fokus)
            ->with('fokusName', $fokusName)
            ->with('authorization', $authorization)
            ->with('filteredSumas', $filteredSumas)
            ->with('disabledReasons', app(Searchengines::class)->disabledReasons)
            ->with('sumas', $sumas)
            ->with('searchCost', app(Searchengines::class)->getSearchCost())
            ->with('filter', $filters)
            ->with('settingActive', $settingActive)
            ->with('url', $url)
            ->with('blacklist', $blacklist)
            ->with('cookieLink', $cookieLink)
            ->with('js', [mix('js/scriptSettings.js')]);
    }

    private function getSumas($fokus)
    {
        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        if (empty($langFile->foki->{$fokus})) {
            // Fokus does not exist in this suma file
            return [];
        }

        $sumasFoki = $langFile->foki->{$fokus}->sumas;

        $sumas = [];
        $locale = LaravelLocalization::getCurrentLocaleRegional();
        $lang = Localization::getLanguage();
        foreach ($sumasFoki as $suma) {
            if (
                (!empty($langFile->sumas->{$suma}->disabled) && $langFile->sumas->{$suma}->disabled) ||
                (!empty($langFile->sumas->{$suma}->{"auto-disabled"}) && $langFile->sumas->{$suma}->{"auto-disabled"}) ||
                    ## Lang support is not defined
                (!\property_exists($langFile->sumas->{$suma}, "lang") || !\property_exists($langFile->sumas->{$suma}->lang, "languages") || !\property_exists($langFile->sumas->{$suma}->lang, "regions")) ||
                    ## Current Locale/Lang is not supported by this engine
                (!\property_exists($langFile->sumas->{$suma}->lang->languages, $lang) && !\property_exists($langFile->sumas->{$suma}->lang->regions, $locale))
            ) {
                continue;
            }
            $sumas[$suma]["display-name"] = $langFile->sumas->{$suma}->infos->display_name;
            $sumas[$suma]["filtered"] = false;
            if (Cookie::get($fokus . "_engine_" . $suma) === "off") {
                $sumas[$suma]["enabled"] = false;
            } else {
                $sumas[$suma]["enabled"] = true;
            }
        }

        foreach ($langFile->filter->{"parameter-filter"} as $name => $filter) {
            $values = $filter->values;
            $cookie = Cookie::get($fokus . "_setting_" . $filter->{"get-parameter"});
            foreach ($sumas as $suma => $sumaInfo) {
                if ($cookie !== null && (empty($filter->sumas->{$suma}) || (!empty($filter->sumas->{$suma}) && empty($filter->sumas->{$suma}->values->$cookie)))) {
                    $sumas[$suma]["filtered"] = true;
                }
            }
        }
        return $sumas;
    }

    public function disableSearchEngine(Request $request)
    {
        $sumaName = $request->input('suma', '');
        $url = $request->input('url', '');

        if (empty($sumaName)) {
            abort(404);
        }

        $settings = app(SearchSettings::class);
        $engines = app(Searchengines::class)->getSearchEnginesForFokus();
        $secure = app()->environment("local") ? false : true;
        if (!$engines[$sumaName]->configuration->disabled) {
            if ($engines[$sumaName]->configuration->disabledByDefault) {
                Cookie::queue(Cookie::forget($settings->fokus . "_engine_" . $sumaName, "/"));
            } else {
                Cookie::queue(Cookie::forever($settings->fokus . "_engine_" . $sumaName, "off", "/", null, $secure, true));
            }
        }

        return redirect(route('settings', ["focus" => $settings->fokus, "url" => $url]) . "#engines");
    }

    public function enableSearchEngine(Request $request)
    {
        $sumaName = $request->input('suma', '');
        $url = $request->input('url', '');

        if (empty($sumaName)) {
            abort(404);
        }

        $settings = app(SearchSettings::class);
        $engines = app(Searchengines::class)->getSearchEnginesForFokus();
        $secure = app()->environment("local") ? false : true;
        if ($engines[$sumaName]->configuration->disabled) {
            if ($engines[$sumaName]->configuration->disabledByDefault) {
                Cookie::queue(Cookie::forever($settings->fokus . "_engine_" . $sumaName, "on", "/", null, $secure, true));
            } else {
                Cookie::queue(Cookie::forget($settings->fokus . "_engine_" . $sumaName, "/"));
            }
        }

        return redirect(route('settings', ["focus" => $settings->fokus, "url" => $url]) . "#engines");
    }

    public function enableFilter(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        if (empty($fokus)) {
            abort(404);
        }

        $newFilters = $request->except(["focus", "url"]);

        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        $settings = app(SearchSettings::class);
        app(Searchengines::class); // Needs to be loaded for parameterfilters to be populated

        foreach ($newFilters as $key => $value) {
            if (!empty($value)) {
                // Check if the new value is the default value for this filter
                foreach ($settings->parameterFilter as $name => $filter) {
                    if ($filter->{"get-parameter"} === $key && $filter->{"default-value"} === $value) {
                        $value = null;
                    }
                }
            }
            if (empty($value)) {
                $path = \Request::path();
                $cookiePath = "/";
                Cookie::queue(Cookie::forget($fokus . "_setting_" . $key, "/"));
            } else {
                # Check if this filter and its value exists:
                foreach ($langFile->filter->{"parameter-filter"} as $name => $filter) {
                    if ($key === $filter->{"get-parameter"} && !empty($filter->values->$value)) {
                        $path = \Request::path();
                        $cookiePath = "/";
                        $secure = app()->environment("local") ? false : true;
                        Cookie::queue(Cookie::forever($fokus . "_setting_" . $key, $value, "/", null, $secure, true));
                        break;
                    }
                }
            }
        }

        return redirect(route('settings', ["focus" => $fokus, "url" => $url]) . "#filter");
    }

    /**
     * Will enable the use of an external search provider. Currently
     * supported for the imagesearch as we do not currently have
     * access to a viable and free alternative
     */
    public function enableExternalSearchProvider(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        $secure = app()->environment("local") ? false : true;

        $external_setting = $request->input('bilder_setting_external', '');
        if (!empty($external_setting) && in_array($external_setting, ["google", "bing", "metager"])) {
            if ($external_setting === "metager") {
                Cookie::queue(Cookie::forget("bilder_setting_external", "/"));
            } else {
                Cookie::queue(Cookie::forever("bilder_setting_external", $external_setting, "/", null, $secure));
            }
        }
        return redirect(route('settings', ["focus" => $fokus, "url" => $url]) . "#external-search-service");
    }

    public function enableSetting(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        $secure = app()->environment("local") ? false : true;
        // Currently only the setting for quotes is supported

        $suggestions = $request->input('sg', '');
        if (!empty($suggestions)) {
            if ($suggestions === "off") {
                Cookie::queue(Cookie::forever('suggestions', 'off', '/', null, $secure, true));
            } elseif ($suggestions === "on") {
                Cookie::queue(Cookie::forget("suggestions", "/"));
            }
        }

        $self_advertisements = $request->input('self_advertisements', '');
        if (!empty($self_advertisements)) {
            if ($self_advertisements === "off") {
                Cookie::queue(Cookie::forever('self_advertisements', 'off', '/', null, $secure, true));
            } elseif ($self_advertisements === "on") {
                Cookie::queue(Cookie::forget("self_advertisements", "/"));
            }
        }

        $quotes = $request->input('zitate', '');
        if (!empty($quotes)) {
            if ($quotes === "off") {
                Cookie::queue(Cookie::forever('zitate', 'off', '/', null, $secure, true));
            } elseif ($quotes === "on") {
                Cookie::queue('zitate', '', 5256000, '/', null, $secure, true);
            }
        }

        $darkmode = $request->input('dm');
        if (!empty($darkmode)) {
            if ($darkmode === "off") {
                Cookie::queue(Cookie::forever('dark_mode', '1', '/', null, $secure, true));
            } elseif ($darkmode === "on") {
                Cookie::queue(Cookie::forever('dark_mode', '2', '/', null, $secure, true));
            } elseif ($darkmode === "system") {
                Cookie::queue(Cookie::forget('dark_mode', '/'));
            }
        }

        $newTab = $request->input('nt');
        if (!empty($newTab)) {
            if ($newTab === "off") {
                Cookie::queue(Cookie::forget('new_tab', '/'));
            } elseif ($newTab === "on") {
                Cookie::queue(Cookie::forever('new_tab', 'on', '/', null, $secure, true));
            }
        }

        return redirect(route('settings', ["focus" => $fokus, "url" => $url]) . "#more-settings");
    }

    public function deleteSettings(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        if (empty($fokus)) {
            abort(404);
        }

        $cookies = Cookie::get();
        foreach ($cookies as $key => $value) {
            if (stripos($key, $fokus . "_engine_") === 0 || stripos($key, $fokus . "_setting_") === 0) {
                Cookie::queue(Cookie::forget($key, "/"));
            }
            $global_settings = [
                "dark_mode",
                "new_tab",
                "zitate",
                "self_advertisements",
                "suggestions"
            ];
            if (in_array($key, $global_settings)) {
                Cookie::queue(Cookie::forget($key, "/"));
            }
        }
        $this->clearBlacklist($request);

        return redirect(route('settings', ["focus" => $fokus, "url" => $url]));
    }

    public function allSettingsIndex(Request $request)
    {
        $sumaFile = MetaGer::getLanguageFile();
        $sumaFile = json_decode(file_get_contents($sumaFile));

        return view('settings.allSettings')
            ->with('title', trans('titles.allSettings'))
            ->with('url', $request->input('url', ''))
            ->with('sumaFile', $sumaFile);
    }

    public function removeOneSetting(Request $request)
    {
        $key = $request->input('key', '');
        $path = \Request::path();
        $cookiePath = "/";
        if ($key === 'dark_mode') {
            Cookie::queue(Cookie::forget($key, "/"));
        } elseif ($key === 'new_tab') {
            Cookie::queue(Cookie::forget($key, "/"));
        } elseif ($key === 'key') {
            Cookie::queue(Cookie::forget($key, "/"));
        } elseif ($key === 'zitate') {
            Cookie::queue(Cookie::forget($key, "/"));
        } else {
            Cookie::queue(Cookie::forget($key, "/"));
        }
        return redirect($request->input('url', 'https://metager.de'));
    }

    public function removeAllSettings(Request $request)
    {
        foreach (Cookie::get() as $key => $value) {
            if ($key === 'dark_mode') {
                Cookie::queue(Cookie::forget($key, "/"));
            } elseif ($key === 'new_tab') {
                Cookie::queue(Cookie::forget($key, "/"));
            } elseif ($key === 'key') {
                Cookie::queue(Cookie::forget($key, "/"));
            } elseif ($key === 'zitate') {
                Cookie::queue(Cookie::forget($key, "/"));
            } else {
                Cookie::queue(Cookie::forget($key, "/"));
            }
        }
        return redirect($request->input('url', 'https://metager.de'));
    }

    public function newBlacklist(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');

        $blacklist = $request->input('blacklist');
        $blacklist = substr($blacklist, 0, 2048);

        // Split the blacklist by all sorts of newlines
        $blacklist = preg_split('/\r\n|[\r\n]/', $blacklist);

        $valid_blacklist_entries = [];

        foreach ($blacklist as $blacklist_entry) {
            $regexProtocol = '#^([a-z]{0,5}://)?(www.)?#';
            $blacklist_entry = preg_filter($regexProtocol, '', $blacklist_entry);

            # Allow Only Domains without path
            if (stripos($blacklist_entry, '/') !== false) {
                $blacklist_entry = substr($blacklist_entry, 0, stripos($blacklist_entry, '/'));
            }

            #fixme: this doesn't match all valid URLs
            $regexUrl = '#^(\*\.)?[a-z0-9-]+(\.[a-z0-9]+)?(\.[a-z0-9]{2,})$#';

            if (preg_match($regexUrl, $blacklist_entry) === 1) {
                $valid_blacklist_entries[] = $blacklist_entry;
            }
        }

        # Check if any setting is active
        $cookies = Cookie::get();

        # Remove all cookies from the old method where they got stored
        # in multiple Cookies.
        # The old cookies are in the request currently send so just delete the old cookie
        foreach ($cookies as $key => $value) {
            if (preg_match('/_blpage[0-9]+$/', $key) === 1 && stripos($key, $fokus) !== false) {
                Cookie::queue(Cookie::forget($key, "/"));
            }
        }

        $valid_blacklist_entries = array_unique($valid_blacklist_entries);
        sort($valid_blacklist_entries);

        $cookieName = $fokus . '_blpage';
        $secure = app()->environment("local") ? false : true;
        Cookie::queue(Cookie::forever($cookieName, implode(",", $valid_blacklist_entries), "/", null, $secure, true));

        return redirect(route('settings', ["focus" => $fokus, "url" => $url]) . "#bl");
    }

    public function deleteBlacklist(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        $cookieKey = $request->input('cookieKey');

        Cookie::queue(Cookie::forget($cookieKey, "/"));

        return redirect(route('settings', ["focus" => $fokus, "url" => $url]) . "#bl");
    }

    public function clearBlacklist(Request $request)
    {
        //function to clear the whole black list
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        $cookies = Cookie::get();

        foreach ($cookies as $key => $value) {
            if (stripos($key, $fokus . '_blpage') === 0) {
                Cookie::queue(Cookie::forget($key, "/"));
            }
        }

        return redirect(route('settings', ["focus" => $fokus, "url" => $url]));
    }

    public function loadSettings(Request $request)
    {
        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        $settings = $request->query();
        $secure = app()->environment("local") ? false : true;
        foreach ($settings as $key => $value) {
            if ($key === 'key') {
                Cookie::queue(Cookie::forever("key", $value, '/', null, $secure, true));
            } elseif ($key === 'dark_mode' && ($value === '1' || $value === '2')) {
                Cookie::queue(Cookie::forever($key, $value, '/', null, $secure, true));
            } elseif ($key === 'new_tab' && $value === 'on') {
                Cookie::queue(Cookie::forever($key, 'on', '/', null, $secure, true));
            } elseif ($key === 'zitate' && $value === 'off') {
                Cookie::queue(Cookie::forever($key, 'off', '/', null, $secure, true));
            } else {
                foreach ($langFile->foki as $fokus => $fokusInfo) {
                    if (strpos($key, $fokus . '_blpage') === 0) {
                        Cookie::queue(Cookie::forever($key, $value, "/", null, $secure, true));
                    } elseif (strpos($key, $fokus . '_setting_') === 0) {
                        foreach ($langFile->filter->{'parameter-filter'} as $parameter) {
                            foreach ($parameter->values as $p => $v) {
                                if ($key === $fokus . '_setting_' . $parameter->{'get-parameter'} && $value === $p) {
                                    Cookie::queue(Cookie::forever($key, $value, "/", null, $secure, true));
                                }
                            }
                        }
                    } else {
                        $sumalist = array_keys($this->getSumas($fokus));
                        foreach ($sumalist as $suma) {
                            if ($value !== "on") {
                                $value = "off";
                            }
                            if (strpos($key, $fokus . '_engine_' . $suma) === 0) {
                                Cookie::queue(Cookie::forever($key, $value, "/", null, true, true));
                            }
                        }
                    }
                }
            }
        }

        // Check if a redirect url is defined
        if ($request->filled("redirect_url") && $request->filled("signature") && $request->filled("expires")) {
            $signature = $request->input("signature");
            $expires = filter_var($request->input("expires"), FILTER_VALIDATE_INT);
            $redirect_url = $request->input("redirect_url");
            if (now()->unix() <= $expires && hash_equals(hash_hmac("sha256", $redirect_url . $request->input("expires"), config("app.key")), $signature)) {
                $url = $redirect_url;
            } else {
                $url = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), url('/'));
            }
        } else {
            $url = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), url('/'));
        }

        return redirect($url);
    }

    private function loadBlacklist(Request $request)
    {
    }
}