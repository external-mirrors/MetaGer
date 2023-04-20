<?php

namespace App;

use App\Models\Authorization\Authorization;
use Cookie;
use Request;

class SearchSettings
{

    public $bv_key = null; // Cache Key where data of BV is temporarily stored
    public $javascript_enabled = false;
    /** @var string */
    public $q;
    /** @var string */
    public $fokus;
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
        $this->fokus = Request::input("fokus", "web");
        if (!in_array($this->fokus, ["web", "bilder", "produkte", "nachrichten", "science"])) {
            $this->fokus = "web";
        }
        $this->loadQueryFilter();
        $this->loadParameterFilter();
    }

    private function loadQueryFilter()
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

    private function loadParameterFilter()
    {
        $authorized = app(Authorization::class)->canDoAuthenticatedSearch();
        foreach ($this->sumasJson->filter->{"parameter-filter"} as $filterName => $filter) {
            $this->parameterFilter[$filterName] = $filter;
            if (
                (Request::filled($filter->{"get-parameter"}) && Request::input($filter->{"get-parameter"}) !== "off") ||
                Cookie::get($this->fokus . "_setting_" . $filter->{"get-parameter"}) !== null
            ) { // If the filter is set via Cookie

                $this->parameterFilter[$filterName]->value = Request::input($filter->{"get-parameter"}, '');
                if (empty($this->parameterFilter[$filterName]->value)) {
                    $this->parameterFilter[$filterName]->value = Cookie::get($this->fokus . "_setting_" . $filter->{"get-parameter"});
                }
            } else {
                $this->parameterFilter[$filterName]->value = null;
            }
            // Check if any options will be disabled
            $this->parameterFilter[$filterName]->{"disabled-values"} = [];
            if (!$authorized) {
                $free_to_use_values = [];
                foreach ($this->parameterFilter[$filterName]->sumas as $name => $options) {
                    if (!in_array($this->fokus, $this->sumasJson->foki->{$this->fokus}->sumas)) {
                        continue;
                    }
                    foreach ($options->values as $value => $sumaValue) {
                        if ($this->sumasJson->sumas->$name->cost === 0) {
                            $free_to_use_values[] = $value;
                        }
                    }
                }
                $test = "test";
            }
        }

    }
}