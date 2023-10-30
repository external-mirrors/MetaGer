<?php

namespace App;

use App\Models\Configuration\Searchengines;
use App\Models\ParameterFilters\Safesearch;
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
        $this->q     = trim(Request::input('eingabe', ''));
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

        $self_advertisements       = Cookie::get("self_advertisements", true);
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
        $this->parameterFilter[Safesearch::class] = new Safesearch();
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