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
    public $page = 1;
    public $queryFilter = [];
    public $parameterFilter = [];
    /** @var object */
    public $sumasJson;
    public $quicktips = true;
    public $enableQuotes = true;
    /** @var bool */
    public $self_advertisements;
    /** @var string */
    public $suggestions = "bing";

    public function __construct()
    {
        $this->sumasJson = json_decode(file_get_contents(config_path("sumas.json")));
        if ($this->sumasJson === null) {
            throw new \Exception("Cannot load sumas.json file");
        }
        $this->q = trim(Request::input('eingabe', ''));
        $this->fokus = Request::input("focus", "web");

        if (!in_array($this->fokus, array_keys((array) $this->sumasJson->foki))) {
            $this->fokus = "web";
        }

        if (Cookie::has("js_available") && Cookie::get("js_available") === "true") {
            $this->javascript_enabled = true;
        }

        if (Cookie::has("zitate") && Cookie::get("zitate") === "off") {
            $this->enableQuotes = false;
        }

        $self_advertisements = Cookie::get("self_advertisements", true);
        $this->self_advertisements = $self_advertisements !== "off" ? true : false;

        $suggestions = Cookie::get("suggestions", "bing");
        if ($suggestions === "off") {
            $this->suggestions = "off";
        }

        if (Request::filled('quicktips')) {
            $this->quicktips = false;
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
            if (
                (Request::filled($filter->{"get-parameter"}) && Request::input($filter->{"get-parameter"}) !== "off") ||
                Cookie::get($this->fokus . "_setting_" . $filter->{"get-parameter"}) !== null
            ) {
                $this->parameterFilter[$filterName]->value = Request::input($filter->{"get-parameter"}, null);

                if (empty($this->parameterFilter[$filterName]->value)) {
                    $this->parameterFilter[$filterName]->value = Cookie::get($this->fokus . "_setting_" . $filter->{"get-parameter"});
                }
                if (
                    $this->parameterFilter[$filterName]->value === "off"
                ) {
                    $this->parameterFilter[$filterName]->value = null;
                }
                if ($this->parameterFilter[$filterName]->value === $this->parameterFilter[$filterName]->{"default-value"}) {
                    $this->parameterFilter[$filterName]->value = null;
                    unset(app(\Illuminate\Http\Request::class)[$filter->{"get-parameter"}]);
                }
            } else {
                $this->parameterFilter[$filterName]->value = null;
            }
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
                        $disabledValues[$value][] = $searchengines->sumas[$name]->configuration->disabledReason;
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
}