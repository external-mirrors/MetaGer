<?php

namespace App;

use App\Models\Authorization\Authorization;
use App\Models\Configuration\Searchengines;
use App\Models\DisabledReason;
use Cookie;
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

    public function __construct()
    {
        $this->sumasJson = json_decode(file_get_contents(config_path("sumas.json")));
        if ($this->sumasJson === null) {
            throw new \Exception("Cannot load sumas.json file");
        }
        $this->q = trim(Request::input('eingabe', ''));
        $this->fokus = Request::input("focus", "web");
        if (!in_array($this->fokus, ["web", "bilder", "produkte", "nachrichten", "science"])) {
            $this->fokus = "web";
        }
    }

    public function loadQueryFilter()
    {
        # Check for query-filter (i.e. Sitesearch, etc.):
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
                    default: # First occurence
                        $this->queryFilter[$filterName] = $matches[$filter->save][0];
                        $toDelete = preg_quote($matches[$filter->delete][0], "/");
                        $this->q = preg_replace('/' . $toDelete . '/si', '', $this->q, 1);
                }
            }
        }
    }

    public function loadParameterFilter(Searchengines $searchengines)
    {
        $authorized = app(Authorization::class)->canDoAuthenticatedSearch();
        foreach ($this->sumasJson->filter->{"parameter-filter"} as $filterName => $filter) {
            // Do not add filter if not available for current focus
            if (sizeof(array_intersect(array_keys((array) $filter->sumas), $this->sumasJson->foki->{$this->fokus}->sumas)) === 0) {
                continue;
            }

            $this->parameterFilter[$filterName] = $filter;
            if (
                (Request::filled($filter->{"get-parameter"}) && Request::input($filter->{"get-parameter"}) !== "off") ||
                Cookie::get($this->fokus . "_setting_" . $filter->{"get-parameter"}) !== null
            ) { // If the filter is set via Cookie

                $this->parameterFilter[$filterName]->value = Request::input($filter->{"get-parameter"}, null);

                if (empty($this->parameterFilter[$filterName]->value)) {
                    $this->parameterFilter[$filterName]->value = Cookie::get($this->fokus . "_setting_" . $filter->{"get-parameter"});
                }
                if ($this->parameterFilter[$filterName]->value === "off") {
                    $this->parameterFilter[$filterName]->value = null;
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