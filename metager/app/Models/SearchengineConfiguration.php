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
    public $engineBoost;
    /** @var int */
    public $cacheDuration = 60;
    /** @var int */
    public $cost = 0;
    /** @var bool */
    public $disabled;
    /** @var DisabledReason */
    public $disabledReason;
    /** @var bool */
    public $disabledByDefault = false;
    /** @var bool */
    public $ads = false;
    /** @var bool */
    public $filterOptIn;
    /** @var int */
    public $monthlyRequests;
    /** @var SearchEngineInfos */
    public $infos;
    /**
     * @param object $engineConfigurationJson
     */
    public function __construct($engineConfigurationJson)
    {
        try {
            $this->host = $engineConfigurationJson->host;
            $this->path = $engineConfigurationJson->path;
            $this->port = $engineConfigurationJson->port;
            $this->queryParameter = $engineConfigurationJson->{"query-parameter"};
            $this->inputEncoding = $engineConfigurationJson->{"input-encoding"};
            $this->outputEncoding = $engineConfigurationJson->{"output-encoding"};
            if (
                is_object($engineConfigurationJson->{"http-auth-credentials"}) &&
                property_exists($engineConfigurationJson->{"http-auth-credentials"}, "username") &&
                property_exists($engineConfigurationJson->{"http-auth-credentials"}, "password")
            ) {
                $this->httpAuthUsername = $engineConfigurationJson->{"http-auth-credentials"}->username;
                $this->httpAuthPassword = $engineConfigurationJson->{"http-auth-credentials"}->password;
            }
            $this->getParameter = $engineConfigurationJson->{"get-parameter"};
            $this->languages = new SearchEngineLanguages($engineConfigurationJson->lang);
            $this->requestHeader = $engineConfigurationJson->{"request-header"};
            $this->engineBoost = $engineConfigurationJson->{"engine-boost"};
            if ($engineConfigurationJson->{"cache-duration"} > -1) {
                $this->cacheDuration = max($engineConfigurationJson->{"cache-duration"}, 5);
            }
            $this->disabled = $engineConfigurationJson->disabled;
            if ($this->disabled) {
                $this->disabledReason = DisabledReason::SUMAS_CONFIGURATION;
            }
            $this->filterOptIn = $engineConfigurationJson->{"filter-opt-in"};
            if (property_exists($engineConfigurationJson, "monthly-requests")) {
                $this->monthlyRequests = $engineConfigurationJson->{"monthly-requests"};
            }
            if (property_exists($engineConfigurationJson, "ads")) {
                $this->ads = $engineConfigurationJson->ads;
            }
            $this->infos = new SearchEngineInfos($engineConfigurationJson->infos);
            $this->cost = $engineConfigurationJson->cost;

        } catch (\Exception $e) {
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
            $this->disabledReason = DisabledReason::INCOMPATIBLE_LOCALE;
        }
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

    public function __construct(object $langJson)
    {
        $this->getParameter = $langJson->parameter;
        $this->languages = $langJson->languages;
        $this->regions = $langJson->regions;
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

    public function __construct(object $langJson)
    {
        $this->homepage = $langJson->homepage;
        $this->indexName = $langJson->index_name;
        $this->displayName = $langJson->display_name;
        $this->founded = $langJson->founded;
        $this->headquarter = $langJson->headquarter;
        $this->operator = $langJson->operator;
        $this->indexSize = $langJson->index_size;
    }
}