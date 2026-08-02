<?php

namespace App\Http\Controllers;

use App\Models\Authorization\Authorization;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Authorization\SuggestionDebtAuthorization;
use App\Models\Configuration\SearchEngineRegistry;
use App\Models\Configuration\Searchengines;
use App\Models\Configuration\SettingsSchema;
use App\Models\DisabledReason;
use App\SearchSettings;
use App\Suggestions;
use Cookie;
use foroco\BrowserDetection;
use \Illuminate\Http\Request;

class SettingsController extends Controller
{



    public function index(Request $request)
    {
        $settings = app(SearchSettings::class);
        $originalFokus = $settings->fokus;
        $authorization = app(Authorization::class);
        $url = $request->input('url', '');

        // Check if any setting is active. Populated further as we build
        // per-fokus data below (loadParameterFilter()/engine settings touch
        // $settings->user_settings as a side effect of reading them).
        $settingActive = sizeof($settings->user_settings) > 0;

        // Build engines/filter/blacklist for every fokus, not just the
        // current one, so the settings page can manage all of them at once.
        $foki = [];
        foreach ($settings->available_foki as $fokus) {
            $settings->fokus = $fokus;
            $settings->parameterFilter = [];

            $searchengines = new Searchengines();
            $sumas = $searchengines->getSearchEnginesForFokus();

            if (sizeof($searchengines->user_settings) > 0) {
                $settingActive = true;
            }

            $filteredSumas = false;
            foreach ($sumas as $suma) {
                if ($suma->configuration->disabled && in_array(DisabledReason::INCOMPATIBLE_FILTER, $suma->configuration->disabledReasons)) {
                    $filteredSumas = true;
                    break;
                }
            }

            [$blacklist_entries, $blacklist_tld] = SearchSettings::parseBlacklistCookie(Cookie::get($fokus . "_blpage"));
            $blacklist = array_merge(array_map(fn($value) => "*." . $value, $blacklist_tld), $blacklist_entries);

            $hasCustomFilter = false;
            foreach ($settings->parameterFilter as $filter) {
                if (!empty($filter->value)) {
                    $hasCustomFilter = true;
                    break;
                }
            }

            $hasEnabledEngine = false;
            foreach ($sumas as $suma) {
                if ($suma->configuration->disabled === false) {
                    $hasEnabledEngine = true;
                    break;
                }
            }

            $foki[$fokus] = [
                'name' => trans('index.foki.' . $fokus),
                'sumas' => $sumas,
                'disabledReasons' => $searchengines->disabledReasons,
                'searchCost' => $searchengines->getSearchCost(),
                'rawSearchCost' => $searchengines->getRawSearchCost(),
                'filteredSumas' => $filteredSumas,
                'filter' => $settings->parameterFilter,
                'blacklist' => $blacklist,
                'hasEnabledEngine' => $hasEnabledEngine,
                // Drives the "this focus has custom settings" tab indicator.
                'hasCustomSettings' => sizeof($searchengines->user_settings) > 0 || sizeof($blacklist) > 0 || $hasCustomFilter,
            ];
        }
        $settings->fokus = $originalFokus;

        # Generating link with set cookies
        $settings_params = [];

        # Add Settings for searchengines supplied in cookies and headers
        foreach (array_merge($request->header(), $request->cookie()) as $key => $value) {
            if (is_array($value)) {
                $value = $value[0];
            }
            if ($settings->isValidSetting($key, $value)) {
                $settings_params[$key] = $value;
            }
        }

        unset($settings_params["js_available"]);
        if ($authorization instanceof KeyAuthorization) {
            $settings_params["key"] = $authorization->getToken();
        }
        $cookieLink = null;
        if (sizeof($settings_params) > 0) {
            $cookieLink = route('loadSettings', $settings_params);
        }

        $agent = (new BrowserDetection())->getAll($request->userAgent());

        return response(view('settings.index')
            ->with('title', trans('titles.settings', ['fokus' => $foki[$originalFokus]['name']]))
            ->with('fokus', $originalFokus)
            ->with('foki', $foki)
            ->with('authorization', $authorization)
            ->with('settingActive', $settingActive)
            ->with('url', $url)
            ->with('cookieLink', $cookieLink)
            ->with('agent', $agent)
            ->with('browser', $agent)
            ->with('globalSettings', collect(SettingsSchema::forClients(["web"]))->keyBy('key'))
            ->with('currentGlobalValues', [
                'tips' => $settings->tips ? 'on' : 'off',
                'tiles_startpage' => $settings->tiles_startpage ? 'on' : 'off',
                'dark_mode' => $settings->theme,
                'new_tab' => $settings->newtab ? 'on' : 'off',
                'zitate' => $settings->zitate ? 'on' : 'off',
            ])
            ->with('js', [mix('js/scriptSettings.js')]), 200, [
            "Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0, private",
        ]);
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

        $redirect_url = route('settings', ["focus" => $settings->fokus, "url" => $url]) . "#" . $settings->fokus . "-engines";

        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
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

        $redirect_url = route('settings', ["focus" => $settings->fokus, "url" => $url]) . "#" . $settings->fokus . "-engines";
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
    }

