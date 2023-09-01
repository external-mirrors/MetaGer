<?php

namespace App;

use App\Models\Authorization\Authorization;
use App\Models\Configuration\Searchengines;
use App\Models\Searchengine;
use App\Models\Verification\HumanVerification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Predis\Connection\ConnectionException;

class MetaGer
{
    const FETCHQUEUE_KEY = "fetcher.queue";

    # Einstellungen für die Suche
    public $alteredQuery = "";
    public $alterationOverrideQuery = "";
    protected $fokus;
    protected $test;
    protected $eingabe;
    protected $q;
    protected $out;
    protected $page;
    protected $lang;
    protected $cache = "";
    protected $site;
    protected $time = 2000;
    protected $hostBlacklist = [];
    protected $domainBlacklist = [];
    private $urlBlacklist = [];
    protected $stopWords = [];
    protected $phrases = [];
    protected $engines = [];
    protected $totalResults = 0;
    protected $results = [];
    protected $queryFilter = [];
    protected $parameterFilter = [];
    protected $ads = [];
    public $news = [];
    public $videos = [];
    protected $infos = [];
    public $warnings = [];
    public $htmlwarnings = [];
    public $errors = [];
    protected $addedHosts = [];
    protected $availableFoki = [];
    protected $startCount = 0;
    protected $canCache = false;
    # Daten über die Abfrage$
    protected $ip;
    protected $useragent;
    protected $language;
    protected $agent;
    protected $apiKey = "";
    protected $apiAuthorized = false;
    protected $next = [];
    # Konfigurationseinstellungen:
    protected $sumaFile;
    protected $mobile;
    protected $framed;
    protected $resultCount;
    protected $sprueche;
    protected $newtab;
    protected $domainsBlacklisted = [];
    protected $adDomainsBlacklisted = [];
    protected $urlsBlacklisted = [];
    protected $adUrlsBlacklisted = [];
    protected $blacklistDescriptionUrl = [];
    protected $url;
    protected $fullUrl;
    protected $enabledSearchengines = [];
    protected $languageDetect;
    protected $searchUid;
    protected $redisResultWaitingKey;
    protected $redisResultEngineList;
    protected $redisEngineResult;
    protected $redisCurrentResultList;
    public $starttime;
    protected $dummy = false;

    public function __construct($hash = "")
    {
        # Timer starten
        $this->starttime = microtime(true);
        # Versuchen Blacklists einzulesen
        if (file_exists(config_path() . "/blacklistDomains.txt") && file_exists(config_path() . "/blacklistUrl.txt")) {
            $tmp = file_get_contents(config_path() . "/blacklistDomains.txt");
            $this->domainsBlacklisted = explode("\n", $tmp);
            $tmp = file_get_contents(config_path() . "/blacklistUrl.txt");
            $this->urlsBlacklisted = explode("\n", $tmp);
        }

        # Versuchen Blacklists einzulesen
        if (file_exists(config_path() . "/adBlacklistDomains.txt")) {
            $tmp = file_get_contents(config_path() . "/adBlacklistDomains.txt");
            $this->adDomainsBlacklisted = explode("\n", $tmp);
        }

        if (file_exists(config_path() . "/adBlacklistUrl.txt")) {
            $tmp = file_get_contents(config_path() . "/adBlacklistUrl.txt");
            $this->adUrlsBlacklisted = explode("\n", $tmp);
        }

        if (file_exists(config_path() . "/blacklistDescriptionUrl.txt")) {
            $tmp = file_get_contents(config_path() . "/blacklistDescriptionUrl.txt");
            $this->blacklistDescriptionUrl = explode("\n", $tmp);
        }

        # Parser Skripte einhängen
        $dir = app_path() . "/Models/parserSkripte/";
        foreach (scandir($dir) as $filename) {
            $path = $dir . $filename;
            if (is_file($path)) {
                require_once $path;
            }
        }

        # Cachebarkeit testen
        try {
            Cache::has('test');
            $this->canCache = true;
        } catch (ConnectionException $e) {
            $this->canCache = false;
        }
        if ($hash === "") {
            $this->searchUid = md5(uniqid());
        } else {
            $this->searchUid = $hash;
        }
        $redisPrefix = "search";
        # This is a list on which the MetaGer process can do a blocking pop to wait for new results
        $this->redisResultWaitingKey = $redisPrefix . "." . $this->searchUid . ".ready";
        # This is a list of searchengines which have delivered results for this search
        $this->redisResultEngineList = $redisPrefix . "." . $this->searchUid . ".engines";
        # This is the key where the results of the engine are stored as well as some statistical data
        $this->redisEngineResult = $redisPrefix . "." . $this->searchUid . ".results.";
        # A list of all search results already delivered to the user (sorted of course)
        $this->redisCurrentResultList = $redisPrefix . "." . $this->searchUid . ".currentResults";

        $this->parseFormData();
    }

