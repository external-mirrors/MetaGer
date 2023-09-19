<?php

namespace App\Models;

use App\Localization;
use App\MetaGer;
use App\Models\Authorization\Authorization;
use app\Models\parserSkripte\Overture;
use App\PrometheusExporter;
use App\SearchSettings;
use Illuminate\Support\Facades\Redis;
use LaravelLocalization;

abstract class Searchengine
{
    public $getString = ""; # Der String für die Get-Anfrage
    public $query = ""; # The search query
    public $alteredQuery = ""; // If the query was modified by the searchengine
    public $alterationOverrideQuery = ""; // The Override to remove the altered query

    /** @var SearchEngineConfiguration */
    public $configuration;

    public $totalResults = 0; # How many Results the Searchengine has found
    public $results = []; # Die geladenen Ergebnisse
    public $ads = []; # Die geladenen Werbungen
    public $products = []; # Die geladenen Produkte
    /** @var Result[] */
    public $news = [];
    /** @var Result[] */
    public $videos = [];
    public $loaded = false; # wahr, sobald die Ergebnisse geladen wurden
    public $cached = false;

    public $ip; # Die IP aus der metager
    public $uses; # Die Anzahl der Nutzungen dieser Suchmaschine
    public $homepage; # Die Homepage dieser Suchmaschine
    public $name; # Der Name dieser Suchmaschine
    public $disabled; # Ob diese Suchmaschine ausgeschaltet ist
    public $useragent; # Der HTTP Useragent
    public $startTime; # Die Zeit der Erstellung dieser Suchmaschine

    private $username; # Username für HTTP-Auth (falls angegeben)
    private $password; # Passwort für HTTP-Auth (falls angegeben)

    private $headers; # Headers to add

    public $fp; # Wird für Artefakte benötigt
    public $socketNumber = null; # Wird für Artefakte benötigt
    public $counter = 0; # Wird eventuell für Artefakte benötigt
    public $write_time = 0; # Wird eventuell für Artefakte benötigt
    public $connection_time = 0; # Wird eventuell für Artefakte benötigt
    public $cacheDuration = 60; # Wie lange soll das Ergebnis im Cache bleiben (Minuten)
    public $new = true; # Important for loading results by JS
    protected $failed = false; # Used to check if Overture search has failed

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->name = $name;


        $metager = app(MetaGer::class);
        // Thanks to our Middleware this is a almost completely random useragent
        // which matches the correct device type
        $this->useragent = $metager->getUserAgent();
        $this->ip = $metager->getIp();
        $this->startTime = microtime(true);