    public function enableFilter(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        if (empty($fokus)) {
            abort(404);
        }

        $newFilters = $request->except(["focus", "url"]);

        $langFile = app(SearchEngineRegistry::class);

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

        $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#" . $fokus . "-filter";
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
    }

    public function enableSetting(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');

        if (self::PROCESS_GLOBAL_SETTING_CHANGE("suggestion_provider", $request->input('suggestion_provider', ''))) {
            $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#suggest-settings";
        } else if (self::PROCESS_GLOBAL_SETTING_CHANGE("suggestion_delay", $request->input('suggestion_delay', ''))) {
            $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#suggest-settings";
        } else if (self::PROCESS_GLOBAL_SETTING_CHANGE("suggestion_addressbar", $request->input('suggestion_addressbar', ''))) {
            $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#suggest-settings";
        } else {
            // All Settings behind "More Settings"
            $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#more-settings";
            self::PROCESS_GLOBAL_SETTING_CHANGE("tips", $request->input('tips', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("tiles_startpage", $request->input('tiles_startpage', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("zitate", $request->input('zitate', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("dark_mode", $request->input('dark_mode', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("new_tab", $request->input('new_tab', ''));
        }

        $headers = [
            "Cache-Control" => "no-store, no-cache, must-revalidate, max-age=0, private"
        ];
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response, 200, $headers);
        } else {
            return redirect($redirect_url, 302, $headers);
        }
    }

    /**
     * Processes a new setting value and queues/deletes necessary cookies
     * 
     * @param string $key
     * @param string $value
     * @return bool True if setting was valid and has been processed. False Otherwise
     */
    public static function PROCESS_GLOBAL_SETTING_CHANGE(string $key, string $value): bool
    {
        $settings = app(SearchSettings::class);
        $secure = app()->environment("local") ? false : true;
        $valid_suggest_providers = array_merge(["off"], array_keys(Suggestions::GET_AVAILABLE_PROVIDERS()));

        if ($key === "suggestion_provider" && !empty($value) && in_array($value, $valid_suggest_providers)) {
            $settings->suggestion_provider = $value;
            if ($value === "off") {
                Cookie::queue(Cookie::forget('suggestion_provider', '/'));
                SuggestionDebtAuthorization::REMOVE_SETTINGS();
            } else {
                Cookie::queue(Cookie::forever('suggestion_provider', $value, '/', null, $secure, true));
                SuggestionDebtAuthorization::UPDATE_SETTINGS(true);
            }
            return true;
        } else if ($key === "suggestion_delay" && !empty($value) && in_array($value, ["short", "medium", "long"])) {
            $settings->suggestion_delay = $value;
            SuggestionDebtAuthorization::UPDATE_SETTINGS();
            if ($value === "medium") {
                Cookie::queue(Cookie::forget("suggestion_delay", "/"));
            } else {
                Cookie::queue(Cookie::forever('suggestion_delay', $value, '/', null, $secure, true));
            }
            $settings->suggestion_delay = $value;
            return true;
        } else if ($key === "suggestion_addressbar" && !empty($value) && in_array($value, ["on", "off"])) {
            $settings->suggestion_addressbar = $value === "on" ? true : false;
            if ($value === "on") {
                Cookie::queue(Cookie::forever('suggestion_addressbar', 'on', '/', null, $secure, true));
                SuggestionDebtAuthorization::UPDATE_SETTINGS(true);
            } elseif ($value === "off") {
                Cookie::queue(Cookie::forget("suggestion_addressbar", "/"));
                SuggestionDebtAuthorization::REMOVE_SETTINGS();
            }
            return true;
        } else if ($key === "tips" && !empty($value)) {
            if ($value === "off") {
                Cookie::queue(Cookie::forever('tips', 'off', '/', null, $secure, true));
            } elseif ($value === "on") {
                Cookie::queue(Cookie::forget("tips", "/"));
            }
            return true;
        } else if ($key === "tiles_startpage" && !empty($value)) {
            if ($value === "off") {
                Cookie::queue(Cookie::forever('tiles_startpage', 'off', '/', null, $secure, true));
            } elseif ($value === "on") {
                Cookie::queue(Cookie::forget("tiles_startpage", "/"));
            }
            return true;
        } else if ($key === "zitate" && !empty($value)) {
            if ($value === "off") {
                Cookie::queue(Cookie::forever('zitate', 'off', '/', null, $secure, true));
            } elseif ($value === "on") {
                Cookie::queue(Cookie::forget("zitate", "/"));
            }
            return true;
        } else if ($key === "dark_mode" && in_array($value, ["system", "light", "dark"])) {
            if ($value === "system") {
                Cookie::queue(Cookie::forget('dark_mode', '/'));
            } else {
                Cookie::queue(Cookie::forever('dark_mode', $value, '/', null, $secure, true));
            }
            return true;
        } else if ($key === "new_tab" && in_array($value, ["on", "off"])) {
            if ($value === "off") {
                Cookie::queue(Cookie::forget('new_tab', '/'));
            } else {
                Cookie::queue(Cookie::forever('new_tab', 'on', '/', null, $secure, true));
            }
            return true;
        }
        return false;
    }

    public function deleteSettings(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        if (empty($fokus)) {
            abort(404);
        }

        $global_settings = array_diff(SettingsSchema::globalSettingKeys(), ["key", "suggestion_addressbar"]);

        $settings = Cookie::get();
        if ($request->wantsJson()) {
            foreach ($request->header() as $key => $value) {
                $settings[str_replace("-", "_", $key)] = $value;
            }
        }
        foreach ($settings as $key => $value) {
            if (stripos($key, $fokus . "_engine_") === 0 || stripos($key, $fokus . "_setting_") === 0) {
                Cookie::queue(Cookie::forget($key, "/"));
            }

            if (in_array($key, $global_settings)) {
                Cookie::queue(Cookie::forget($key, "/"));
            }
        }
        $this->clearBlacklist($request);

        $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]);
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
    }

    /**
     * Machine-readable description of every setting MetaGer understands:
     * global settings and, per fokus, its engines/filters/blacklist -
     * along with the {fokus}_engine_{name} / {fokus}_setting_{name} /
     * {fokus}_blpage naming pattern needed to actually address them as a
     * GET-parameter, cookie, or header (see SearchSettings::getSettingValue()).
     *
     * Intended for headless clients (e.g. the mobile app) that need to
     * render a settings UI without reverse-engineering the web page.
     */
    public function schema(Request $request)
    {
        $registry = app(SearchEngineRegistry::class);

        $global = array_map(function ($setting) {
            return [
                "key" => $setting["key"],
                "type" => $setting["type"],
                "default" => $setting["default"] ?? null,
                "values" => isset($setting["values"]) ? array_map(fn($option) => [
                    "value" => $option["value"],
                    "label" => $option["translate"] ? trans($option["label"]) : $option["label"],
                ], $setting["values"]) : null,
            ];
        }, SettingsSchema::forClients(["headless"]));

        $foki = [];
        foreach ($registry->foki as $fokus => $fokusInfo) {
            $engines = [];
            foreach ($fokusInfo->sumas as $engineName) {
                if (!property_exists($registry->sumas, $engineName)) {
                    continue;
                }
                $engines[] = [
                    "name" => $engineName,
                    "displayName" => $registry->sumas->{$engineName}->infos->display_name ?? $engineName,
                    "settingKey" => "{$fokus}_engine_{$engineName}",
                ];
            }

            $filters = [];
            foreach ($registry->filter->{"parameter-filter"} as $filterName => $filter) {
                if (sizeof(array_intersect(array_keys((array) $filter->sumas), $fokusInfo->sumas)) === 0) {
                    continue;
                }
                $filters[] = [
                    "name" => $filterName,
                    "label" => trans($filter->name),
                    "settingKey" => "{$fokus}_setting_{$filter->{'get-parameter'}}",
                    "values" => array_map(
                        fn($value, $label) => ["value" => $value, "label" => trans($label)],
                        array_keys((array) $filter->values),
                        array_values((array) $filter->values)
                    ),
                ];
            }

            $foki[] = [
                "fokus" => $fokus,
                "displayName" => trans($fokusInfo->{"display-name"}),
                "engines" => $engines,
                "filters" => $filters,
                "blacklistSettingKey" => "{$fokus}_blpage",
            ];
        }

        return response()->json([
            "global" => $global,
            "foki" => $foki,
            "keyPatterns" => [
                "engine" => "{fokus}_engine_{name}",
                "filter" => "{fokus}_setting_{name}",
                "blacklist" => "{fokus}_blpage",
            ],
        ]);
    }

    public function allSettingsIndex(Request $request)
    {
        $sumaFile = app(SearchEngineRegistry::class);

        return view('settings.allSettings')
            ->with('title', trans('titles.allSettings'))
            ->with('url', $request->input('url', ''))
            ->with('sumaFile', $sumaFile);
    }

    public function removeOneSetting(Request $request)
    {
        $key = $request->input('key', '');
        Cookie::queue(Cookie::forget($key, "/"));

        $redirect_url = $request->input('url', 'https://metager.de');
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
    }

    public function removeAllSettings(Request $request)
    {
        foreach (app(SearchSettings::class)->user_settings as $key => $value) {
            Cookie::queue(Cookie::forget($key, "/"));
        }

        $redirect_url = $request->input('url', 'https://metager.de');
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($request->input('url', 'https://metager.de'));
        }
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
            $blacklist_entry = trim($blacklist_entry);
            if ($blacklist_entry === '') {
                continue;
            }
            if (!preg_match('/^https?:\/\//', $blacklist_entry)) {
                $blacklist_entry = "https://" . $blacklist_entry;
            }
            // Only use hostname from url
            $blacklist_entry = parse_url($blacklist_entry, PHP_URL_HOST);
            if ($blacklist_entry === null || $blacklist_entry === false)
                continue;
            $blacklist_entry = substr($blacklist_entry, 0, 255);

            // Reject anything that isn't actually a valid hostname (optionally "*."-wildcarded)
            $hostname = str_starts_with($blacklist_entry, "*.") ? substr($blacklist_entry, 2) : $blacklist_entry;
            if (filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
                continue;
            }

            $valid_blacklist_entries[] = $blacklist_entry;
        }
        $valid_blacklist_entries = array_unique($valid_blacklist_entries);
        sort($valid_blacklist_entries);

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


        $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#" . $fokus . "-bl";
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
    }

    public function deleteBlacklist(Request $request)
    {
        $fokus = $request->input('focus', '');
        $url = $request->input('url', '');
        $cookieKey = $request->input('cookieKey');

        Cookie::queue(Cookie::forget($cookieKey, "/"));

        $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#" . $fokus . "-bl";
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
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

        $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]);
        if ($request->wantsJson()) {
            $response = $this->cookiesToJsonResponse($redirect_url);
            return response()->json($response);
        } else {
            return redirect($redirect_url);
        }
    }

