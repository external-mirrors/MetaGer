<?php

namespace App\Http\Controllers;

use Cookie;
use LaravelLocalization;
use \App\MetaGer;
use \App\Models\Key;
use \Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $fokus = $request->input('fokus', '');
        $fokusName = "";
        if (empty($fokus)) {
            return redirect('/');
        } else {
            $fokusName = trans('index.foki.' . $fokus);
        }

        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        $sumas = $this->getSumas($fokus);

        # Parse the Parameter Filter
        $filters = [];
        $filteredSumas = false;
        foreach ($langFile->filter->{"parameter-filter"} as $name => $filter) {
            $values = $filter->values;
            $cookie = Cookie::get($fokus . "_setting_" . $filter->{"get-parameter"});
            foreach ($sumas as $suma => $sumaInfo) {
                if (!$filteredSumas && $sumaInfo["filtered"]) {
                    $filteredSumas = true;
                }
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
            if (stripos($key, 'blpage') !== false && stripos($key, $fokus) !== false) {
                $blacklist[$key] = $value;
            }
        }

        # Generating link with set cookies
        $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', $cookies));

        return view('settings.index')
            ->with('title', trans('titles.settings', ['fokus' => $fokusName]))
            ->with('fokus', $request->input('fokus', ''))
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

        $sumasFoki = $langFile->foki->{$fokus}->sumas;

        $sumas = [];
        foreach ($sumasFoki as $suma) {
            if ((!empty($langFile->sumas->{$suma}->disabled) && $langFile->sumas->{$suma}->disabled) ||
                (!empty($langFile->sumas->{$suma}->{"auto-disabled"}) && $langFile->sumas->{$suma}->{"auto-disabled"})
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
            $path = \Request::path();
            $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
            Cookie::queue($fokus . "_engine_" . $suma, "off", 525600, $cookiePath, null, false, false);
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
            $path = \Request::path();
            $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
            Cookie::queue($fokus . "_engine_" . $suma, "", 525600, $cookiePath, null, false, false);
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
            if ($value === "") {
                $path = \Request::path();
                $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
                Cookie::queue($fokus . "_setting_" . $key, "", 0, $cookiePath, null, false, false);
            } else {
                # Check if this filter and its value exists:
                foreach ($langFile->filter->{"parameter-filter"} as $name => $filter) {
                    if ($key === $filter->{"get-parameter"} && !empty($filter->values->$value)) {
                        $path = \Request::path();
                        $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
                        Cookie::queue($fokus . "_setting_" . $key, $value, 525600, $cookiePath, null, false, false);
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
                Cookie::queue('zitate', 'off', 525600, '/', null, false, false);
            } elseif ($quotes === "on") {
                Cookie::queue('zitate', '', 0, '/', null, false, false);
            }
        }

        $darkmode = $request->input('dm');
        if (!empty($darkmode)) {
            if ($darkmode === "off") {
                Cookie::queue('dark_mode', '1', 525600, '/', null, false, false);
            } elseif ($darkmode === "on") {
                Cookie::queue('dark_mode', '2', 525600, '/', null, false, false);
            } elseif ($darkmode === "system") {
                Cookie::queue('dark_mode', '', 0, '/', null, false, false);
            }
        }

        $newTab = $request->input('nt');
        if (!empty($newTab)) {
            if ($newTab === "off") {
                Cookie::queue('new_tab', '', 0, '/', null, false, false);
            } elseif ($newTab === "on") {
                Cookie::queue('new_tab', 'on', 525600, '/', null, false, false);
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
                $path = \Request::path();
                $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
                Cookie::queue($key, "", 0, $cookiePath, null, false, false);
            }
            if ($key === 'dark_mode') {
                Cookie::queue($key, "", 0, '/', null, false, false);
            }
            if ($key === 'new_tab') {
                Cookie::queue($key, "", 0, '/', null, false, false);
            }
            if ($key === 'key') {
                Cookie::queue($key, "", 0, '/', null, false, false);
            }
            if ($key === 'zitate') {
                Cookie::queue($key, "", 0, '/', null, false, false);
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
        $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
        if ($key === 'dark_mode') {
            Cookie::queue($key, "", 0, '/', null, false, false);
        } elseif ($key === 'new_tab') {
            Cookie::queue($key, "", 0, '/', null, false, false);
        } elseif ($key === 'key') {
            Cookie::queue($key, "", 0, '/', null, false, false);
        } elseif ($key === 'zitate') {
            Cookie::queue($key, "", 0, '/', null, false, false);
        } else {
            Cookie::queue($key, "", 0, $cookiePath, null, false, false);
        }
        return redirect($request->input('url', 'https://metager.de'));
    }

    public function removeAllSettings(Request $request)
    {
        $path = \Request::path();
        $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);

        foreach (Cookie::get() as $key => $value) {
            if ($key === 'dark_mode') {
                Cookie::queue($key, "", 0, '/', null, false, false);
            } elseif ($key === 'new_tab') {
                Cookie::queue($key, "", 0, '/', null, false, false);
            } elseif ($key === 'key') {
                Cookie::queue($key, "", 0, '/', null, false, false);
            } elseif ($key === 'zitate') {
                Cookie::queue($key, "", 0, '/', null, false, false);
            } else {
                Cookie::queue($key, "", 0, $cookiePath, null, false, false);
            }
        }
        return redirect($request->input('url', 'https://metager.de'));
    }

    public function newBlacklist(Request $request)
    {
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');

        $regexProtocol = '#^([a-z]{0,5}://)?(www.)?#';
        $blacklist = preg_filter($regexProtocol, '', $request->input('blacklist'));

        if (stripos($blacklist, '/') !== false) {
            $blacklist = substr($blacklist, 0, stripos($blacklist, '/'));
        }
        #fixme: this doesn't match all valid URLs
        $regexUrl = '#^(\*\.)?[a-z0-9-]+(\.[a-z0-9]+)?(\.[a-z0-9]{2,})$#';
        if (preg_match($regexUrl, $blacklist) === 1) {
            $path = \Request::path();
            $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
            $cookies = Cookie::get();
            $cookieCounter = 0;
            $noduplicate = true;

            ksort($cookies);

            if (!empty($cookies)) {
                foreach ($cookies as $key => $value) {
                    if (stripos($key, $fokus . '_blpage') === 0) {
                        if ($value === $blacklist) {
                            $noduplicate = false;
                            break;
                        }
                        if ((int)(substr($key, strlen($fokus . '_blpage'))) === $cookieCounter) {
                            $cookieCounter++;
                        }
                    }
                }
            }
            if ($noduplicate && !empty($blacklist) > 0 && strlen($blacklist) <= 255) {
                $cookieName = $fokus . '_blpage' . $cookieCounter;
                Cookie::queue($cookieName, $blacklist, 525600, $cookiePath, null, false, false);
            }
        }
        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function deleteBlacklist(Request $request)
    {
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');
        $path = \Request::path();
        $cookieKey = $request->input('cookieKey');
        $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);

        Cookie::queue($cookieKey, "", 0, $cookiePath, null, false, false);

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function clearBlacklist(Request $request)
    {
        //function to clear the whole black list
        $fokus = $request->input('fokus', '');
        $url = $request->input('url', '');
        $path = \Request::path();
        $empty = $request->input('empty');
        $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);
        $cookies = Cookie::get();

        foreach ($cookies as $key => $value) {
            if (stripos($key, $fokus . '_blpage') === 0) {
                Cookie::queue($key, "", 0, $cookiePath, null, false, false);
            }
        }

        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('settings', ["fokus" => $fokus, "url" => $url])));
    }

    public function loadSettings(Request $request)
    {
        $path = \Request::path();
        $cookiePath = "/" . substr($path, 0, strpos($path, "meta/") + 5);

        $langFile = MetaGer::getLanguageFile();
        $langFile = json_decode(file_get_contents($langFile));

        $regexUrl = '#^(\*\.)?[a-z0-9]+(\.[a-z0-9]+)?(\.[a-z0-9]{2,})$#';

        $settings = $request->all();
        foreach ($settings as $key => $value) {
            if ($key === 'key') {
                $memberKey = new Key($value);
                if ($memberKey->getStatus()) {
                    Cookie::queue($key, $value, 525600, '/', null, false, false);
                }
            } elseif ($key === 'dark_mode' && ($value === '1' || $value === '2')) {
                Cookie::queue($key, $value, 525600, '/', null, false, false);
            } elseif ($key === 'new_tab' && $value === 'on') {
                Cookie::queue($key, 'on', 525600, '/', null, false, false);
            } elseif ($key === 'zitate' && $value === 'off') {
                Cookie::queue($key, 'off', 525600, '/', null, false, false);
            } else {
                foreach ($langFile->foki as $fokus => $fokusInfo) {
                    if (strpos($key, $fokus . '_blpage') === 0 && preg_match($regexUrl, $value) === 1) {
                        Cookie::queue($key, $value, 525600, $cookiePath, null, false, false);
                    } elseif (strpos($key, $fokus . '_setting_') === 0) {
                        foreach ($langFile->filter->{'parameter-filter'} as $parameter) {
                            foreach ($parameter->values as $p => $v) {
                                if ($key === $fokus . '_setting_' . $parameter->{'get-parameter'} && $value === $p) {
                                    Cookie::queue($key, $value, 525600, $cookiePath, null, false, false);
                                }
                            }
                        }
                    } else {
                        $sumalist = array_keys($this->getSumas($fokus));
                        foreach ($sumalist as $suma) {
                            if (strpos($key, $fokus . '_engine_' . $suma) === 0) {
                                Cookie::queue($key, 'off', 525600, $cookiePath, null, false, false);
                            }
                        }
                    }
                }
            }
        }
        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), url('/')));
    }
}
