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
        foreach ($filters as $name => $value) {
            $test = "test";
        }

        $filteredSumas = false;
        foreach ($langFile->filter->{"parameter-filter"} as $name => $filter) {
            $values = $filter->values;
            foreach ($sumas as $name => $suma) {
                if ($suma->configuration->disabled && $suma->configuration->disabledReason === DisabledReason::INCOMPATIBLE_FILTER) {
                    $filteredSumas = true;
                } /*
                 if (!$sumaInfo["filtered"] && $sumaInfo["enabled"] && !empty($filter->sumas->{$suma})) {
                 if (empty($filters[$name])) {
                 $filters[$name] = $filter;
                 unset($filters[$name]->values);
                 }
                 if (empty($filters[$name]->values)) {
                 $filters[$name]->values = (object) [];
                 }
                 foreach ($filter->sumas->{$suma}->values as $key => $value) {
                 $filters[$name]->values->$key = $values->$key;
                 }
                 }*/
            }
        }

        // Apply default value for Language selection
        $current_locale = LaravelLocalization::getCurrentLocaleRegional();
        $default_language_value = "";
        # Set default Value for language selector to current locale
        $authorization = app(Authorization::class);
        $suma_name = "yahoo";
        if ($authorization->canDoAuthenticatedSearch()) {
            $suma_name = "bing";
        }
        if (\property_exists($langFile->sumas->{$suma_name}->lang->regions, $current_locale)) {
            if (\array_key_exists("language", $filters) && \property_exists($filters["language"], "sumas") && \property_exists($filters["language"]->sumas, $suma_name)) {
                $region_suma_value = $langFile->sumas->{$suma_name}->lang->regions->{$current_locale};
                foreach ($filters["language"]->sumas->{$suma_name}->values as $key => $value) {
                    if ($value === $region_suma_value) {
                        $default_language_value = $key;
                        break;
                    }
                }
                if (!empty($default_language_value) && \property_exists($filters["language"]->values, $default_language_value)) {
                    $filters["language"]->values->nofilter = $filters["language"]->values->$default_language_value;
                    unset($filters["language"]->values->$default_language_value);
                } else {
                    $filters["language"]->values->nofilter = "metaGer.filter.noFilter";
                }
            }
        }

        $url = $request->input('url', '');

        # Check if any setting is active
        $cookies = Cookie::get();
        $settingActive = false;
        foreach ($cookies as $key => $value) {
            if (stripos($key, $fokus . "_engine_") === 0 || stripos($key, $fokus . "_setting_") === 0 || strpos($key, $fokus . '_blpage') === 0 || $key === 'dark_mode' || $key === 'new_tab' || $key === 'key' || $key === 'zitate') {
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
        $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', $cookies));

        return view('settings.index')
            ->with('title', trans('titles.settings', ['fokus' => $fokusName]))
            ->with('fokus', $settings->fokus)
            ->with('fokusName', $fokusName)
            ->with('filteredSumas', $filteredSumas)
            ->with('sumas', $sumas)
            ->with('filter', $filters)
            ->with('settingActive', $settingActive)
            ->with('url', $url)
            ->with('blacklist', $blacklist)
            ->with('cookieLink', $cookieLink);
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
        $suma = $request->input('suma', '');
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');

        if (empty($suma) || empty($fokus)) {
            abort(404);
        }

        # Only disable this engine if it's not the last
        $sumas = $this->getSumas($fokus);
        $sumaCount = 0;
        foreach ($sumas as $name => $sumainfo) {
            if (!$sumainfo["filtered"] && $sumainfo["enabled"]) {
                $sumaCount++;
            }
        }
        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        if ($sumaCount > 1 && in_array($suma, $langFile->foki->{$fokus}->sumas)) {
            Cookie::queue(Cookie::forever($fokus . "_engine_" . $suma, "off", "/", null, true, true));
        }

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function enableSearchEngine(Request $request)
    {
        $suma = $request->input('suma', '');
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');

        if (empty($suma) || empty($fokus)) {
            abort(404);
        }

        if (Cookie::get($fokus . "_engine_" . $suma) !== null) {
            Cookie::queue(Cookie::forget($fokus . "_engine_" . $suma, "/"));
        }

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function enableFilter(Request $request)
    {
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');
        if (empty($fokus)) {
            abort(404);
        }

        $newFilters = $request->except(["fokus", "url"]);

        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        foreach ($newFilters as $key => $value) {
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
                        Cookie::queue(Cookie::forever($fokus . "_setting_" . $key, $value, "/", null, true, true));
                        break;
                    }
                }
            }
        }

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function enableSetting(Request $request)
    {
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');
        // Currently only the setting for quotes is supported

        $quotes = $request->input('zitate', '');
        if (!empty($quotes)) {
            if ($quotes === "off") {
                Cookie::queue(Cookie::forever('zitate', 'off', '/', null, true, true));
            } elseif ($quotes === "on") {
                Cookie::queue('zitate', '', 5256000, '/', null, true, true);
            }
        }

        $darkmode = $request->input('dm');
        if (!empty($darkmode)) {
            if ($darkmode === "off") {
                Cookie::queue(Cookie::forever('dark_mode', '1', '/', null, true, true));
            } elseif ($darkmode === "on") {
                Cookie::queue(Cookie::forever('dark_mode', '2', '/', null, true, true));
            } elseif ($darkmode === "system") {
                Cookie::queue(Cookie::forget('dark_mode', '/'));
            }
        }

        $newTab = $request->input('nt');
        if (!empty($newTab)) {
            if ($newTab === "off") {
                Cookie::queue(Cookie::forget('new_tab', '/'));
            } elseif ($newTab === "on") {
                Cookie::queue(Cookie::forever('new_tab', 'on', '/', null, true, true));
            }
        }

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function deleteSettings(Request $request)
    {
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');
        if (empty($fokus)) {
            abort(404);
        }

        $cookies = Cookie::get();
        foreach ($cookies as $key => $value) {
            if (stripos($key, $fokus . "_engine_") === 0 || stripos($key, $fokus . "_setting_") === 0) {
                Cookie::queue(Cookie::forget($key, "/"));
            }
            if ($key === 'dark_mode') {
                Cookie::queue(Cookie::forget($key, "/"));
            }
            if ($key === 'new_tab') {
                Cookie::queue(Cookie::forget($key, "/"));
            }
            if ($key === 'key') {
                Cookie::queue(Cookie::forget($key, "/"));
            }
            if ($key === 'zitate') {
                Cookie::queue(Cookie::forget($key, "/"));
            }
        }
        $this->clearBlacklist($request);

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
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
        $fokus = $request->input('fokus', '');
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
        Cookie::queue(Cookie::forever($cookieName, implode(",", $valid_blacklist_entries), "/", null, true, true));

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])) . "#bl");
    }

    public function deleteBlacklist(Request $request)
    {
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');
        $cookieKey = $request->input('cookieKey');

        Cookie::queue(Cookie::forget($cookieKey, "/"));

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])) . "#bl");
    }

    public function clearBlacklist(Request $request)
    {
        //function to clear the whole black list
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');
        $cookies = Cookie::get();

        foreach ($cookies as $key => $value) {
            if (stripos($key, $fokus . '_blpage') === 0) {
                Cookie::queue(Cookie::forget($key, "/"));
            }
        }

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function loadSettings(Request $request)
    {
        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        $regexUrl = '#^(\*\.)?[a-z0-9]+(\.[a-z0-9]+)?(\.[a-z0-9]{2,})$#';

        $settings = $request->all();
        foreach ($settings as $key => $value) {
            if ($key === 'key') {
                Cookie::queue(Cookie::forever("key", $value, '/', null, true, true));
            } elseif ($key === 'dark_mode' && ($value === '1' || $value === '2')) {
                Cookie::queue(Cookie::forever($key, $value, '/', null, true, true));
            } elseif ($key === 'new_tab' && $value === 'on') {
                Cookie::queue(Cookie::forever($key, 'on', '/', null, true, true));
            } elseif ($key === 'zitate' && $value === 'off') {
                Cookie::queue(Cookie::forever($key, 'off', '/', null, true, true));
            } else {
                foreach ($langFile->foki as $fokus => $fokusInfo) {
                    if (strpos($key, $fokus . '_blpage') === 0 && preg_match($regexUrl, $value) === 1) {
                        Cookie::queue(Cookie::forever($key, $value, "/", null, true, true));
                    } elseif (strpos($key, $fokus . '_setting_') === 0) {
                        foreach ($langFile->filter->{'parameter-filter'} as $parameter) {
                            foreach ($parameter->values as $p => $v) {
                                if ($key === $fokus . '_setting_' . $parameter->{'get-parameter'} && $value === $p) {
                                    Cookie::queue(Cookie::forever($key, $value, "/", null, true, true));
                                }
                            }
                        }
                    } else {
                        $sumalist = array_keys($this->getSumas($fokus));
                        foreach ($sumalist as $suma) {
                            if (strpos($key, $fokus . '_engine_' . $suma) === 0) {
                                Cookie::queue(Cookie::forever($key, 'off', "/", null, true, true));
                            }
                        }
                    }
                }
            }
        }
        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), url('/')));
    }

    private function loadBlacklist(Request $request)
    {
    }
}