    public function loadSettings(Request $request)
    {
        $settings = $request->query();
        $secure = app()->environment("local") ? false : true;

        $params_for_startpage = [];
        if ($request->filled("eingabe")) {
            $params_for_startpage["eingabe"] = $request->input("eingabe");
        }

        $searchsettings = app(SearchSettings::class);

        foreach ($settings as $key => $value) {
            # Add Settings for searchengines supplied in cookies and headers
            if ($searchsettings->isValidSetting($key, $value)) {
                if ($key === 'key') {
                    Cookie::queue(Cookie::forever("key", $value, '/', null, $secure, true));
                    $params_for_startpage["key"] = $value;
                } elseif ($key === 'dark_mode' && ($value === '1' || $value === '2')) {
                    Cookie::queue(Cookie::forever($key, $value, '/', null, $secure, true));
                } elseif ($key === 'new_tab' && $value === 'on') {
                    Cookie::queue(Cookie::forever($key, 'on', '/', null, $secure, true));
                } elseif ($key === 'zitate' && $value === 'off') {
                    Cookie::queue(Cookie::forever($key, 'off', '/', null, $secure, true));
                } else {
                    // Setting page
                    Cookie::queue(Cookie::forever($key, $value, '/', null, $secure, true));
                }
            }
        }

        // Check if a redirect url is defined
        $url = route("startpage", $params_for_startpage);
        if ($request->filled("redirect_url") && $request->filled("expires")) {
            $redirect_url = $request->input("redirect_url");
            $expires = filter_var($request->input("expires"), FILTER_VALIDATE_INT);
            if ($expires && now()->unix() <= $expires) {
                if ($request->filled("signature") && hash_equals(hash_hmac("sha256", $redirect_url . $request->input("expires"), config("app.key")), $request->input("signature"))) {
                    $url = $redirect_url;
                } elseif ($request->filled("safebrowse_signature") && ($safebrowseSecret = config("app.safebrowse_secret")) && hash_equals(hash_hmac("sha256", $redirect_url . $request->input("expires"), $safebrowseSecret), $request->input("safebrowse_signature"))) {
                    $url = $redirect_url;
                }
            }
        }

        return redirect($url);
    }

    /**
     * The webextension calls settings manually
     * and expects a json response instead of
     * set cookies.
     * We will loop through all queued cookies and 
     * create a JSON response object from that
     */
    private function cookiesToJsonResponse($redirect_url)
    {
        $cookies = Cookie::getQueuedCookies();
        $response = [
            "remove" => [],
            "set" => [],
            "redirect" => $redirect_url
        ];
        foreach ($cookies as $cookie) {
            if ($cookie->isCleared()) {
                $response["remove"][] = $cookie->getName();
            } else {
                $response["set"][$cookie->getName()] = $cookie->getValue();
            }
        }
        return $response;
    }
    private function loadBlacklist(Request $request)
    {
    }
}