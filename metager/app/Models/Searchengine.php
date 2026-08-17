<?php

namespace App\Models;

use App\Localization;
use App\MetaGer;
use App\Models\Authorization\Authorization;
use App\PrometheusExporter;
use App\SearchSettings;
use App\Support\UpstreamUserAgent;
use Auth;
use Cache;
use Carbon;
use LaravelLocalization;

abstract class Searchengine
{
    const CONFIG_OVERLOAD = [];
    public $getString = ""; # Der String für die Get-Anfrage
    public $query = ""; # The search query
    public $alteredQuery = ""; // If the query was modified by the searchengine
    public $alterationOverrideQuery = ""; // The Override to remove the altered query

    /** @var SearchEngineConfiguration */
    public $configuration;

    public $totalResults = 0; # How many Results the Searchengine has found
    public $results = []; # Die geladenen Ergebnisse
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
    public $useragent; # Der HTTP Useragent, den wir nach draußen senden
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
    protected $ratelimitKey = null;

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->name = $name;
        $this->ratelimitKey = "searchengine.ratelimit." . now()->format("Y-m") . "." . $this->name;


        $metager = app(MetaGer::class);
        // A generic User-Agent, not the visitor's own — see UpstreamUserAgent.
        $this->useragent = app(UpstreamUserAgent::class)->value();
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

    /**
     * Describe the request this engine wants made, for the fetcher to make.
     *
     * Returning the mission rather than queueing it is what lets
     * Search\EngineOrchestrator push every engine's mission in one round trip;
     * this used to end in an rpush of its own, once per engine.
     *
     * Null means there is nothing to fetch, because the answer is already in the
     * cache. Asking for a mission also books the engine's monthly usage, since
     * that is the point at which the request is decided on.
     *
     * @return array<string, mixed>|null
     */
    public function createMission(): ?array
    {
        if ($this->cached) {
            return null;
        }

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
            // Where the fetcher will put the answer. The orchestrator looks
            // there for it, and nothing computes this hash twice.
            "resulthash" => $this->getHash(),
            "url" => $url,
            "useragent" => $this->useragent,
            "username" => $this->configuration->httpAuthUsername,
            "password" => $this->configuration->httpAuthPassword,
            "headers" => (array) $this->configuration->requestHeader,
            "cacheDuration" => $this->configuration->cacheDuration,
            "name" => $this->name,
        ];

        if ($this->configuration->method === "post_json") {
            $mission["headers"]["Content-Type"] = "application/json";
            $mission["curlopts"] = [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($this->configuration->getParameter)
            ];
        }

        // Increase ratelimit counter
        if ($this->configuration->monthlyRequests !== null) {
            // Increment counter for monthly searchengine usage
            Cache::increment($this->ratelimitKey);
        }

        return $mission;
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

    public function getDisplayName(bool $includeCost = false)
    {
        // Generates the public visible Label
        $displayName = $this->configuration->infos->displayName;

        if (!$includeCost) {
            return $displayName;
        }
        $costLabel = "";
        if ($this->configuration->cost > 0) {
            $costLabel = $this->configuration->cost . " Token";
        } else if ($this->configuration->monthlyRequests !== null) {
            if (app(Authorization::class)->canDoAuthenticatedSearch() || $this->isRateLimited()) {
                $costLabel = __("settings.free");
            } else {
                $costLabel = __("settings.limited");
            }
        } else {
            $costLabel = __("settings.free");
        }
        return $displayName . " ($costLabel)";
    }

    /**
     * Parse an answer this engine's parser understands.
     *
     * The answer is fetched by Search\EngineOrchestrator, not here — reading it
     * used to be part of this method, one rpoplpush and one expire per engine,
     * and moving it out is what lets all of them be read together. What is left
     * is the part that is actually this class' business: turning the engine's
     * own response format into results.
     *
     * Null means the engine has nothing to say: no answer arrived, or none was
     * asked for. "no-result" is the literal string the fetcher writes when
     * upstream failed, and counts as an empty answer rather than no answer — the
     * engine did report, it reported nothing.
     */
    public function loadResponse(MetaGer $metager, ?string $body): bool
    {
        if ($this->loaded) {
            return true;
        }

        if ($body === null) {
            return false;
        }

        if ($body === "no-result") {
            $body = "";
        }

        $this->loadResults($body);
        $this->getNext($metager, $body);
        $this->markNew();
        $this->loaded = true;

        return true;
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

        if ($this->configuration->method === "get") {
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
        }

        return $getString;
    }

    private function isRateLimited(): bool
    {
        if ($this->configuration->monthlyRequests !== null) {
            $request_limit_this_month = $this->configuration->monthlyRequests;
            $seconds_this_month = date('t') * 86400;

            $seconds_this_month_until_now = (new Carbon("first day of this month"))->hour(0)->minute(0)->second(0)->microsecond(0)->diffInSeconds(now(), true);
            $allowed_requests_until_now = round(($seconds_this_month_until_now / $seconds_this_month) * $request_limit_this_month);
            $requests_this_month = intval(Cache::get($this->ratelimitKey, $allowed_requests_until_now));

            // Initialize if not set yet
            Cache::add($this->ratelimitKey, $requests_this_month, (new Carbon("first day of next month"))->hour(0)->minute(0)->second(0)->microsecond(0));
            if ($allowed_requests_until_now <= $requests_this_month) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
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