    # Erstellt aus den gesammelten Ergebnissen den View
    public function createView($quicktipResults = [])
    {
        # Hiermit werden die evtl. ausgewählten SuMas extrahiert, damit die Input-Boxen richtig gesetzt werden können
        $focusPages = [];

        foreach ($this->request->all() as $key => $value) {
            if (stripos($key, 'engine_') === 0 && $value === 'on') {
                $focusPages[] = $key;
            }
        }

        $viewResults = [];
        # Wir extrahieren alle notwendigen Variablen und geben Sie an unseren View:
        foreach ($this->results as $result) {
            $viewResults[] = get_object_vars($result);
        }
        # Wir müssen natürlich noch den Log für die durchgeführte Suche schreiben:
        /** @var QueryLogger */
        $query_logger = App::make(QueryLogger::class);
        $query_logger->createLog();
        if (app(SearchSettings::class)->fokus === "bilder") {
            switch ($this->out) {
                case 'results':
                    return view('resultpages.results_images')
                        ->with('results', $viewResults)
                        ->with('eingabe', $this->eingabe)
                        ->with('mobile', $this->mobile)
                        ->with('warnings', $this->warnings)
                        ->with('htmlwarnings', $this->htmlwarnings)
                        ->with('errors', $this->errors)
                        ->with('apiAuthorized', $this->apiAuthorized)
                        ->with('metager', $this)
                        ->with('imagesearch', true)
                        ->with('browser', (new Agent())->browser());
                default:
                    return view('resultpages.resultpage_images')
                        ->with('results', $viewResults)
                        ->with('eingabe', $this->eingabe)
                        ->with('mobile', $this->mobile)
                        ->with('warnings', $this->warnings)
                        ->with('htmlwarnings', $this->htmlwarnings)
                        ->with('errors', $this->errors)
                        ->with('apiAuthorized', $this->apiAuthorized)
                        ->with('metager', $this)
                        ->with('browser', (new Agent())->browser())
                        ->with('quicktips', $quicktipResults)
                        ->with('focus', app(SearchSettings::class)->fokus)
                        ->with('imagesearch', true)
                        ->with('resultcount', count($this->results));
            }
        } else {
            switch ($this->out) {
                case 'results':
                    return view('resultpages.results')
                        ->with('results', $viewResults)
                        ->with('eingabe', $this->eingabe)
                        ->with('mobile', $this->mobile)
                        ->with('warnings', $this->warnings)
                        ->with('htmlwarnings', $this->htmlwarnings)
                        ->with('errors', $this->errors)
                        ->with('apiAuthorized', $this->apiAuthorized)
                        ->with('metager', $this)
                        ->with('browser', (new Agent())->browser())
                        ->with('fokus', app(SearchSettings::class)->fokus);
                    break;
                case 'results-with-style':
                    return view('resultpages.resultpage')
                        ->with('results', $viewResults)
                        ->with('eingabe', $this->eingabe)
                        ->with('mobile', $this->mobile)
                        ->with('warnings', $this->warnings)
                        ->with('htmlwarnings', $this->htmlwarnings)
                        ->with('errors', $this->errors)
                        ->with('apiAuthorized', $this->apiAuthorized)
                        ->with('metager', $this)
                        ->with('suspendheader', "yes")
                        ->with('browser', (new Agent())->browser())
                        ->with('fokus', app(SearchSettings::class)->fokus);
                    break;
                case 'rss20':
                    return view('resultpages.metager3resultsrss20')
                        ->with('results', $viewResults)
                        ->with('eingabe', $this->eingabe)
                        ->with('apiAuthorized', $this->apiAuthorized)
                        ->with('metager', $this)
                        ->with('resultcount', sizeof($viewResults))
                        ->with('fokus', app(SearchSettings::class)->fokus);
                    break;
                case 'api':
                    return view('resultpages.metager3resultsatom10', ['eingabe' => $this->eingabe, 'resultcount' => sizeof($viewResults), 'key' => $this->apiKey, 'metager' => $this]);
                    break;
                case 'atom10':
                    return view('resultpages.metager3resultsatom10', ['eingabe' => $this->eingabe, 'resultcount' => sizeof($viewResults), 'key' => $this->apiKey, 'metager' => $this]);
                    break;
                case 'result-count':
                    # Wir geben die Ergebniszahl und die benötigte Zeit zurück:
                    return sizeof($viewResults) . ";" . round((microtime(true) - $this->starttime), 2);
                    break;
                default:
                    return view('resultpages.resultpage')
                        ->with('eingabe', $this->eingabe)
                        ->with('focusPages', $focusPages)
                        ->with('mobile', $this->mobile)
                        ->with('warnings', $this->warnings)
                        ->with('htmlwarnings', $this->htmlwarnings)
                        ->with('errors', $this->errors)
                        ->with('apiAuthorized', $this->apiAuthorized)
                        ->with('metager', $this)
                        ->with('browser', (new Agent())->browser())
                        ->with('quicktips', $quicktipResults)
                        ->with('resultcount', count($this->results))
                        ->with('focus', app(SearchSettings::class)->fokus);
                    break;
            }
        }
    }

    public function prepareResults()
    {
        // combine
        $this->combineResults();

        // misc (WiP)
        if (app(SearchSettings::class)->fokus == "nachrichten") {
            $this->results = array_filter($this->results, function ($v, $k) {
                return !is_null($v->getRank());
            }, ARRAY_FILTER_USE_BOTH);
            uasort($this->results, function ($a, $b) {
                $datea = $a->getDate();
                $dateb = $b->getDate();
                return $dateb - $datea;
            });
        } else {
            uasort($this->results, function ($a, $b) {
                if ($a->getRank() == $b->getRank()) {
                    return 0;
                }

                return ($a->getRank() < $b->getRank()) ? 1 : -1;
            });
        }

        # Validate Results
        $newResults = [];
        foreach ($this->results as $result) {
            if ($result->isValid($this)) {
                $newResults[] = $result;
            }
        }
        $this->results = $newResults;

        $this->duplicationCheck();

        # Validate Advertisements
        $newResults = [];
        foreach ($this->ads as $ad) {
            if (
                ($ad->strippedHost !== "" && (in_array($ad->strippedHost, $this->adDomainsBlacklisted) ||
                    in_array($ad->strippedLink, $this->adUrlsBlacklisted)))
            ) {
                continue;
            }
            $newResults[] = $ad;
        }

        $this->ads = $newResults;

        #Adgoal Implementation
        if (empty($this->adgoalLoaded)) {
            $this->adgoalLoaded = false;
        }

        # Human Verification
        $this->humanVerification($this->results);
        $this->humanVerification($this->ads);

        if (count($this->results) <= 0) {
            if (strlen($this->site) > 0) {
                $no_sitesearch_query = str_replace(urlencode("site:" . $this->site), "", $this->fullUrl);
                $this->errors[] = trans('metaGer.results.failedSitesearch', ['altSearch' => $no_sitesearch_query]);
            } else {
                $this->errors[] = trans('metaGer.results.failed');
            }
        }

        if ($this->canCache() && isset($this->next) && count($this->next) > 0 && count($this->results) > 0) {
            $page = app(SearchSettings::class)->page + 1;
            $this->next = [
                'page' => $page,
                'engines' => $this->next,
            ];
            Cache::put($this->getSearchUid(), serialize($this->next), 60 * 60);
        } else {
            $this->next = [];
        }
    }

