<?php

namespace App\Models;

use App\Localization;
use App\Models\DisabledReason;
use LaravelLocalization;
use \Log;

class SearchengineConfiguration
{
    /** @var string */
    public $host;
    /** @var string */
    public $path;
    /** @var int */
    public $port;
    /** @var string */
    public $queryParameter;
    /** @var string */
    public $inputEncoding;
    /** @var string */
    public $outputEncoding;
    /** @var string */
    public $httpAuthUsername;
    /** @var string */
    public $httpAuthPassword;
    /** @var object */
    public $getParameter;
    /** @var SearchEngineLanguages */
    public $languages;
    /** @var object */
    public $requestHeader;
    /** @var float */
    public $engineBoost = 1.0;
    /** @var int */
    public $cacheDuration = 60;
    /** @var int */
    public $cost = 0;
    /** @var bool */
    public $disabled = false;
    /** @var DisabledReason */
    public $disabledReasons = [];
    /** @var bool */
    public $disabledByDefault = false;
    /** @var bool */
    public $ads = false;
    /** @var bool */
    public $filterOptIn = false;
    /** @var int */
    public $monthlyRequests;
    /** @var SearchEngineInfos */
    public $infos;
    /**
     * Additional curlopts which should be set for the query
     * @var array
     */
    public array $curl_opts = [];
    /**
     * @param object $engineConfigurationJson
     */
    public function __construct($engineConfigurationJson)
    {
        try {
            /** Required parameters from json file */
            $this->host = $engineConfigurationJson->host;
            $this->path = $engineConfigurationJson->path;
            $this->port = $engineConfigurationJson->port;
            $this->queryParameter = $engineConfigurationJson->{"query-parameter"};
            $this->inputEncoding = $engineConfigurationJson->{"input-encoding"};
            $this->outputEncoding = $engineConfigurationJson->{"output-encoding"};

            /** 
             * optional parameters fo here 
             * might get overriden in Searchengine implementation constructor
             * */
            if (
                property_exists($engineConfigurationJson, "http-auth-credentials") &&
                is_object($engineConfigurationJson->{"http-auth-credentials"}) &&
                property_exists($engineConfigurationJson->{"http-auth-credentials"}, "username") &&
                property_exists($engineConfigurationJson->{"http-auth-credentials"}, "password")
            ) {
                $this->setHttpAuth($engineConfigurationJson->{"http-auth-credentials"}->username, $engineConfigurationJson->{"http-auth-credentials"}->password);
            }
            if (property_exists($engineConfigurationJson, "get-parameter") && is_object($engineConfigurationJson->{"get-parameter"})) {
                $this->addQueryParameters($engineConfigurationJson->{"get-parameter"});
            } else {
                $this->addQueryParameters([]);
            }
            if (property_exists($engineConfigurationJson, "lang")) {
                $this->setLanguages($engineConfigurationJson->lang->parameter, $engineConfigurationJson->lang->languages, $engineConfigurationJson->lang->regions);
            }
            if (property_exists($engineConfigurationJson, "request-header")) {
                $this->addRequestHeaders($engineConfigurationJson->{"request-header"});
            } else {
                $this->addRequestHeaders([]);
            }
            if (property_exists($engineConfigurationJson, "engine-boost"))
                $this->engineBoost = $engineConfigurationJson->{"engine-boost"};
            if (property_exists($engineConfigurationJson, "cache-duration") && $engineConfigurationJson->{"cache-duration"} > -1) {
                $this->cacheDuration = max($engineConfigurationJson->{"cache-duration"}, 5);
            }
            if (property_exists($engineConfigurationJson, "disabled")) {
                $this->disabled = $engineConfigurationJson->disabled;
            }
            if ($this->disabled) {
                $this->disabledReasons[] = DisabledReason::SUMAS_CONFIGURATION;
            }
            if (property_exists($engineConfigurationJson, "filter-opt-in"))
                $this->filterOptIn = $engineConfigurationJson->{"filter-opt-in"};
            if (property_exists($engineConfigurationJson, "monthly-requests")) {
                $this->monthlyRequests = $engineConfigurationJson->{"monthly-requests"};
            }
            if (property_exists($engineConfigurationJson, "ads")) {
                $this->ads = $engineConfigurationJson->ads;
            }
            if (property_exists($engineConfigurationJson, "infos")) {
                $infos = $engineConfigurationJson->infos;
                $homepage = property_exists($infos, "homepage") ? $infos->homepage : "";
                $index_name = property_exists($infos, "index_name") ? $infos->index_name : "";
                $display_name = property_exists($infos, "display_name") ? $infos->display_name : "";
                $founded = property_exists($infos, "founded") ? $infos->founded : "";
                $headquarter = property_exists($infos, "headquarter") ? $infos->headquarter : "";
                $operator = property_exists($infos, "operator") ? $infos->operator : "";
                $index_size = property_exists($infos, "index_size") ? $infos->index_size : "";
                $this->infos = new SearchEngineInfos($homepage, $index_name, $display_name, $founded, $headquarter, $operator, $index_size);
            }
            if (property_exists($engineConfigurationJson, "cost"))
                $this->cost = $engineConfigurationJson->cost;

        } catch (\Exception $e) {
            $this->disabled = true;
            $this->disabledReasons[] = DisabledReason::SUMAS_CONFIGURATION;
            Log::error($e->getTraceAsString());
        }
    }

