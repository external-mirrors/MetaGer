<?php

namespace App;

use App\Models\Configuration\Searchengines;
use Cookie;
use LaravelLocalization;
use \Request;

class SearchSettings
{

    public $bv_key = null; // Cache Key where data of BV is temporarily stored
    public $javascript_enabled = false;
    /** @var string */
    public $q;
    /** @var string */
    public $fokus;
    public $available_foki = [];
    public $newtab = false;
    public $zitate = true;
    public $blacklist = [];
    public $blacklist_tld = [];
    public $page = 1;
    public $queryFilter = [];
    public $parameterFilter = [];
    /** @var object */
    public $sumasJson;
    public $quicktips = true;
    public $theme = "system";    // Darkmode setting currently either one of 'system', 'light', 'dark'
    public $enableQuotes = true;
    /** @var bool */
    public $self_advertisements;
    /** @var bool */
    public $tiles_startpage;
    /** @var string */
    public $suggestions = "bing";
    public $external_image_search = "metager";

    public $user_settings = []; // Stores user settings that are parsed
    private $ignore_user_settings = ["js_available"];
    public function __construct()
    {

    }

    /**
     * Initializes Settings that depend on Localization which
     * needs to be deferred as Localization is done in another ServiceProvider
     */
    public function boot()
    {
        $this->sumasJson = json_decode(file_get_contents(config_path("sumas.json")));
        if ($this->sumasJson === null) {
            throw new \Exception("Cannot load sumas.json file");
        }
        $this->q = trim(Request::input('eingabe', ''));
        $this->fokus = Request::input("focus", "web");

        if (!in_array($this->fokus, array_merge(array_keys((array) $this->sumasJson->foki), ["maps"]))) {
            $this->fokus = "web";
        }

        // Make sure sumas definition for current fokus exists
        if (!property_exists($this->sumasJson->foki, $this->fokus)) {
            $this->sumasJson->foki->{$this->fokus} = new \stdClass;
            $this->sumasJson->foki->{$this->fokus}->sumas = [];
        }

        $this->available_foki = array_keys(get_object_vars($this->sumasJson->foki));

        $this->user_settings = [];

        $this->javascript_enabled = filter_var($this->getSettingValue("js_available", false), FILTER_VALIDATE_BOOLEAN);

        if (Localization::getLanguage() !== "de" || $this->getSettingValue("zitate", "on") === "off") {
            $this->enableQuotes = false;
        }

        $this->self_advertisements = $this->getSettingValue("self_advertisements", true);
        $this->self_advertisements = $this->self_advertisements !== "off" ? true : false;

        $this->tiles_startpage = $this->getSettingValue("tiles_startpage", true);
        $this->tiles_startpage = $this->tiles_startpage !== "off" ? true : false;

        $suggestions = $this->getSettingValue("suggestions", "bing");
        if ($suggestions === "off") {
            $this->suggestions = "off";
        }

        if ($this->getSettingValue("quicktips") !== null) {
            $this->quicktips = false;
        }
        $this->theme = $this->getSettingValue("dark_mode", "system");
        if ($this->theme === "1")
            $this->theme = "light";
        else if ($this->theme === "2")
            $this->theme = "dark";
        else
            $this->theme = "system";
        $newtab = $this->getSettingValue("new_tab", false);
        switch ($newtab) {
            case "on":
                $this->newtab = true;
                break;
            default:
                $this->newtab = false;
        }
        $zitate = $this->getSettingValue("zitate", Localization::getLanguage() === "de" ? "on" : "off");
        if ($zitate === "on")
            $this->zitate = true;
        else
            $this->zitate = false;

        $external_image_search = $this->getSettingValue("bilder_setting_external", "metager");
        if (in_array($external_image_search, ["metager", "bing", "google"])) {
            $this->external_image_search = $external_image_search;
        } else {
            $this->external_image_search = "metager";
        }

        // Parse the blacklist
        $blacklist_string = $this->getSettingValue($this->fokus . "_blpage");
        if ($blacklist_string !== null) {
            $blacklist_string = substr($blacklist_string, 0, 2048);

            // Split the blacklist by all sorts of newlines
            $blacklist = preg_split('/,/', $blacklist_string);

            foreach ($blacklist as $blacklist_entry) {
                if (!preg_match('/^https?:\/\//', $blacklist_entry)) {
                    $blacklist_entry = "https://" . $blacklist_entry;
                }
                // Only use hostname from url
                $blacklist_entry = parse_url($blacklist_entry, PHP_URL_HOST);
                if ($blacklist_entry === null || $blacklist_entry === false)
                    continue;
                $blacklist_entry = substr($blacklist_entry, 0, 255);

                if (stripos($blacklist_entry, "*.") === 0) {
                    $this->blacklist_tld[] = str_replace("*.", "", $blacklist_entry);
                } else {
                    $this->blacklist[] = $blacklist_entry;
                }
            }
        }

        $this->blacklist = array_unique($this->blacklist);
        sort($this->blacklist);

        $this->blacklist_tld = array_unique($this->blacklist_tld);
        sort($this->blacklist_tld);

        foreach ($this->ignore_user_settings as $ignored_key) {
            unset($this->user_settings[$ignored_key]);
        }
    }

