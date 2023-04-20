<?php

namespace App\Models;

use App\Localization;
use App\MetaGer;
use Illuminate\Support\Facades\Redis;
use LaravelLocalization;

abstract class Searchengine
{
    public $getString = ""; # Der String für die Get-Anfrage
    public $query = ""; # The search query

    /** @var SearchEngineConfiguration */
    public $configuration;

    public $totalResults = 0; # How many Results the Searchengine has found
    public $results = []; # Die geladenen Ergebnisse
    public $ads = []; # Die geladenen Werbungen
    public $products = []; # Die geladenen Produkte
    public $loaded = false; # wahr, sobald die Ergebnisse geladen wurden
    public $cached = false;

    public $ip; # Die IP aus der metager
    public $uses; # Die Anzahl der Nutzungen dieser Suchmaschine
    public $homepage; # Die Homepage dieser Suchmaschine
    public $name; # Der Name dieser Suchmaschine
    public $disabled; # Ob diese Suchmaschine ausgeschaltet ist
    public $useragent; # Der HTTP Useragent
    public $startTime; # Die Zeit der Erstellung dieser Suchmaschine
    public $hash; # Der Hash-Wert dieser Suchmaschine

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

        # Suchstring generieren
        $query = $metager->getQ();
        $filters = $metager->getSumaFile()->filter;
        foreach ($metager->getQueryFilter() as $queryFilter => $filter) {
            $filterOptions = $filters->{"query-filter"}->$queryFilter;
            if (!$filterOptions->sumas->{$this->name}) {
                continue;
            }
            $filterOptionsEngine = $filterOptions->sumas->{$this->name};
            $query_part = $filterOptionsEngine->prefix . $filter . $filterOptionsEngine->suffix;
            $query .= " " . $query_part;
        }
        $this->configuration->applyQuery($query);

        # Parse enabled Parameter-Filter
        foreach ($metager->getParameterFilter() as $filterName => $filter) {
            $inputParameter = $filter->value;

            if (empty($inputParameter) || empty($filter->sumas->{$name}->values->{$inputParameter})) {
                continue;
            }
            $engineParameterKey = $filter->sumas->{$name}->{"get-parameter"};
            $engineParameterValue = $filter->sumas->{$name}->values->{$inputParameter};
            if (stripos($engineParameterValue, "dyn-") === 0) {
                $functionname = substr($engineParameterValue, stripos($engineParameterValue, "dyn-") + 4);
                $engineParameterValue = \App\DynamicEngineParameters::$functionname();
            }
            $this->configuration->getParameter->{$engineParameterKey} = $engineParameterValue;
        }

        $this->updateHash();
        $this->canCache = $metager->canCache();
    }

    abstract public function loadResults($result);

    # Standardimplementierung der getNext Funktion, damit diese immer verwendet werden kann
    public function getNext(MetaGer $metager, $result)
    {
    }

    # Prüft, ob die Suche bereits gecached ist, ansonsted wird sie als Job dispatched
    public function startSearch()
    {
        if (!$this->cached || 1 == 1) {
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
                "resulthash" => $this->hash,
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
            Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

            // The request is not cached and will be submitted to the searchengine
            // We need to check if the number of requests to this engine are limited
            if (!empty($this->configuration->monthlyRequests)) {
                Redis::incr("monthlyRequests:" . $this->name);
            }
        }
    }

    # Ruft die Ranking-Funktion aller Ergebnisse auf.
    public function rank($eingabe)
    {
        foreach ($this->results as $result) {
            $result->rank($eingabe);
        }
    }

    public function setResultHash($hash)
    {
        $this->resultHash = $hash;
    }

    public function updateHash()
    {
        $this->hash = md5(serialize($this->configuration));
    }

    # Fragt die Ergebnisse von Redis ab und lädt Sie
    public function retrieveResults(MetaGer $metager, $body = null)
    {
        if ($this->loaded) {
            return true;
        }
        if (!$this->cached && empty($body)) {
            $body = Redis::rpoplpush($this->hash, $this->hash);
            Redis::expire($this->hash, 60);
            if ($body === false) {
                return $body;
            }
        }

        if ($body === "no-result") {
            $body = "";
        }

        if ($body !== null) {
            $this->loadResults($body);
            $this->getNext($metager, $body);
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