        $this->canCache = $metager->canCache();
    }

    /**
     * SearchSettings are not fully loaded when Searchengines are created
     * this function is called when all Settings are finished loading
     */
    public function applySettings()
    {
        $settings = app(SearchSettings::class);
        $query = $settings->q;
        $filters = $settings->sumasJson->filter;
        foreach (app(SearchSettings::class)->queryFilter as $queryFilter => $filter) {
            $filterOptions = $filters->{"query-filter"}->$queryFilter;
            if (!$filterOptions->sumas->{$this->name}) {
                continue;
            }
            $filterOptionsEngine = $filterOptions->sumas->{$this->name};
            $query_part = $filterOptionsEngine->prefix . $filter . $filterOptionsEngine->suffix;
            $query .= " " . $query_part;
        }
        $this->configuration->applyQuery($query);

        // Parse enabled Parameter-Filter
        foreach (app(SearchSettings::class)->parameterFilter as $filterName => $filter) {
            $inputParameter = $filter->value;

            if (empty($inputParameter) || empty($filter->sumas->{$this->name}->values->{$inputParameter})) {
                continue;
            }
            $engineParameterKey = $filter->sumas->{$this->name}->{"get-parameter"};
            $engineParameterValue = $filter->sumas->{$this->name}->values->{$inputParameter};
            if (stripos($engineParameterValue, "dyn-") === 0) {
                $functionname = substr($engineParameterValue, stripos($engineParameterValue, "dyn-") + 4);
                $engineParameterValue = \App\DynamicEngineParameters::$functionname();
            }
            $this->configuration->getParameter->{$engineParameterKey} = $engineParameterValue;
        }
    }

    abstract public function loadResults($result);

    # Standardimplementierung der getNext Funktion, damit diese immer verwendet werden kann
    public function getNext(MetaGer $metager, $result)
    {
    }

    # Prüft, ob die Suche bereits gecached ist, ansonsted wird sie als Job dispatched
    public function startSearch()
    {
        if (!$this->cached) {
            // We need to submit a action that one of our workers can understand
            // The missions are submitted to a redis queue in the following string format
            // <ResultHash>;<URL to fetch>
            // With <ResultHash> being the Hash Value where the fetcher will store the result.
            // and <URL to fetch> being the full URL to the searchengine

            $url = "";
            if ($this->configuration->port === 443) {
                $url = "https://";
            } else {
                $url = "http://";
            }
            $url .= $this->configuration->host;
            if ($this->configuration->port !== 80 && $this->configuration->port !== 443) {
                $url .= ":" . $this->configuration->port;
            }
            $url .= $this->generateGetString();

            $mission = [
                "resulthash" => $this->getHash(),
                "url" => $url,
                "useragent" => $this->useragent,
                "username" => $this->configuration->httpAuthUsername,
                "password" => $this->configuration->httpAuthPassword,
                "headers" => (array) $this->configuration->requestHeader,
                "cacheDuration" => $this->configuration->cacheDuration,
                "name" => $this->name
            ];

            $mission = json_encode($mission);

            // Submit this mission to the corresponding Redis Queue
            // Since each Searcher is dedicated to one specific search engine
            // each Searcher has it's own queue lying under the redis key <name>.queue
            Redis::rpush(MetaGer::FETCHQUEUE_KEY, $mission);
        }
    }

    # Ruft die Ranking-Funktion aller Ergebnisse auf.
    public function rank()
    {
        foreach ($this->results as $result) {
            $result->rank();
        }
    }

    public function setResultHash($hash)
    {
        $this->resultHash = $hash;
    }

    public function getHash()
    {
        return md5(serialize($this->configuration));
    }

    # Fragt die Ergebnisse von Redis ab und lädt Sie
    public function retrieveResults(MetaGer $metager, $body = null)
    {
        if ($this->loaded) {
            return true;
        }
        if (!$this->cached && empty($body)) {
            $body = Redis::rpoplpush($this->getHash(), $this->getHash());
            Redis::expire($this->getHash(), 60);
            if ($body === false) {
                return $body;
            }
        }

        if ($body === "no-result") {
            $body = "";
        }

        if ($body !== null) {
            $this->loadResults($body);
            if ($this instanceof Overture && !$this->failed && sizeof($this->results) === 0) {
                $this->failed = true;
                return false;
            }
            $this->getNext($metager, $body);
            // Pay for the searchengine if cost > 0 and returned results
            if ($this->configuration->cost > 0 && sizeof($this->results) > 0) {
                // Remove namespace before passing engine to exporter
                PrometheusExporter::KeyUsed(preg_replace("/^.*\\\/", "", get_class($this)), $this->cached);
                if (!$this->cached) {
                    app(Authorization::class)->makePayment($this->configuration->cost);
                }
            }
            $this->markNew();
            $this->loaded = true;
            return true;
        } else {
            return false;
        }
    }

    public function markNew()
    {
        foreach ($this->results as $result) {
            $result->new = $this->new;
        }
    }

    # Erstellt den für die Get-Anfrage genutzten String
    protected function generateGetString()
    {
        $getString = "";

        # Skript:
        if (!empty($this->configuration->path)) {
            $getString .= $this->configuration->path;
        } else {
            $getString .= "/";
        }

        $getString .= "?";

        $parameters = (array) clone $this->configuration->getParameter;

        # Dynamic Parameters
        $parameters = \array_merge($parameters, $this->getDynamicParams());

        if (!empty($this->configuration->inputEncoding)) {
            $inputEncoding = $this->configuration->inputEncoding;
            \array_walk($parameters, function (&$value, $key) use ($inputEncoding) {
                $value = \mb_convert_encoding($value, $inputEncoding);
            });
        }

        $getString .= \http_build_query($parameters, "", "&", \PHP_QUERY_RFC3986);

        return $getString;
    }

    # Wandelt einen String nach aktuell gesetztem inputEncoding dieser Searchengine in URL-Format um
    protected function urlEncode($string)
    {
        if (isset($this->configuration->inputEncoding)) {
            return urlencode(mb_convert_encoding($string, $this->configuration->inputEncoding));
        } else {
            return urlencode($string);
        }
    }

    protected function getDynamicParams()
    {
        return [];
    }

    public function setNew($new)
    {
        $this->new = $new;
    }

    public function setCached($cached)
    {
        $this->cached = $cached;
    }
}