    public function combineResults()
    {
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            if (isset($engine->next)) {
                $this->next[] = $engine->next;
            }
            foreach ($engine->results as $result) {
                if ($result->valid) {
                    $this->results[] = clone $result;
                }
            }
            foreach ($engine->ads as $ad) {
                $this->ads[] = clone $ad;
            }
            foreach ($engine->news as $news) {
                $this->news[] = clone $news;
            }
            foreach ($engine->videos as $video) {
                $this->videos[] = clone $video;
            }
        }
    }

    public function duplicationCheck()
    {
        $arr = [];
        for ($i = 0; $i < count($this->results); $i++) {
            $link = $this->results[$i]->link;

            if (strpos($link, "http://") === 0) {
                $link = substr($link, 7);
            }

            if (strpos($link, "https://") === 0) {
                $link = substr($link, 8);
            }

            if (strpos($link, "www.") === 0) {
                $link = substr($link, 4);
            }

            $link = trim($link, "/");

            if (isset($arr[$link])) {
                $arr[$link]->gefVon[] = $this->results[$i]->gefVon[0];
                $arr[$link]->gefVonLink[] = $this->results[$i]->gefVonLink[0];

                if (!empty($this->results[$i]->image)) {
                    $arr[$link]->image = $this->results[$i]->image;
                }

                if (!empty($this->results[$i]->inheritedResults)) {
                    $arr[$link]->inheritedResults = $this->results[$i]->inheritedResults;
                }

                if (!empty($this->results[$i]->deepResults)) {
                    $arr[$link]->deepResults = $this->results[$i]->deepResults;
                }
                // The duplicate might already be an adgoal partnershop
                if ($this->results[$i]->partnershop) {
                    # Den Link hinzufügen:
                    $arr[$link]->logo = $this->results[$i]->logo;
                    $arr[$link]->image = $this->results[$i]->image;
                    $arr[$link]->link = $this->results[$i]->link;
                    $arr[$link]->partnershop = $this->results[$i]->partnershop;
                }



                array_splice($this->results, $i, 1);
                $i--;
                if ($arr[$link]->new === true || $this->results[$i]->new === true) {
                    $arr[$link]->changed = true;
                }
            } else {
                $arr[$link] = &$this->results[$i];
            }
        }
    }

    /**
     * @param \App\Models\Admitad[] $affiliates
     * @return \App\Models\Admitad[] whether or not all Admitad Objects are finished
     */
    public function parseAffiliates($affiliates)
    {
        $wait = false;
        $finished = true;
        if (!\app()->make(SearchSettings::class)->javascript_enabled) {
            $wait = true;
        }
        $newAffiliates = [];
        foreach ($affiliates as $affiliate) {
            $affiliate->fetchAffiliates($wait);
            $affiliate->parseAffiliates();
            if (!$affiliate->finished) {
                $newAffiliates[] = $affiliate;
            }
        }

        return $newAffiliates;
    }


    /**
     * Modifies the already filled array of advertisements and
     * includes an advertisement for our donation page.
     * 
     * It will do so everytime when there are other advertisments to mix it in
     * and only in a percentage of cases when there are no other advertisements.
     * 
     * The Position at which our advertisement is placed is random within the other
     * advertisements. In some cases it will be the first ad and in other cases in some
     * other place.
     *
     * @param int $position Position to ad advertisement at or null if random
     *
     * @return int | null Position where advertisement was added
     */
    public function addDonationAdvertisement($position = null)
    {
        if (!app(\App\SearchSettings::class)->self_advertisements) {
            return;
        }
        /**
         * If there are no other advertisements we will only display our advertisements 
         * every so often. ~33% in this case
         * ToDo set back to 5 once we do not want to advertise donations as much anymore
         */
        if ($position === null && rand(1, 100) >= 34) {
            return null;
        }

        $donationAd = new \App\Models\Result(
            "MetaGer",
            __("metaGer.ads.own.title"),
            route("spende"),
            route("spende"),
            __("metaGer.ads.own.description"),
            "MetaGer",
            "https://metager.de",
            1
        );
        $adCount = sizeof($this->ads);
        // Put Donation Advertisement to random position
        $position = $position !== null ? $position : random_int(0, $adCount);

        array_splice($this->ads, $position, 0, [$donationAd]);
        return $position;
    }

    public function humanVerification(&$results)
    {
        # Let's check if we need to implement a redirect for human verification
        $human_verification = \app()->make(HumanVerification::class);
        if (max($human_verification->getVerificationCount()) > 10) {
            foreach ($results as $result) {
                $link = $result->link;
                $day = Carbon::now()->day;
                $pw = md5($day . $link . config("metager.metager.proxy.password"));

                $params = [
                    'hv' => $human_verification->key,
                    'pw' => $pw,
                    "url" => \bin2hex($link)
                ];

                $url = route('humanverification', $params);
                $proxyPw = md5($day . $result->proxyLink . config("metager.metager.proxy.password"));
                $params["pw"] = $proxyPw;
                $params["url"] = \bin2hex($result->proxyLink);
                $proxyUrl = route('humanverification', $params);
                $result->link = $url;
                $result->proxyLink = $proxyUrl;
            }
        }
    }

    public function startSearch()
    {
        // Check all engines for Cached responses
        $this->checkCache();

        // Wir starten alle Suchen
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            $engine->startSearch();
        }
    }

    public function checkCache()
    {
        if ($this->canCache()) {
            foreach (app(Searchengines::class)->getEnabledSearchengines() as $suma) {
                if (Cache::has($suma->getHash())) {
                    $suma->cached = true;
                    $suma->retrieveResults($this, Cache::get($suma->getHash()));
                }
            }
        }
    }

    // Spezielle Suchen und Sumas

    public function sumaIsSelected($suma, $request, $custom)
    {
        if ($custom) {
            if ($request->filled("engine_" . strtolower($suma["name"]))) {
                return true;
            }
        } else {
            $types = explode(",", $suma["type"]);
            if (in_array(app(SearchSettings::class)->fokus, $types)) {
                return true;
            }
        }
        return false;
    }

    public function actuallyCreateSearchEngines($enabledSearchengines)
    {
        $engines = [];
        foreach ($enabledSearchengines as $engineName => $engine) {
            if (!isset($engine->{"parser-class"})) {
                die(var_dump($engine));
            }
            # Setze Pfad zu Parser
            $path = "App\\Models\\parserSkripte\\" . $engine->{"parser-class"};

            # Prüfe ob Parser vorhanden
            if (!file_exists(app_path() . "/Models/parserSkripte/" . $engine->{"parser-class"} . ".php")) {
                Log::error("Konnte " . $engine->infos->display_name . " nicht abfragen, da kein Parser existiert");
                $this->errors[] = trans('metaGer.engines.noParser', ['engine' => $engine->infos->display_name]);
                continue;
            }

            # Es wird versucht die Suchengine zu erstellen
            $time = microtime();
            try {
                $tmp = new $path($engineName, $engine, $this);
            } catch (\ErrorException $e) {
                Log::error("Konnte " . $engine->infos->display_name . " nicht abfragen. " . $e);
                continue;
            }

            $engines[] = $tmp;
        }
        $this->engines = $engines;
    }

    public function getAvailableParameterFilter()
    {
        $request = App::make(Request::class);
        $parameterFilter = $this->sumaFile->filter->{"parameter-filter"};

        $availableFilter = [];

        foreach ($parameterFilter as $filterName => $filter) {
            $values = clone $filter->values;
            # Check if any of the enabled search engines provide this filter
            foreach ($this->enabledSearchengines as $engineName => $engine) {
                if (!empty($filter->sumas->$engineName)) {
                    if (empty($availableFilter[$filterName])) {
                        $availableFilter[$filterName] = $filter;
                        foreach ($availableFilter[$filterName]->values as $key => $value) {
                            if ($key !== "nofilter") {
                                unset($availableFilter[$filterName]->values->{$key});
                            }
                        }
                    }
                    if (empty($availableFilter[$filterName]->values)) {
                        $availableFilter[$filterName]->values = new \stdClass();
                    }
                    foreach ($filter->sumas->{$engineName}->values as $key => $value) {
                        if (\property_exists($values, $key)) {
                            $availableFilter[$filterName]->values->{$key} = $values->$key;
                        }
                    }
                }
            }
            # We will also add the filter from the opt-in search engines (the searchengines that are only used when a filter of it is too)
            foreach ($this->sumaFile->foki->{app(SearchSettings::class)->fokus}->sumas as $suma) {
                if ($this->sumaFile->sumas->{$suma}->{"filter-opt-in"} && Cookie::get($this->getFokus() . "_engine_" . $suma) !== "off") {
                    if (!empty($filter->sumas->{$suma})) {
                        # If the searchengine is disabled this filter shouldn't be available
                        if (
                            (!empty($this->sumaFile->sumas->{$suma}->disabled) && $this->sumaFile->sumas->{$suma}->disabled === true)
                            || (!empty($this->sumaFile->sumas->{$suma}->{"auto-disabled"}) && $this->sumaFile->sumas->{$suma}->{"auto-disabled"} === true)
                        ) {
                            continue;
                        }
                        if (empty($availableFilter[$filterName])) {
                            $availableFilter[$filterName] = $filter;
                            foreach ($availableFilter[$filterName]->values as $key => $value) {
                                if ($key !== "nofilter") {
                                    unset($availableFilter[$filterName]->values->{$key});
                                }
                            }
                        }
                        if (empty($availableFilter[$filterName]->values)) {
                            $availableFilter[$filterName]->values = new \stdClass();
                        }
                        foreach ($filter->sumas->{$suma}->values as $key => $value) {
                            if (\property_exists($values, $key)) {
                                $availableFilter[$filterName]->values->{$key} = $values->$key;
                            }
                        }
                    }
                }
            }
        }

        # Set the current values for the filters
        foreach ($availableFilter as $filterName => $filter) {
            if ($request->filled($filter->{"get-parameter"})) {
                $filter->value = $request->input($filter->{"get-parameter"});
            } elseif (Cookie::get($this->getFokus() . "_setting_" . $filter->{"get-parameter"}) !== null) {
                $filter->value = Cookie::get($this->getFokus() . "_setting_" . $filter->{"get-parameter"});
            }
        }

        if (\array_key_exists("language", $availableFilter)) {
            $current_locale = LaravelLocalization::getCurrentLocaleRegional();
            $default_language_value = "";
            # Set default Value for language selector to current locale
            foreach ($this->enabledSearchengines as $name => $engine) {
                if (\property_exists($engine->lang->regions, $current_locale) && \property_exists($availableFilter["language"]->sumas, $name)) {
                    $region_suma_value = $engine->lang->regions->{$current_locale};
                    foreach ($availableFilter["language"]->sumas->{$name}->values as $key => $value) {
                        if ($value === $region_suma_value) {
                            $default_language_value = $key;
                            break 2;
                        }
                    }
                }
            }

            if (\property_exists($availableFilter["language"], "value") && $availableFilter["language"]->value === $default_language_value) {
                unset($availableFilter["language"]->value);
            }
            if (!empty($default_language_value) && \property_exists($availableFilter["language"]->values, $default_language_value)) {
                $availableFilter["language"]->values->nofilter = $availableFilter["language"]->values->$default_language_value;
                unset($availableFilter["language"]->values->$default_language_value);
            }
        }

        return $availableFilter;
    }

    public function isBildersuche()
    {
        return app(SearchSettings::class)->fokus === "bilder";
    }

    public function sumaIsAdsuche($suma, $overtureEnabled)
    {
        $sumaName = $suma["name"]->__toString();
        return
            $sumaName === "qualigo"
            || $sumaName === "similar_product_ads"
            || (!$overtureEnabled && $sumaName === "overtureAds");
    }

    public function sumaIsDisabled($suma)
    {
        return
            isset($suma['disabled'])
            && $suma['disabled']->__toString() === "1";
    }

    public function sumaIsOverture($suma)
    {
        return
            $suma["name"]->__toString() === "overture"
            || $suma["name"]->__toString() === "overtureAds";
    }

    public function sumaIsNotAdsuche($suma)
    {
        return
            $suma["name"]->__toString() !== "qualigo"
            && $suma["name"]->__toString() !== "similar_product_ads"
            && $suma["name"]->__toString() !== "overtureAds";
    }

    public function waitForMainResults()
    {
        $engines = app(Searchengines::class)->getEnabledSearchengines();
        $enginesToWaitFor = [];
        $mainEngines = $this->sumaFile->foki->{app(SearchSettings::class)->fokus}->main;
        foreach ($mainEngines as $mainEngine) {
            foreach ($engines as $engine) {
                if ($engine->name === $mainEngine) {
                    $enginesToWaitFor[] = $engine->getHash();
                }
            }
        }

        # If no main engines are enabled by the user we will wait for all results
        if (sizeof($enginesToWaitFor) === 0) {
            foreach ($engines as $engine) {
                $enginesToWaitFor[] = $engine->getHash();
            }
        } else {
            $newEnginesToWaitFor = [];
            // Don't wait for engines that are already loaded in Cache
            foreach ($enginesToWaitFor as $engineToWaitFor) {
                foreach ($engines as $engine) {
                    if ($engine->getHash() === $engineToWaitFor && !$engine->loaded) {
                        $newEnginesToWaitFor[] = $engineToWaitFor;
                    }
                }
            }
            $enginesToWaitFor = $newEnginesToWaitFor;
        }

        $timeStart = microtime(true);
        while (sizeof($enginesToWaitFor) > 0) {
            if ((microtime(true) - $timeStart) >= 2) {
                break;
            }
            $answer = Redis::brpop($enginesToWaitFor, 2);

            if ($answer === null) {
                continue;
            } else {
                Redis::lpush($answer[0], $answer[1]);
                Redis::expire($answer[0], 60);
            }
            foreach ($engines as $index => $engine) {
                if ($engine->getHash() === $answer[0]) {
                    $engine->retrieveResults($this, $answer[1]);
                    foreach ($enginesToWaitFor as $waitIndex => $engineToWaitFor) {
                        if ($engineToWaitFor === $answer[0]) {
                            unset($enginesToWaitFor[$waitIndex]);
                            break 2;
                        }
                    }
                }
            }
        }
    }

    public function retrieveResults()
    {
        $engines = app(Searchengines::class)->getEnabledSearchengines();
        // Von geladenen Engines die Ergebnisse holen
        foreach ($engines as $engine) {
            if (!$engine->loaded) {
                try {
                    $engine->retrieveResults($this);
                } catch (\ErrorException $e) {
                    Log::error($e);
                }
            }
            if (!empty($engine->totalResults) && $engine->totalResults > $this->totalResults) {
                $this->totalResults = $engine->totalResults;
            }
            if (!empty($engine->alteredQuery) && !empty($engine->alterationOverrideQuery)) {
                $this->alteredQuery = $engine->alteredQuery;
                $this->alterationOverrideQuery = $engine->alterationOverrideQuery;
            }
        }
    }

    /*
     * Ende Suchmaschinenerstellung und Ergebniserhalt
     */

    public function parseFormData($auth = true)
    {
        # Sichert, dass der request in UTF-8 formatiert ist
        if (\Request::input('encoding', 'utf8') !== "utf8") {
            # In früheren Versionen, als es den Encoding Parameter noch nicht gab, wurden die Daten in ISO-8859-1 übertragen
            $input = \Request::all();
            foreach ($input as $key => $value) {
                $input[$key] = mb_convert_encoding("$value", "UTF-8", "ISO-8859-1");
            }
            \Request::replace($input);
        }

        $this->url = \Request::url();
        $this->fullUrl = \Request::fullUrl();
        # Zunächst überprüfen wir die eingegebenen Einstellungen:

        # Suma-File
        if ($this->dummy) {
            $this->sumaFile = \config_path("stress.json");
        } else {
            $this->sumaFile = \config_path("sumas.json");
        }

        if (!file_exists($this->sumaFile)) {
            die(trans('metaGer.formdata.cantLoad'));
        } else {
            $this->sumaFile = json_decode(file_get_contents($this->sumaFile));
        }
        # Sucheingabe
        $this->eingabe = trim(\Request::input('eingabe', ''));
        $this->q = $this->eingabe;

        $this->out = \Request::input("out", "");

        // Check if request header "Sec-Fetch-Dest" is set
        $this->framed = false;
        if (\Request::header("Sec-Fetch-Dest") === "iframe") {
            $this->framed = true;
        } elseif (\Request::input("out", "") === "results-with-style") {
            $this->framed = true;
        } elseif (\Request::input("iframe", "false") === "1") {
            $this->framed = true;
        }
        unset(app(Request::class)["iframe"]);

        # IP
        $this->ip = $this->anonymizeIp(\Request::ip());

        $this->useragent = \Request::header('User-Agent');

        # Language
        if (isset($_SERVER['HTTP_LANGUAGE'])) {
            $this->language = $_SERVER['HTTP_LANGUAGE'];
        } else {
            $this->language = "";
        }

        # Page
        $this->page = 1;
        # Lang
        $this->lang = \Request::input('lang', 'all');
        if ($this->lang !== "de" && $this->lang !== "en" && $this->lang !== "all") {
            $this->lang = "all";
        }

        $this->agent = new Agent();
        $this->mobile = $this->agent->isMobile();
        # Sprüche
        if (Localization::getLanguage() !== "de" || (Cookie::has('zitate') && Cookie::get('zitate') === 'off')) {
            $this->sprueche = 'off';
        } else {
            $this->sprueche = 'on';
        }
        if (\Request::filled('zitate') && \Request::input('zitate') === 'on' || \Request::input('zitate') === 'off') {
            $this->sprueche = \Request::input('quotes');
        }

        $this->newtab = \Request::input('new_tab', Cookie::get('new_tab'));
        if ($this->newtab === "on") {
            $this->newtab = "_blank";
        } elseif ($this->framed) {
            $this->newtab = "_top";
        } else {
            $this->newtab = "_self";
        }
        if (\Request::filled("key") && \Request::input('key') === config("metager.metager.keys.uni_mainz")) {
            $this->newtab = "_blank";
        }
        # Theme
        $this->theme = preg_replace("/[^[:alnum:][:space:]]/u", '', \Request::input('theme', 'default'));
        # Ergebnisse pro Seite:
        $this->resultCount = \Request::input('resultCount', '20');

        if (\Request::filled('minism') && (\Request::filled('fportal') || \Request::filled('harvest'))) {
            $input = \Request::all();
            $newInput = [];
            foreach ($input as $key => $value) {
                if ($key !== "fportal" && $key !== "harvest") {
                    $newInput[$key] = $value;
                }
            }
            \Request::replace($newInput);
        }

        if ($this->resultCount <= 0 || $this->resultCount > 200) {
            $this->resultCount = 1000;
        }
        if (\Request::filled('onenewspageAll') || \Request::filled('onenewspageGermanyAll')) {
            $this->time = 5000;
            $this->cache = "cache";
        }
        if (\Request::filled('password')) {
            $this->password = \Request::input('password');
        }

        $this->queryFilter = [];

        // Remove Inputs that are not used
        $this->request = \Request::replace(\Request::except(['uid']));

        // Disable freshness filter if custom freshness filter isset
        if ($this->request->filled("ff") && $this->request->filled("f")) {
            $this->request = $this->request->replace($this->request->except(["f"]));
        }
        // Remove custom time filter if either of the dates isn't set or is not a date
        if ($this->request->input("fc") === "on") {
            if (!$this->request->filled("ff") || !$this->request->filled("ft")) {
                $this->request = $this->request->replace($this->request->except(["fc", "ff", "ft"]));
            } else {
                $ff = $this->request->input("ff");
                $ft = $this->request->input("ft");
                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $ff) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $ft)) {
                    $this->request = $this->request->replace($this->request->except(["fc", "ff", "ft"]));
                } else {
                    // Now Check if there is something wrong with the dates
                    $from = $this->request->input("ff");
                    $to = $this->request->input("ft");
                    $changed = false;
                    $from = Carbon::createFromFormat("Y-m-d H:i:s", $from . " 00:00:00");
                    $to = Carbon::createFromFormat("Y-m-d H:i:s", $to . " 00:00:00");

                    if ($from > Carbon::now()) {
                        $from = Carbon::now();
                        $changed = true;
                    }
                    if ($to > Carbon::now()) {
                        $to = Carbon::now();
                        $changed = true;
                    }
                    if ($from > $to) {
                        $tmp = $to;
                        $to = $from;
                        $from = $tmp;
                        $changed = true;
                    }

                    # Bing only allows a maximum of 1 year in the past
                    # Verify the parameters
                    $yearAgo = Carbon::now()->subYear();
                    if ($from < $yearAgo) {
                        $from = clone $yearAgo;
                        $changed = true;
                    }
                    if ($to < $yearAgo) {
                        $to = clone $yearAgo;
                        $changed = true;
                    }

                    if ($changed) {
                        $oldParameters = $this->request->all();
                        $oldParameters["ff"] = $from->format("Y-m-d");
                        $oldParameters["ft"] = $to->format("Y-m-d");
                        $this->request = $this->request->replace($oldParameters);
                    }
                }
            }
        } elseif ($this->request->filled("ff") || $this->request->filled("ft")) {
            $this->request = $this->request->replace($this->request->except(["fc", "ff", "ft"]));
        }

        $this->out = \Request::input('out', "html");
        # Standard output format html
        if ($this->out !== "html" && $this->out !== "json" && $this->out !== "results" && $this->out !== "results-with-style" && $this->out !== "result-count" && $this->out !== "atom10" && $this->out !== "api" && $this->out !== "rss20") {
            $this->out = "html";
        }
        # Wir schalten den Cache aus, wenn die Ergebniszahl überprüft werden soll
        #   => out=result-count
        # Ist dieser Parameter gesetzt, so soll überprüft werden, wie viele Ergebnisse wir liefern.
        # Wenn wir gecachte Ergebnisse zurück liefern würden, wäre das nicht sonderlich klug, da es dann keine Aussagekraft hätte
        # ob MetaGer funktioniert (bzw. die Fetcher laufen)
        # Auch ein Log sollte nicht geschrieben werden, da es am Ende ziemlich viele Logs werden könnten.
        if ($this->out === "result-count") {
            $this->canCache = false;
            $this->shouldLog = false;
        } else {
            $this->shouldLog = true;
        }
    }

    public function createQuicktips()
    {
        # Die quicktips werden als job erstellt und zur Abarbeitung freigegeben
        if (app(SearchSettings::class)->fokus !== "bilder") {
            $quicktips = new \App\Models\Quicktips\Quicktips($this->q, LaravelLocalization::getCurrentLocale(), $this->getTime(), app(SearchSettings::class)->enableQuotes);
            return $quicktips;
        } else {
            return null;
        }
    }



    private function anonymizeIp($ip)
    {
        if (str_contains($ip, ":")) {
            # IPv6
            # Check if IP contains "::"
            if (str_contains($ip, "::")) {
                $ipAr = explode("::", $ip);
                $count = 0;
                if (!empty($ipAr[0])) {
                    $ipLAr = explode(":", $ipAr[0]);
                    $count += sizeof($ipLAr);
                }
                if (!empty($ipAr[1])) {
                    $ipRAr = explode(":", $ipAr[1]);
                    $count += sizeof($ipRAr);
                }

                $fillUp = "";
                for ($i = 1; $i <= (8 - $count); $i++) {
                    $fillUp .= "0000:";
                }
                $fillUp = rtrim($fillUp, ":");

                $ip = $ipAr[0] . ":" . $fillUp . ":" . $ipAr[1];
                $ip = trim($ip, ":");
            }
            $resultIp = "";
            foreach (explode(":", $ip) as $block) {
                $blockAr = str_split($block);
                while (sizeof($blockAr) < 4) {
                    array_unshift($blockAr, "0");
                }
                $resultIp .= implode("", $blockAr) . ":";
            }
            $resultIp = rtrim($resultIp, ":");

            # Now that we have the expanded Form of the IPv6 we can anonymize it
            # we use the first 48 bit and replace the rest with zeros
            # Our expanded IPv6 now has 8 blocks with 16 bit each
            # xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx
            # We just want to use the first thre blocks and replace the rest with zeros
            # xxxx:xxxx:xxxx::
            $resultIp = preg_replace("/^([^:]+:[^:]+:[^:]+).*$/", "$1::", $resultIp);
            return $resultIp;
        } else {
            # IPv4
            return preg_replace("/(\d+)\.(\d+)\.\d+.\d+/s", "$1.$2.0.0", $ip);
        }
    }

    public function checkSpecialSearches(Request $request)
    {
        $this->searchCheckPhrase();

        $this->searchCheckHostBlacklist($request);
        $this->searchCheckDomainBlacklist($request);
        $this->searchCheckUrlBlacklist();
        $this->searchCheckStopwords($request);
        $this->searchCheckNoSearch();

        # Check for self-harm related searches
        $triggers = ["suizid", "selbstmord", "Selbstmordgedanken", "selbsttötung", "Freitod", "Sterbehilfe", "umbringen", "suizidale", "depressionen", "depressiv", "selbstverletzung", "einsam", "einsamkeit", "self harm", "self injury", "suicidal", "suicidality", "self-murder", "self-slaughter", "self-destruction", "self-homocide", "self-murderer", "kill oneself", "lonely", "depression"];
        foreach ($triggers as $i => $trigger) {
            if (stripos($this->q, $trigger) !== false) {
                $this->htmlwarnings[] = trans('metaGer.prevention.phrase', ['prevurl' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "prevention")]);
                break;
            }
        }
    }

    private function searchCheckPhrase()
    {
        $p = "";
        $tmp = $this->q;
        // matches '[... ]"test satz"[ ...]'
        while (preg_match("/(^|.*?\s)\"(.+)\"(\s.*|$)/si", $tmp, $match)) {
            $tmp = $match[1] . $match[3];
            $this->phrases[] = $match[2];
        }

        foreach ($this->phrases as $phrase) {
            $p .= "\"$phrase\", ";
        }
        $p = rtrim($p, ", ");
        if (sizeof($this->phrases) > 0) {
            $this->warnings[] = trans('metaGer.formdata.phrase', ['phrase' => $p]);
        }
    }

    private function searchCheckHostBlacklist($request)
    {
        // matches '[... ]-site:test.de[ ...]'
        while (preg_match("/(^|.*?\s)-site:([^\*\s]\S*)(\s.*|$)/si", $this->q, $match)) {
            $this->hostBlacklist[] = $match[2];
            $this->q = $match[1] . $match[3];
        }
        # Overwrite Setting if it's submitted via Parameter
        if ($request->has('blacklist')) {
            $this->hostBlacklist = [];
            $blacklistString = trim($request->input('blacklist'));
            if (strpos($blacklistString, ",") !== false) {
                $blacklistArray = explode(',', $blacklistString);
                foreach ($blacklistArray as $blacklistElement) {
                    $blacklistElement = trim($blacklistElement);
                    if (strpos($blacklistElement, "*") !== 0) {
                        $this->hostBlacklist[] = $blacklistElement;
                    }
                }
            } elseif (strpos($blacklistString, "*") !== 0) {
                $this->hostBlacklist[] = $blacklistString;
            }
        }

        $this->hostBlacklist = array_unique($this->hostBlacklist);

        // print the host blacklist as a user warning
        if (sizeof($this->hostBlacklist) > 0) {
            if (sizeof($this->hostBlacklist) <= 3) {
                $hostString = "";
                foreach ($this->hostBlacklist as $host) {
                    $hostString .= $host . ", ";
                }
                $hostString = rtrim($hostString, ", ");
                $this->warnings[] = trans('metaGer.formdata.hostBlacklist', ['host' => $hostString]);
            } else {
                $this->warnings[] = trans('metaGer.formdata.hostBlacklistCount', ['count' => sizeof($this->hostBlacklist)]);
            }
        }
    }

    private function searchCheckDomainBlacklist($request)
    {
        // matches '[... ]-site:*.test.de[ ...]'
        while (preg_match("/(^|.*?\s)-site:\*\.(\S+)(\s.*|$)/si", $this->q, $match)) {
            $this->domainBlacklist[] = $match[2];
            $this->q = $match[1] . $match[3];
        }
        # Overwrite Setting if it's submitted via Parameter
        if ($request->has('blacklist')) {
            $this->domainBlacklist = [];
            $blacklistString = trim($request->input('blacklist'));
            if (strpos($blacklistString, ",") !== false) {
                $blacklistArray = explode(',', $blacklistString);
                foreach ($blacklistArray as $blacklistElement) {
                    $blacklistElement = trim($blacklistElement);
                    if (strpos($blacklistElement, "*.") === 0) {
                        $this->domainBlacklist[] = substr($blacklistElement, strpos($blacklistElement, "*.") + 2);
                    }
                }
            } elseif (strpos($blacklistString, "*.") === 0) {
                $this->domainBlacklist[] = substr($blacklistString, strpos($blacklistString, "*.") + 2);
            }
        }
        foreach (Cookie::get() as $key => $value) {
            $regexUrl = '#^(\*\.)?[a-z0-9-]+(\.[a-z0-9]+)?(\.[a-z0-9]{2,})$#';
            if (preg_match('/_blpage[0-9]+$/', $key) === 1 && stripos($key, app(SearchSettings::class)->fokus) !== false && preg_match($regexUrl, $value) === 1) {
                $this->domainBlacklist[] = substr($value, 0, 255);
            } elseif (preg_match('/_blpage$/', $key) === 1 && stripos($key, app(SearchSettings::class)->fokus) !== false) {
                $blacklistItems = explode(",", $value);
                foreach ($blacklistItems as $blacklistItem) {

                    if (preg_match($regexUrl, $blacklistItem) === 1) {
                        $this->domainBlacklist[] = substr($blacklistItem, 0, 255);
                    }
                }
            }
        }

        $this->domainBlacklist = array_unique($this->domainBlacklist);

        // print the domain blacklist as a user warning
        if (sizeof($this->domainBlacklist) > 0) {
            if (sizeof($this->domainBlacklist) <= 3) {
                $domainString = "";
                foreach ($this->domainBlacklist as $domain) {
                    $domainString .= $domain . ", ";
                }
                $domainString = rtrim($domainString, ", ");
                $this->warnings[] = trans('metaGer.formdata.domainBlacklist', ['domain' => $domainString]);
            } else {
                $this->warnings[] = trans('metaGer.formdata.domainBlacklistCount', ['count' => sizeof($this->domainBlacklist)]);
            }
        }
    }

    private function searchCheckUrlBlacklist()
    {
        // matches '[... ]-site:*.test.de[ ...]'
        while (preg_match("/(^|.*?\s)-url:(\S+)(\s.*|$)/si", $this->q, $match)) {
            $this->urlBlacklist[] = $match[2];
            $this->q = $match[1] . $match[3];
        }
        // print the url blacklist as a user warning
        if (sizeof($this->urlBlacklist) > 0) {
            $urlString = "";
            foreach ($this->urlBlacklist as $url) {
                $urlString .= $url . ", ";
            }
            $urlString = rtrim($urlString, ", ");
            $this->warnings[] = trans('metaGer.formdata.urlBlacklist', ['url' => $urlString]);
        }
    }

    private function searchCheckStopwords($request)
    {
        $oldQ = $this->q;

        $tmp = $this->q;
        // matches '[... ]"test satz"[ ...]'
        // In order to avoid "finding" stopwords inside of phrase searches only strings outside of quotation marks should be checked
        while (preg_match("/(^|.*?\s)\"(.+)\"(\s.*|$)/si", $tmp, $match)) {
            $tmp = $match[1] . $match[3];
        }

        // matches '[... ]-test[ ...]'
        $words = preg_split("/\s+/si", $tmp);
        $newQ = $this->q;
        foreach ($words as $word) {
            if (preg_match("/^-[a-zA-Z0-9]/", $word)) {
                $this->stopWords[] = substr($word, 1);
                $newQ = str_ireplace($word, "", $newQ);
            }
        }
        $newQ = preg_replace("/(\s)\s+/", "$1", $newQ);
        $this->q = trim($newQ);
        # Overwrite Setting if submitted via Parameter
        if ($request->has('stop')) {
            $this->stopWords = [];
            $stop = trim($request->input('stop'));
            if (strpos($stop, ',') !== false) {
                $stopArray = explode(',', $stop);
                foreach ($stopArray as $stopElement) {
                    $stopElement = trim($stopElement);
                    $this->stopWords[] = $stopElement;
                }
            } else {
                $this->stopWords[] = $stop;
            }
            $this->q = $oldQ;
        }
        // print the stopwords as a user warning
        if (sizeof($this->stopWords) > 0) {
            $stopwordsString = "";
            foreach ($this->stopWords as $stopword) {
                $stopwordsString .= $stopword . ", ";
            }
            $stopwordsString = rtrim($stopwordsString, ", ");
            $this->warnings[] = trans('metaGer.formdata.stopwords', ['stopwords' => $stopwordsString]);
        }
    }

    private function searchCheckNoSearch()
    {
        if ($this->q === "") {
            $this->warnings[] = trans('metaGer.formdata.noSearch');
        }
    }

    public function nextSearchLink()
    {
        if (isset($this->next) && isset($this->next['engines']) && count($this->next['engines']) > 0) {
            $requestData = $this->request->except(['page', 'out', 'submit-query', 'mgv']);
            if ($this->request->input('out', '') !== "results" && $this->request->input('out', '') !== '') {
                $requestData["out"] = $this->request->input('out');
            }
            $requestData['next'] = $this->getSearchUid();
            $link = action('MetaGerSearch@search', $requestData);
        } else {
            $link = "#";
        }
        return $link;
    }

    public function rankAll()
    {
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            $engine->rank();
        }
    }

    # Hilfsfunktionen
    public function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function removeInvalids()
    {
        $results = [];
        foreach ($this->results as $result) {
            if ($result->isValid($this)) {
                $results[] = $result;
            }
        }
        $this->results = $results;
    }

    public function atLeastOneSearchengineSelected(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            if ($this->startsWith($key, 'engine')) {
                return true;
            }
        }
        return false;
    }

    public function popAd()
    {
        if (count($this->ads) > 0) {
            return array_shift($this->ads);
        } else {
            return null;
        }
    }

    public function canCache()
    {
        return $this->canCache;
    }

    public static function getMGLogFile()
    {
        $logpath = storage_path("logs/metager/" . date("Y") . "/" . date("m") . "/");
        if (!file_exists($logpath)) {
            mkdir($logpath, 0777, true);
        }
        $logpath .= date("d") . ".log";
        return $logpath;
    }

    public function setNext($next)
    {
        $this->next = $next;
    }

    public function addLink($link)
    {
        if (strpos($link, "http://") === 0) {
            $link = substr($link, 7);
        }

        if (strpos($link, "https://") === 0) {
            $link = substr($link, 8);
        }

        if (strpos($link, "www.") === 0) {
            $link = substr($link, 4);
        }

        $link = trim($link, "/");
        $hash = md5($link);
        if (isset($this->addedLinks[$hash])) {
            return false;
        } else {
            $this->addedLinks[$hash] = 1;
            return true;
        }
    }

    public function addHostCount($host)
    {
        $hash = md5($host);
        if (isset($this->addedHosts[$hash])) {
            $this->addedHosts[$hash] += 1;
        } else {
            $this->addedHosts[$hash] = 1;
        }
    }

    # Generators

    public function generateSearchLink($fokus, $results = true)
    {
        $except = ['page', 'next', 'out', 'submit-query', 'mgv', 'ua'];
        # Remove every Filter
        foreach ($this->sumaFile->filter->{"parameter-filter"} as $filterName => $filter) {
            $except[] = $filter->{"get-parameter"};
        }
        $requestData = $this->request->except($except);
        $requestData['focus'] = $fokus;

        $link = action('MetaGerSearch@search', $requestData);
        return $link;
    }

    public function generateEingabeLink($eingabe)
    {
        $except = ['page', 'next', 'out', 'eingabe', 'submit-query', 'mgv', 'ua'];
        $requestData = $this->request->except($except);

        $requestData['eingabe'] = $eingabe;

        $link = action('MetaGerSearch@search', $requestData);
        return $link;
    }

    public function generateQuicktipLink()
    {
        $link = action('MetaGerSearch@quicktips');

        return $link;
    }

    public function generateSiteSearchLink($host)
    {
        $host = urlencode($host);
        $requestData = $this->request->except(['page', 'out', 'next', 'submit-query', 'mgv', 'ua']);
        $requestData['eingabe'] .= " site:$host";
        $requestData['focus'] = "web";
        $link = action('MetaGerSearch@search', $requestData);
        return $link;
    }

    public function generateRemovedHostLink($host)
    {
        $host = urlencode($host);
        $requestData = $this->request->except(['page', 'out', 'next', 'submit-query', 'mgv', 'ua']);
        $requestData['eingabe'] .= " -site:$host";
        $link = action('MetaGerSearch@search', $requestData);
        return $link;
    }

    public function generateRemovedDomainLink($domain)
    {
        $domain = urlencode($domain);
        $requestData = $this->request->except(['page', 'out', 'next', 'submit-query', 'mgv', 'ua']);
        $requestData['eingabe'] .= " -site:*.$domain";
        $link = action('MetaGerSearch@search', $requestData);
        return $link;
    }

    public function getUnFilteredLink()
    {
        $requestData = $this->request->except(['lang']);
        $requestData['lang'] = "all";
        $link = action('MetaGerSearch@search', $requestData);
        return $link;
    }

    # Komplexe Getter

    public function getHostCount($host)
    {
        $hash = md5($host);
        if (isset($this->addedHosts[$hash])) {
            return $this->addedHosts[$hash];
        } else {
            return 0;
        }
    }

    public function getSearchUid()
    {
        return $this->searchUid;
    }

    public function getSavedSettingCount()
    {
        $cookies = Cookie::get();
        $count = 0;

        $sumaFile = MetaGer::getLanguageFile();
        $sumaFile = json_decode(file_get_contents($sumaFile), true);

        foreach ($cookies as $key => $value) {
            if (in_array($key, [$this->getFokus() . "_setting_", $this->getFokus() . "_engine_", $this->getFokus() . "_blpage"])) {
                $count++;
                continue;
            }
        }
        return $count;
    }

    # Einfache Getter

    public function getNext()
    {
        return $this->next;
    }

    public function getOut()
    {
        return $this->out;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getNewtab()
    {
        return $this->newtab;
    }

    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * @return \App\Models\Result[]
     */
    public function getResults()
    {
        return $this->results;
    }

    public function getFokus()
    {
        return app(SearchSettings::class)->fokus;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getUserAgent()
    {
        return $this->useragent;
    }

    public function getEingabe()
    {
        return $this->eingabe;
    }

    public function getQ()
    {
        return $this->q;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public static function getLanguageFile()
    {
        return config_path('sumas.json');
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function getAvailableFoki()
    {
        return $this->availableFoki;
    }

    public function getSprueche()
    {
        return $this->sprueche;
    }

    public function getPhrases()
    {
        return $this->phrases;
    }
    public function getPage()
    {
        return app(SearchSettings::class)->page;
    }

    public function getSumaFile()
    {
        return $this->sumaFile;
    }

    public function getTotalResultCount()
    {
        return number_format($this->totalResults, 0, ",", ".");
    }

    public function getQueryFilter()
    {
        return $this->queryFilter;
    }

    public function getParameterFilter()
    {
        return $this->parameterFilter;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getUserHostBlacklist()
    {
        return $this->hostBlacklist;
    }

    public function getUserDomainBlacklist()
    {
        return $this->domainBlacklist;
    }

    public function getUserUrlBlacklist()
    {
        return $this->urlBlacklist;
    }

    public function getDomainBlacklist()
    {
        return $this->domainsBlacklisted;
    }

    public function getBlacklistDescriptionUrl()
    {
        return $this->blacklistDescriptionUrl;
    }

    public function getUrlBlacklist()
    {
        return $this->urlsBlacklisted;
    }

    public function getLanguageDetect()
    {
        return $this->languageDetect;
    }

    public function getStopWords()
    {
        return $this->stopWords;
    }

    public function getStartCount()
    {
        return $this->startCount;
    }

    public function getInfos()
    {
        return $this->infos;
    }

    public function getRedisResultWaitingKey()
    {
        return $this->redisResultWaitingKey;
    }

    public function getRedisResultEngineList()
    {
        return $this->redisResultEngineList;
    }

    public function getRedisEngineResult()
    {
        return $this->redisEngineResult;
    }
    public function getRedisCurrentResultList()
    {
        return $this->redisCurrentResultList;
    }

    /**
     * @return SearchEngine
     */
    public function getEngines()
    {
        return $this->engines;
    }

    public function isMobile()
    {
        return $this->mobile;
    }

    public function isApiAuthorized()
    {
        return $this->apiAuthorized;
    }

    public function setApiAuthorized($authorized)
    {
        $this->apiAuthorized = $authorized;
    }

    public function isFramed()
    {
        return $this->framed;
    }

    public function isDummy()
    {
        return $this->dummy;
    }

    public function setDummy($dummy)
    {
        $this->dummy = $dummy;
    }
}