    public function loadQueryFilter()
    {
        foreach ($this->sumasJson->filter->{"query-filter"} as $filterName => $filter) {
            if (!empty($filter->{"optional-parameter"}) && Request::filled($filter->{"optional-parameter"})) {
                $this->queryFilter[$filterName] = Request::input($filter->{"optional-parameter"});
            } elseif (preg_match_all("/" . $filter->regex . "/si", $this->q, $matches) > 0) {
                switch ($filter->match) {
                    case "last":
                        $this->queryFilter[$filterName] = $matches[$filter->save][sizeof($matches[$filter->save]) - 1];
                        $toDelete = preg_quote($matches[$filter->delete][sizeof($matches[$filter->delete]) - 1], "/");
                        $this->q = preg_replace('/(' . $toDelete . '(?!.*' . $toDelete . '))/si', '', $this->q);
                        break;
                    default:
                        $this->queryFilter[$filterName] = $matches[$filter->save][0];
                        $toDelete = preg_quote($matches[$filter->delete][0], "/");
                        $this->q = preg_replace('/' . $toDelete . '/si', '', $this->q, 1);
                }
            }
        }
    }

    public function loadParameterFilter(Searchengines $searchengines)
    {
        foreach ($this->sumasJson->filter->{"parameter-filter"} as $filterName => $filter) {
            // Do not add filter if not available for current focus
            if (sizeof(array_intersect(array_keys((array) $filter->sumas), $this->sumasJson->foki->{$this->fokus}->sumas)) === 0) {
                continue;
            }
            $this->parameterFilter[$filterName] = $filter;
            if ($filterName === "language") {
                // Update default Parameter for language
                $current_locale = LaravelLocalization::getCurrentLocaleRegional();
                $this->parameterFilter["language"]->{"default-value"} = $current_locale;
            }
            if (!property_exists($filter, "default-value")) {
                $this->parameterFilter[$filterName]->{"default-value"} = "nofilter";
            }
            $parameter_filter_value = $this->getSettingValue($filter->{"get-parameter"});
            if ($parameter_filter_value === "off")
                $parameter_filter_value = null;
            if ($parameter_filter_value === $this->parameterFilter[$filterName]->{"default-value"}) {
                $parameter_filter_value = null;
                unset(app(\Illuminate\Http\Request::class)[$filter->{"get-parameter"}]);
            }
            $this->parameterFilter[$filterName]->value = $parameter_filter_value;

            // Check if any options will be disabled
            $this->parameterFilter[$filterName]->{"disabled-values"} = [];
            $enabledValues = [];
            $disabledValues = [];
            foreach ($this->parameterFilter[$filterName]->sumas as $name => $options) {
                if (!in_array($name, (array) $this->sumasJson->foki->{$this->fokus}->sumas)) {
                    continue;
                }
                foreach ($options->values as $value => $sumaValue) {
                    if ($searchengines->sumas[$name]->configuration->disabled === true && !in_array($value, $enabledValues)) {
                        if (!array_key_exists($value, $disabledValues)) {
                            $disabledValues[$value] = [];
                        }
                        $disabledValues[$value] = array_merge($searchengines->sumas[$name]->configuration->disabledReasons, $disabledValues[$value]);
                    }
                    if (!$searchengines->sumas[$name]->configuration->disabled && !in_array($value, $enabledValues)) {
                        $enabledValues[] = $value;
                        if (array_key_exists($value, $disabledValues)) {
                            unset($disabledValues[$value]);
                        }
                    }
                }
            }
            $this->parameterFilter[$filterName]->{"disabled-values"} = $disabledValues;
        }
    }
    public function isParameterFilterSet()
    {
        foreach ($this->parameterFilter as $filterName => $filter) {
            if ($filter->value !== null) {
                return true;
            }
        }
        return false;
    }
    public function isTemporaryParameterFilterSet()
    {
        foreach ($this->parameterFilter as $filterName => $filter) {
            if (
                Request::filled($filter->{"get-parameter"})
                && Cookie::get($this->fokus . "_setting_" . $filter->{"get-parameter"}) !== Request::input($filter->{"get-parameter"})
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parses the current request and checks if the specified setting is defined in the following order:
     * 1. GET-Parameter
     * 2. HTTP Header with that name
     * 3. Cookie 
     * 
     * @param string $setting_name The name of the setting
     * @param bool $global (Optional) Is this setting global or specific to a focus
     * @param bool|string|null $default (Optional) Default value to return if setting is not defined anywhere
     * @return string|null
     */
    private function getSettingValue(string $setting_name, $default = null): string|null
    {
        /**
         * Check GET-Parameter in all variations
         */
        // Setting defined directly in GET Parameters
        if (Request::filled($setting_name)) {
            $value = Request::input($setting_name, $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }
        // Setting defined without fokus prefix which will be handled as matching all foki
        if (stripos($setting_name, $this->fokus . "_setting_") === 0 && Request::filled(str_replace($this->fokus . "_setting_", "", $setting_name))) {
            $value = Request::input(str_replace($this->fokus . "_setting_", "", $setting_name), $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }
        // Setting defined with fokus prefix in request parameters and fokus matches currently used one
        if (stripos($setting_name, $this->fokus . "_setting_") === false && Request::filled($this->fokus . "_setting_" . $setting_name)) {
            $value = Request::input($this->fokus . "_setting_" . $setting_name, $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }

        /**
         * Check Request HTTP Header in all variations
         */
        // Setting defined directly in GET Parameters
        if (Request::hasHeader($setting_name)) {
            $value = Request::header($setting_name, $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }
        // Setting defined without fokus prefix which will be handled as matching all foki
        if (stripos($setting_name, $this->fokus . "_setting_") === 0 && Request::hasHeader(str_replace($this->fokus . "_setting_", "", $setting_name))) {
            $value = Request::header(str_replace($this->fokus . "_setting_", "", $setting_name), $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }
        // Setting defined with fokus prefix in request parameters and fokus matches currently used one
        if (stripos($setting_name, $this->fokus . "_setting_") === false && Request::hasHeader($this->fokus . "_setting_" . $setting_name)) {
            $value = Request::header($this->fokus . "_setting_" . $setting_name, $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }

        /**
         * Check Cookies in all variations
         */
        // Setting defined directly in GET Parameters
        if (Cookie::has($setting_name)) {
            $value = Cookie::get($setting_name, $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }
        // Setting defined without fokus prefix which will be handled as matching all foki
        if (stripos($setting_name, $this->fokus . "_setting_") === 0 && Cookie::has(str_replace($this->fokus . "_setting_", "", $setting_name))) {
            $value = Cookie::get(str_replace($this->fokus . "_setting_", "", $setting_name), $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }
        // Setting defined with fokus prefix in request parameters and fokus matches currently used one
        if (stripos($setting_name, $this->fokus . "_setting_") === false && Cookie::has($this->fokus . "_setting_" . $setting_name)) {
            $value = Cookie::get($this->fokus . "_setting_" . $setting_name, $default);
            $this->user_settings[$setting_name] = $value;
            return $value;
        }

        return $default;
    }
}