    public function applyLocale()
    {
        $key = $this->languages->getParameter;
        $value = $this->languages->getParameterForLocale();
        if ($value !== null) {
            $this->getParameter->{$key} = $value;
        } else {
            $this->disabled = true;
            $this->disabledReasons[] = DisabledReason::INCOMPATIBLE_LOCALE;
        }
    }

    public function setHttpAuth(string $username, string $password)
    {
        if (empty($username) || empty($password))
            return;
        $this->httpAuthUsername = $username;
        $this->httpAuthPassword = $password;
    }

    /**
     * Configures supported languages for this searchengine
     * @param string $getParameter query parameter key used by the searchengine to switch the language
     * @param object|array $languages associates specific two letter language codes from MetaGer with a given query paramaeter value
     * @param object|array $regions associates specific four letter language/region (i.e. de_DE) combinations with a given query parameter value
     */
    public function setLanguages(string $getParameter, object|array $languages, object|array $regions)
    {
        if (is_array($languages)) {
            // Languages supplied as array: convert to an object
            $languages = json_decode(json_encode($languages, JSON_FORCE_OBJECT), false);
        }
        if (is_array($regions)) {
            // Languages supplied as array: convert to an object
            $regions = json_decode(json_encode($regions, JSON_FORCE_OBJECT), false);
        }
        $this->languages = new SearchEngineLanguages($getParameter, $languages, $regions);
    }

    public function addRequestHeaders(object|array $requestHeader)
    {
        if (is_array($requestHeader)) {
            $requestHeader = json_decode(json_encode($requestHeader, JSON_FORCE_OBJECT), false);
        }
        if ($this->requestHeader === null) {
            $this->requestHeader = new \stdClass;
        }
        $this->requestHeader = (object) array_merge((array) $requestHeader, (array) $this->requestHeader);
    }

    public function addQueryParameters(object|array $queryParameters)
    {
        if (is_array($queryParameters)) {
            $queryParameters = json_decode(json_encode($queryParameters, JSON_FORCE_OBJECT), false);
        }
        if ($this->getParameter === null) {
            $this->getParameter = new \stdClass;
        }
        $this->getParameter = (object) array_merge((array) $queryParameters, (array) $this->getParameter);
    }

    public function applyQuery(string $query)
    {
        $this->getParameter->{$this->queryParameter} = $query;
    }
}

class SearchEngineLanguages
{

    /** @var string */
    public $getParameter;
    /** @var object */
    private $languages;
    /** @var object */
    private $regions;

    public function __construct(string $getParameter, object $languages, object $regions)
    {
        $this->getParameter = $getParameter;
        $this->languages = $languages;
        $this->regions = $regions;
    }

    public function getParameterForLocale()
    {
        $locale = LaravelLocalization::getCurrentLocaleRegional();
        $language = Localization::getLanguage();
        if (\property_exists($this->regions, $locale)) {
            return $this->regions->{$locale};
        } elseif (\property_exists($this->languages, $language)) {
            return $this->languages->{$language};
        }
        return null;
    }
}

class SearchEngineInfos
{

    /** @var string */
    public $homepage;
    /** @var string */
    public $indexName;
    /** @var string */
    public $displayName;
    /** @var string */
    public $founded;
    /** @var string */
    public $headquarter;
    /** @var string */
    public $operator;
    /** @var string */
    public $indexSize;

    public function __construct(string $homepage = null, string $index_name = null, string $display_name = null, string $founded = null, string $headquarter = null, string $operator = null, string $index_size = null)
    {
        $this->homepage = $homepage;
        $this->indexName = $index_name;
        $this->displayName = $display_name;
        $this->founded = $founded;
        $this->headquarter = $headquarter;
        $this->operator = $operator;
        $this->indexSize = $index_size;
    }
}