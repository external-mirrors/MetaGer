<?php

namespace App\Models;

use App\MetaGer;
use Cache;
use Illuminate\Support\Facades\Redis;

abstract class Searchengine
{
    public $getString = ""; # Der String für die Get-Anfrage
    public $engine; # Die ursprüngliche Engine XML
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

    public function __construct($name, \stdClass $engine, MetaGer $metager)
    {
        $this->engine = $engine;
        $this->name = $name;

        if (isset($engine->{"cache-duration"}) && $engine->{"cache-duration"} !== -1) {
            $this->cacheDuration = $engine->{"cache-duration"};
        }
        $this->cacheDuration = max($this->cacheDuration, 5);

        $this->useragent = $metager->getUserAgent();
        $this->ip = $metager->getIp();
        $this->startTime = microtime(true);
        # check for http Auth
        if (!empty($this->engine->{"http-auth-credentials"}->username) && !empty($this->engine->{"http-auth-credentials"}->password)) {
            $this->username = $this->engine->{"http-auth-credentials"}->username;
            $this->password = $this->engine->{"http-auth-credentials"}->password;
        }

        if (!empty($this->engine->{"request-header"})) {
            $this->headers = [];
            foreach ($this->engine->{"request-header"} as $key => $value) {
                $this->headers[$key] = $value;
            }
            if (sizeof($this->headers) == 0) {
                $this->headers = null;
            }
        }

        # Suchstring generieren
        $q = $metager->getQ();
        $filters = $metager->getSumaFile()->filter;
        foreach ($metager->getQueryFilter() as $queryFilter => $filter) {
            $filterOptions = $filters->{"query-filter"}->$queryFilter;
            if (!$filterOptions->sumas->{$this->name}) {
                continue;
            }
            $filterOptionsEngine = $filterOptions->sumas->{$this->name};
            $query = $filterOptionsEngine->prefix . $filter . $filterOptionsEngine->suffix;
            $q = $query . " " . $q;
        }

        # Parse enabled Parameter-Filter
        foreach ($metager->getParameterFilter() as $filterName => $filter) {
            $inputParameter = $filter->value;

            if (empty($inputParameter) || empty($filter->sumas->{$name}->values->{$inputParameter})) {
                continue;
            }
            $engineParameterKey = $filter->sumas->{$name}->{"get-parameter"};
            $engineParameterValue = $filter->sumas->{$name}->values->{$inputParameter};
            $this->engine->{"get-parameter"}->{$engineParameterKey} = $engineParameterValue;
        }

        $this->getString = $this->generateGetString($q);
        $this->updateHash();
        $this->canCache = $metager->canCache();
    }

    abstract public function loadResults($result);

    # Standardimplementierung der getNext Funktion, damit diese immer verwendet werden kann
    public function getNext(MetaGer $metager, $result)
    {}

    # Prüft, ob die Suche bereits gecached ist, ansonsted wird sie als Job dispatched
    public function startSearch(\App\MetaGer $metager, &$timings)
    {
        if (!empty($timings)) {
            $timings["startSearch"][$this->name]["start"] = microtime(true) - $timings["starttime"];
        }

        if (!$this->cached) {
            if (!empty($timings)) {
                $timings["startSearch"][$this->name]["checked cache"] = microtime(true) - $timings["starttime"];
            }

            // We need to submit a action that one of our workers can understand
            // The missions are submitted to a redis queue in the following string format
            // <ResultHash>;<URL to fetch>
            // With <ResultHash> being the Hash Value where the fetcher will store the result.
            // and <URL to fetch> being the full URL to the searchengine

            $url = "";
            if ($this->engine->port === 443) {
                $url = "https://";
            } else {
                $url = "http://";
            }
            $url .= $this->engine->host;
            if ($this->engine->port !== 80 && $this->engine->port !== 443) {
                $url .= ":" . $this->engine->port;
            }
            $url .= $this->getString;

            $mission = [
                "resulthash" => $this->hash,
                "url" => $url,
                "username" => $this->username,
                "password" => $this->password,
                "headers" => $this->headers,
                "cacheDuration" => $this->cacheDuration,
            ];

            $mission = json_encode($mission);

            // Submit this mission to the corresponding Redis Queue
            // Since each Searcher is dedicated to one specific search engine
            // each Searcher has it's own queue lying under the redis key <name>.queue
            Redis::connection('cache')->rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
            if (!empty($timings)) {
                $timings["startSearch"][$this->name]["pushed job"] = microtime(true) - $timings["starttime"];
            }

            // The request is not cached and will be submitted to the searchengine
            // We need to check if the number of requests to this engine are limited
            if (!empty($this->engine->{"monthly-requests"})) {
                Redis::incr("monthlyRequests:" . $this->name);
                if (!empty($timings)) {
                    $timings["startSearch"][$this->name]["increased monthly requests"] = microtime(true) - $timings["starttime"];
                }
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
        $this->hash = md5($this->engine->host . $this->getString . $this->engine->port . $this->name);
    }

    # Fragt die Ergebnisse von Redis ab und lädt Sie
    public function retrieveResults(MetaGer $metager, $body = null)
    {
        if ($this->loaded) {
            return true;
        }

        if ($this->cached) {
            if ($body === "no-result") {
                $body = "";
            }
        } else {
            $body = Cache::get($this->hash);
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
    protected function generateGetString($query)
    {
        $getString = "";

        # Skript:
        if (!empty($this->engine->path)) {
            $getString .= $this->engine->path;
        } else {
            $getString .= "/";
        }

        $getString .= "?";
        $parameter = [];
        foreach ($this->engine->{"get-parameter"} as $key => $value) {
            $parameter[] = $this->urlEncode($key) . "=" . $this->urlEncode($value);
        }
        $getString .= implode("&", $parameter);

        # Append the Query String
        $getString .= "&" . $this->engine->{"query-parameter"} . "=" . $this->urlEncode($query);

        $getString .= $this->getDynamicParamsString();

        return $getString;
    }

    # Wandelt einen String nach aktuell gesetztem inputEncoding dieser Searchengine in URL-Format um
    protected function urlEncode($string)
    {
        if (isset($this->inputEncoding)) {
            return urlencode(mb_convert_encoding($string, $this->inputEncoding));
        } else {
            return urlencode($string);
        }
    }

    private function getDynamicParamsString()
    {
        $paramString = "";

        $params = $this->getDynamicParams();
        foreach ($params as $key => $value) {
            $paramString .= sprintf("&%s=%s", urlencode($key), urlencode($value));
        }

        return $paramString;
    }

    protected function getDynamicParams()
    {
        return [];
    }

    public function setNew($new)
    {
        $this->new = $new;
    }
}
