<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Vite;
use App\Authentication\CookieSupport;
use App\Http\SettingsCarry;
use App\Models\Authorization\Authorization;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Authorization\SuggestionDebtAuthorization;
use App\Models\Configuration\SearchEngineRegistry;
use App\Models\Configuration\Searchengines;
use App\Models\Configuration\SettingsSchema;
use App\Models\DisabledReason;
use App\Models\SearchengineConfiguration;
use App;
use App\Localization;
use App\Localization\LocaleContext;
use App\SearchSettings;
use App\Suggestions;
use Cookie;
use App\Support\Browser;
use \Illuminate\Http\Request;
use LaravelLocalization;

class SettingsController extends Controller
{



    public function index(Request $request)
    {
        $settings = app(SearchSettings::class);
        $originalFokus = $settings->fokus;
        $authorization = app(Authorization::class);
        // The "back to the last page" link, rendered verbatim as $url below —
        // never through route()/to(), so a setting changed on this very page
        // load (SettingsCarry::set()/forget(), called by the POST handler
        // that redirected here) would otherwise never reach it: the visitor
        // navigates back to the page they came from and the change they just
        // made is gone again. carryIntoUrl() is a no-op for an empty $url and
        // for one that already carries everything current, so this is safe
        // to call unconditionally.
        // ConvertEmptyStringsToNull turns an empty `?url=` into a present-but-
        // null input, which `input('url', '')`'s default does not catch —
        // the default only applies when the key is missing entirely.
        $url = CookieSupport::carryIntoUrl((string) $request->input('url', ''), $request);

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

            [$blacklist_entries, $blacklist_tld] = $settings->blacklistFor($fokus);
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

        # Add Settings for searchengines supplied in cookies, headers, and the
        # query — query last, so a query override wins over a stale cookie,
        # matching SearchSettings::getSettingValue()'s own GET -> cookie ->
        # header precedence. Without this, a cookie-blind visitor's settings
        # (query only, no cookie ever stuck) were invisible to this backup
        # link entirely.
        foreach (array_merge($request->header(), $request->cookie(), $request->query()) as $key => $value) {
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

        $agent = Browser::fromRequest($request);

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
            ->with('js', [Vite::asset('resources/js/scriptSettings.js')]), 200, [
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
        $cookieName = $settings->fokus . "_engine_" . $sumaName;
        if (!$engines[$sumaName]->configuration->disabled) {
            if ($engines[$sumaName]->configuration->disabledByDefault) {
                Cookie::queue(Cookie::forget($cookieName, "/"));
                app(SettingsCarry::class)->forget($cookieName);
            } else {
                Cookie::queue(Cookie::forever($cookieName, "off", "/", null, $secure, true));
                app(SettingsCarry::class)->set($cookieName, "off");
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
        $cookieName = $settings->fokus . "_engine_" . $sumaName;
        if ($engines[$sumaName]->configuration->disabled) {
            if ($engines[$sumaName]->configuration->disabledByDefault) {
                Cookie::queue(Cookie::forever($cookieName, "on", "/", null, $secure, true));
                app(SettingsCarry::class)->set($cookieName, "on");
            } else {
                Cookie::queue(Cookie::forget($cookieName, "/"));
                app(SettingsCarry::class)->forget($cookieName);
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

        $langFile = app(SearchEngineRegistry::class);

        // Not $request->except(["focus", "url"]): that trusted everything
        // else in the merged query+body input to be a filter this form
        // posted. Once this form's own `action` URL can carry settings
        // forward for a cookie-blind visitor (App\Http\SettingsCarry), an
        // unrelated carried setting — e.g. `web_engine_bing=off` sitting in
        // the query string — would land here too and get treated as a
        // filter with an unrecognised name, which the empty-value branch
        // below would then Cookie::forget() under a garbage key. Iterating
        // the known parameter-filters instead means only an actual filter
        // get-parameter is ever read.
        $newFilters = [];
        foreach ($langFile->filter->{"parameter-filter"} as $filter) {
            $param = $filter->{"get-parameter"};
            if ($request->has($param)) {
                $newFilters[$param] = $request->input($param);
            }
        }

        /**
         * Pin the interface language before touching a market.
         *
         * `web_setting_m` is written from here, and it is also where every
         * pre-`mg_locale` browser's language still lives, so
         * `LocaleContext::cookieLocale()` reads it as a language while no
         * `mg_locale` exists. For a browser that never had one, a first-ever
         * market change would therefore come back as an interface change —
         * exactly the conflation being removed. Writing the current locale
         * first makes the old cookie unambiguous from this point on.
         */
        if (Cookie::get(LocaleContext::cookieName()) === null) {
            Localization::context()->persistCookie();
        }

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
                app(SettingsCarry::class)->forget($fokus . "_setting_" . $key);
            } else {
                # Check if this filter and its value exists:
                foreach ($langFile->filter->{"parameter-filter"} as $name => $filter) {
                    if ($key === $filter->{"get-parameter"} && !empty($filter->values->$value)) {
                        $path = \Request::path();
                        $cookiePath = "/";
                        $secure = app()->environment("local") ? false : true;
                        Cookie::queue(Cookie::forever($fokus . "_setting_" . $key, $value, "/", null, $secure, true));
                        app(SettingsCarry::class)->set($fokus . "_setting_" . $key, $value);
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
            //
            // route() is called after every PROCESS_GLOBAL_SETTING_CHANGE
            // below, not before: it reads App\Http\SettingsCarry, which
            // those calls just mutated, and a URL built first would still
            // carry whatever value was current before this request changed
            // it — silently undoing the change for a cookie-blind visitor
            // the moment they followed their own redirect.
            self::PROCESS_GLOBAL_SETTING_CHANGE("tips", $request->input('tips', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("tiles_startpage", $request->input('tiles_startpage', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("zitate", $request->input('zitate', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("dark_mode", $request->input('dark_mode', ''));
            self::PROCESS_GLOBAL_SETTING_CHANGE("new_tab", $request->input('new_tab', ''));
            $redirect_url = route('settings', ["focus" => $fokus, "url" => $url]) . "#more-settings";
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

        $carry = app(SettingsCarry::class);

        if ($key === "suggestion_provider" && !empty($value) && in_array($value, $valid_suggest_providers)) {
            $settings->suggestion_provider = $value;
            if ($value === "off") {
                Cookie::queue(Cookie::forget('suggestion_provider', '/'));
                $carry->forget('suggestion_provider');
                SuggestionDebtAuthorization::REMOVE_SETTINGS();
            } else {
                Cookie::queue(Cookie::forever('suggestion_provider', $value, '/', null, $secure, true));
                $carry->set('suggestion_provider', $value);
                SuggestionDebtAuthorization::UPDATE_SETTINGS(true);
            }
            return true;
        } else if ($key === "suggestion_delay" && !empty($value) && in_array($value, ["short", "medium", "long"])) {
            $settings->suggestion_delay = $value;
            SuggestionDebtAuthorization::UPDATE_SETTINGS();
            if ($value === "medium") {
                Cookie::queue(Cookie::forget("suggestion_delay", "/"));
                $carry->forget('suggestion_delay');
            } else {
                Cookie::queue(Cookie::forever('suggestion_delay', $value, '/', null, $secure, true));
                $carry->set('suggestion_delay', $value);
            }
            $settings->suggestion_delay = $value;
            return true;
        } else if ($key === "suggestion_addressbar" && !empty($value) && in_array($value, ["on", "off"])) {
            $settings->suggestion_addressbar = $value === "on" ? true : false;
            if ($value === "on") {
                Cookie::queue(Cookie::forever('suggestion_addressbar', 'on', '/', null, $secure, true));
                $carry->set('suggestion_addressbar', 'on');
                SuggestionDebtAuthorization::UPDATE_SETTINGS(true);
            } elseif ($value === "off") {
                Cookie::queue(Cookie::forget("suggestion_addressbar", "/"));
                $carry->forget('suggestion_addressbar');
                SuggestionDebtAuthorization::REMOVE_SETTINGS();
            }
            return true;
        } else if ($key === "tips" && !empty($value)) {
            if ($value === "off") {
                Cookie::queue(Cookie::forever('tips', 'off', '/', null, $secure, true));
                $carry->set('tips', 'off');
            } elseif ($value === "on") {
                Cookie::queue(Cookie::forget("tips", "/"));
                $carry->forget('tips');
            }
            return true;
        } else if ($key === "tiles_startpage" && !empty($value)) {
            if ($value === "off") {
                Cookie::queue(Cookie::forever('tiles_startpage', 'off', '/', null, $secure, true));
                $carry->set('tiles_startpage', 'off');
            } elseif ($value === "on") {
                Cookie::queue(Cookie::forget("tiles_startpage", "/"));
                $carry->forget('tiles_startpage');
            }
            return true;
        } else if ($key === "zitate" && !empty($value)) {
            if ($value === "off") {
                Cookie::queue(Cookie::forever('zitate', 'off', '/', null, $secure, true));
                $carry->set('zitate', 'off');
            } elseif ($value === "on") {
                Cookie::queue(Cookie::forget("zitate", "/"));
                $carry->forget('zitate');
            }
            return true;
        } else if ($key === "dark_mode" && in_array($value, ["system", "light", "dark"])) {
            if ($value === "system") {
                Cookie::queue(Cookie::forget('dark_mode', '/'));
                $carry->forget('dark_mode');
            } else {
                Cookie::queue(Cookie::forever('dark_mode', $value, '/', null, $secure, true));
                $carry->set('dark_mode', $value);
            }
            return true;
        } else if ($key === "new_tab" && in_array($value, ["on", "off"])) {
            if ($value === "off") {
                Cookie::queue(Cookie::forget('new_tab', '/'));
                $carry->forget('new_tab');
            } else {
                Cookie::queue(Cookie::forever('new_tab', 'on', '/', null, $secure, true));
                $carry->set('new_tab', 'on');
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
        // Cookie::get() alone is also empty for a cookie-blind visitor —
        // nothing ever stuck — so merge in whatever is currently carried by
        // query too, and "reset all settings" actually resets what is
        // active rather than only what happens to be in a cookie.
        $settings = array_merge($settings, app(SettingsCarry::class)->all());
        foreach ($settings as $key => $value) {
            if (stripos($key, $fokus . "_engine_") === 0 || stripos($key, $fokus . "_setting_") === 0) {
                Cookie::queue(Cookie::forget($key, "/"));
                app(SettingsCarry::class)->forget($key);
            }

            if (in_array($key, $global_settings)) {
                Cookie::queue(Cookie::forget($key, "/"));
                app(SettingsCarry::class)->forget($key);
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
     * `?lang=` -> a default regional locale, one per `lang/` directory this
     * MetaGer install ships translations for. Deliberately not derived from
     * `LaravelLocalization::getSupportedLocales()` (~19 regional variants):
     * `schema()` below needs exactly one *default* region per language.
     *
     * The same table `LocaleContext` resolves a bare `Accept-Language` with,
     * and referenced rather than repeated: two copies of "which region does
     * this language mean" is how they came to disagree about Catalan.
     * `docs/locale-contract.md` §4 is the written form.
     */
    private const LANG_TO_LOCALE = LocaleContext::HOME_REGION;

    /**
     * Machine-readable description of every setting MetaGer understands:
     * global settings and, per fokus, its engines/filters/blacklist -
     * along with the {fokus}_engine_{name} / {fokus}_setting_{name} /
     * {fokus}_blpage naming pattern needed to actually address them as a
     * GET-parameter, cookie, or header (see SearchSettings::getSettingValue()).
     *
     * Intended for headless clients (e.g. the mobile app) that need to
     * render a settings UI without reverse-engineering the web page.
     *
     * `?lang=` overrides the locale `Localization::setLocale()` already
     * resolved from the request (host, then URL path prefix, then a guess
     * from `Accept-Language` - see that class). All three are built for a
     * *browser* navigating pages, and get this endpoint wrong for a headless
     * client with no URL path segment to carry a locale in: `metager3.de`
     * does not literally equal `metager.de`, so the host check silently
     * treats every non-production instance as English-first; and even on
     * production, an `Accept-Language` other than German is ignored by
     * design unless it *also* guesses German (`metager.de` is the
     * German-first domain on purpose - `metager.org` is the English one).
     * A client that already knows its own language, like this one, has no
     * way to say so through any of that.
     *
     * Every language MetaGer itself ships a `lang/` directory for is
     * accepted ([LANG_TO_LOCALE]) - not just the three this mobile app
     * currently speaks (`docs/09-roadmap.md` Phase 9 / D48) - since this is
     * a general headless-client endpoint, not a mobile-app-only one, and a
     * future client (or a future version of this app) asking for e.g. `fr`
     * should not need another backend change to get it. Unlike the fields
     * above, an unrecognised or absent `lang` does not fall through to the
     * guess - it resolves to English, deliberately: a predictable default
     * for an API consumer, not "whatever the page-oriented heuristic
     * happens to guess", which is the exact failure mode this parameter
     * exists to avoid.
     */
    public function schema(Request $request)
    {
        $locale = self::LANG_TO_LOCALE[$request->query("lang")] ?? self::LANG_TO_LOCALE["en"];
        LaravelLocalization::setLocale($locale);
        App::setLocale($locale);

        $registry = app(SearchEngineRegistry::class);
        // Fully qualified rather than a second `use` - this file already imports
        // `App\Models\Configuration\Searchengines` (the per-request, per-fokus
        // engine list) under the same short name. This is the *other* one: a
        // pure function of the locale just set above, with no auth/payment/fokus
        // state of its own - the same one `index.blade.php`/`foki.blade.php` use
        // to decide which fokus tabs the website itself shows for this language.
        $availableFoki = app(\App\Searchengines::class)->available_foki;

        $global = array_map(function ($setting) {
            return [
                "key" => $setting["key"],
                "type" => $setting["type"],
                "default" => $setting["default"] ?? null,
                "values" => isset($setting["values"]) ? array_map(fn($option) => [
                    "value" => $option["value"],
                    "label" => $option["translate"] ? trans($option["label"]) : $option["label"],
                    // Only `suggestion_provider`'s values set this (SettingsSchema::suggestionProviderValues) -
                    // every other global setting's options have no per-choice cost, so this is
                    // null for them rather than an omitted key, matching a per-engine cost of 0
                    // being sent explicitly rather than left absent below.
                    "cost" => $option["cost"] ?? null,
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
                // `disabled`/`disabledByDefault` are not on the registry's raw merged
                // config - `disabled` there is only ever the hardcoded CONFIG_OVERLOAD
                // value (e.g. Yandex, retired), and `disabledByDefault` (e.g. Mojeek) is
                // set imperatively in the parser class's own constructor, the same way
                // Searchengines::__construct() discovers it. Constructing the parser
                // instance here - without running Searchengines' later request-context
                // disabling (payment/ads/locale/filter) - gets the same two static facts
                // fokus-section.blade.php uses to decide what the website's own settings
                // page renders at all, without baking in this request's auth/locale state
                // into a schema response meant to be cached for the whole app run.
                $engineConfig = $registry->sumas->{$engineName};
                $parserClass = "App\\Models\\parserSkripte\\" . $engineConfig->{"parser-class"};
                $engine = new $parserClass($engineName, new SearchengineConfiguration($engineConfig));
                // Permanently disabled at the config level (sanctions, retired
                // integrations) - never offered as a toggle, matching
                // fokus-section.blade.php's own handling of this exact case.
                if ($engine->configuration->disabled) {
                    continue;
                }
                $engines[] = [
                    "name" => $engineName,
                    "displayName" => $engineConfig->infos->display_name ?? $engineName,
                    "settingKey" => "{$fokus}_engine_{$engineName}",
                    // Per-search-engine token cost, straight from its CONFIG_OVERLOAD
                    // (SearchengineConfiguration.php defaults this to 0 when a parser
                    // class doesn't set one, so this mirrors that rather than inventing
                    // a new default). What a client does with several enabled engines'
                    // costs - summed, floored to a minimum of 1 token per search - is
                    // Searchengines::getSearchCost()'s rule, deliberately not
                    // recomputed here: it is a static fact about one engine, not a
                    // live quote for a particular combination, so headless clients
                    // (the mobile app) apply the same floor themselves once they know
                    // which engines are enabled.
                    "cost" => $engineConfig->cost ?? 0,
                    // Whether a fresh install should have this engine switched on.
                    // Mirrors what the website's own settings page shows as an
                    // already-off toggle (e.g. Mojeek) rather than defaulting
                    // everything to on and leaving a client to guess.
                    "enabledByDefault" => !$engine->configuration->disabledByDefault,
                    // Whether this engine can serve the requested language at all.
                    // Engines are indexed per language, not globally: `onenewspage`
                    // only covers English, `onenewspagegermany` only German. A search
                    // already drops the ones that cannot serve the current locale
                    // (`SearchengineConfiguration::applyLocale()`), so without this a
                    // client would show a toggle with nothing behind it — and for
                    // `onenewspage` on a German device, one that implied German news
                    // came out of an English-only index.
                    //
                    // Additive like the fokus-level `available` above, and for the
                    // same reason (SettingsSchemaAvailableFokiTest): a client that
                    // already had this engine switched off still needs to see it to
                    // manage it. The same test `applyLocale()` makes — a supported
                    // language may map to the empty string as its parameter value, so
                    // only `null` means "cannot serve this locale".
                    "available" => $engine->configuration->languages?->getParameterForLocale() !== null,
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
                // Whether at least one of this fokus's searchengines supports
                // the requested language/region - the same test the website's
                // own fokus tabs use ($availableFoki above). Additive rather
                // than omitting the fokus outright: a client can still choose
                // to expose the engine/filter settings below for a fokus it
                // otherwise hides, e.g. while the user has it selected from
                // before a language change.
                "available" => in_array($fokus, $availableFoki, true),
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
        app(SettingsCarry::class)->forget($key);

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
            app(SettingsCarry::class)->forget($key);
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
                app(SettingsCarry::class)->forget($key);
            }
        }

        $valid_blacklist_entries = array_unique($valid_blacklist_entries);
        sort($valid_blacklist_entries);

        $cookieName = $fokus . '_blpage';
        if (sizeof($valid_blacklist_entries) === 0) {
            // A blacklist with nothing left in it is not a setting with an
            // empty value, it is no setting — and the difference is visible.
            // An empty cookie still counts as something set, so "reset all
            // settings" stays on a page where nothing is; and the webextension
            // only forgets a setting it is told to forget, so an empty value is
            // a value it keeps attaching to every request. Deleting the last
            // entry of a blacklist came straight back on the next page load.
            Cookie::queue(Cookie::forget($cookieName, "/"));
            app(SettingsCarry::class)->forget($cookieName);
        } else {
            $secure = app()->environment("local") ? false : true;
            Cookie::queue(Cookie::forever($cookieName, implode(",", $valid_blacklist_entries), "/", null, $secure, true));
            app(SettingsCarry::class)->set($cookieName, implode(",", $valid_blacklist_entries));
        }


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
        app(SettingsCarry::class)->forget($cookieKey);

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
        $settings = Cookie::get();
        if ($request->wantsJson()) {
            // The webextension keeps the settings in its own storage and sends
            // them as request headers, so there is no cookie here to find and
            // forget — and a setting nothing forgets is one it never hears
            // about, because what it acts on is the list of expired cookies in
            // the response. deleteSettings() already merges them in for exactly
            // this reason; this method was the exception, so "reset all
            // settings" cleared every engine and filter and left the blacklist.
            // Symfony normalises a header name's underscores to dashes.
            foreach ($request->header() as $key => $value) {
                $settings[str_replace("-", "_", $key)] = $value;
            }
        }

        // Also empty for a cookie-blind visitor for the same reason as
        // above — nothing ever stuck as a cookie.
        $settings = array_merge($settings, app(SettingsCarry::class)->all());

        foreach ($settings as $key => $value) {
            if (stripos($key, $fokus . '_blpage') === 0) {
                Cookie::queue(Cookie::forget($key, "/"));
                app(SettingsCarry::class)->forget($key);
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

        // The cookies just queued above may not survive the round trip — this
        // is the mechanism a visitor whose cookies never stuck in the first
        // place actually uses to get back in (SettingsController::index()'s
        // $cookieLink), so it gets the same one-hop marker as sign-in and key
        // creation. Only when this request actually carried a key: a
        // settings-only sync (no key among $settings) has nothing to check.
        // Left off the redirect_url branch below on purpose — a validated
        // signed redirect_url is a different, already-authenticated flow
        // (e.g. SafeBrowse), not part of this one.
        if (isset($params_for_startpage["key"])) {
            $url = CookieSupport::withKeyCheck($url, $params_for_startpage["key"]);
        }

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