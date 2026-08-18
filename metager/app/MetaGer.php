<?php

namespace App;

use App\Models\Configuration\SearchEngineRegistry;
use App\Models\Configuration\Searchengines;
use App\Models\Result;
use App\Search\LinkBuilder;
use App\Search\QueryParser;
use App\Search\ResponseFactory;
use App\Search\ResultDeduplicator;
use App\Search\ResultRanker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Predis\Connection\ConnectionException;

class MetaGer
{
    const FETCHQUEUE_KEY = "fetcher.queue";

    # Schema-Version von `out=json`. Additive Änderungen (neue Felder) lassen
    # sie unverändert; Umbenennen oder Entfernen eines Feldes erhöht sie.
    const API_SCHEMA_VERSION = 1;

    # JSON_INVALID_UTF8_SUBSTITUTE: die Texte stammen aus fremden Parsern und
    # sind nicht garantiert sauberes UTF-8. Ohne das Flag gäbe json_encode
    # false zurück, und eine einzige kaputte Beschreibung würde die ganze
    # Suche zu einem Fehler machen. Gilt für jede Antwort im API-Schema, also
    # auch für die des Nachlade-Pfads (MetaGerSearch@loadMore).
    const JSON_API_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;

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

    public function __construct($hash = "")
    {
        # start timer
        $this->starttime = microtime(true);

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

    /**
     * Die fertige Suche in der angeforderten Form ausliefern.
     *
     * Die Formatweiche steckt in Search\ResponseFactory; hier bleibt der Log-
     * Aufruf, weil er zur Suche gehört und nicht zur Antwort.
     */
    public function createView($quicktipResults = [])
    {
        App::make(QueryLogger::class)->createLog();

        return app(ResponseFactory::class)->make($this, $quicktipResults ?: []);
    }

    /**
     * Die Antwort für `out=json`.
     *
     * Liefert wie die übrigen Formate einen String; den Content-Type setzt
     * MetaGerSearch@search anhand von getOut().
     *
     * Das Schema ist versioniert. Neue Felder dürfen jederzeit dazukommen;
     * Umbenennen oder Entfernen erhöht `API_SCHEMA_VERSION`.
     */
    /**
     * Der Rumpf von `out=json` als Array.
     *
     * Getrennt von createJsonView(), weil MetaGerSearch@loadMore dieselbe
     * Antwort um seine eigenen Felder (`finished`, `engines`) ergänzt und
     * selbst kodiert. Zwei Stellen, die dasselbe Schema bauen, wären genau die
     * Doppelung, die die Fokus-Weiche oben schon vermeidet.
     *
     * @param array $additional Zusätzliche Felder; überschreiben gleichnamige.
     */
    public function toApiArray(array $additional = []): array
    {
        $results = [];
        foreach ($this->results as $result) {
            $results[] = $result->toApiArray();
        }

        $news = [];
        foreach ($this->news as $newsResult) {
            $news[] = $newsResult->toApiArray();
        }

        $videos = [];
        foreach ($this->videos as $videoResult) {
            $videos[] = $videoResult->toApiArray();
        }

        $nextPage = $this->nextSearchLink();

        return array_merge([
            "version" => self::API_SCHEMA_VERSION,
            "query" => $this->eingabe,
            "focus" => app(SearchSettings::class)->fokus,
            # Die Such-UID, unter der der Suchzustand eine Stunde lang liegt.
            # Ein Client braucht sie für den Nachlade-Pfad
            # (MetaGerSearch@loadMore) — die Website liest denselben Wert aus
            # <meta name="searchkey">. Aus `nextPage` wäre sie zwar auch zu
            # holen, aber nur solange es eine nächste Seite gibt; ohne eigenes
            # Feld verliert ein Client das Nachladen genau dann, wenn wenige
            # Engines geantwortet haben — also wenn er es am nötigsten hat.
            "searchUid" => $this->getSearchUid(),
            # Die Anzahl der Ergebnisse *in dieser Antwort*, keine Schätzung der
            # Gesamttreffer — die hat MetaGer nicht. Der Atom-Feed schreibt
            # denselben Wert in opensearch:totalResults, was dort irreführend
            # ist; hier heißt das Feld deshalb, was es enthält.
            "resultCount" => count($results),
            # Fertige URL für die nächste Seite, inklusive Such-UID, oder null
            # wenn keine weitere Seite existiert.
            "nextPage" => $nextPage === "#" ? null : $nextPage,
            "searchTime" => round(microtime(true) - $this->starttime, 2),
            "results" => $results,
            # Passende Nachrichten und Videos zur Suche, die die Web-Engines
            # nebenbei mitliefern (Brave, Serper). Kein zweiter Suchlauf: sie
            # stehen ohnehin schon da. Auf der Website schiebt
            # `resultpages/results.blade.php` sie zwischen Ergebnis 3 und 4
            # bzw. 6 und 7 in die Liste; ein Client, der das nicht will, kann
            # sie ignorieren, aber ohne dieses Feld hat er die Wahl nicht.
            #
            # Dieselbe Struktur wie ein Ergebnis. Sie sind Result-Objekte, und
            # ein eigener Typ für "Ergebnis, aber mit weniger Feldern" würde
            # den Clients nur zusätzlichen Code abverlangen, ohne ihnen etwas
            # zu sagen. Ihr Datum steht in `dateString` (bei ihnen `age`).
            "news" => $news,
            "videos" => $videos,
            # Immer leer. MetaGer zeigt keine Werbung mehr, und keine Suchmaschine
            # liefert noch welche; das Feld bleibt nur, weil sein Wegfall die
            # Schema-Version erhöht — das passiert in einem eigenen Commit, damit
            # bestehende Clients nicht mit dem Aufräumen brechen.
            "ads" => [],
            # Normale Zustände, keine Fehler: leere Ergebnisliste mit Hinweis.
            "warnings" => array_values($this->warnings),
            "errors" => array_values($this->errors),
        ], $additional);
    }

    /**
     * Turn what the engines answered into the list the page shows.
     *
     * Order matters and is not obvious, so it is spelled out here: results are
     * ranked before they are filtered so that ranking sees every engine's view
     * of a page, and filtered before they are deduplicated so that a blacklisted
     * copy cannot become the surviving one.
     */
    public function prepareResults()
    {
        $this->combineResults();

        $this->results = app(ResultRanker::class)->order($this->results);

        $this->results = $this->validOnly($this->results);
        $this->videos = $this->validOnly($this->videos);
        $this->news = $this->validOnly($this->news);

        $this->results = app(ResultDeduplicator::class)
            ->deduplicate($this->results, app(SearchSettings::class)->fokus);

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

    /**
     * Drop everything this search is not allowed to show.
     *
     * Result::isValid needs the MetaGer to ask, because what is filtered out
     * depends on the search: the user's own host/domain/url blacklists, the
     * operator blacklists and the special-search restrictions all live here.
     *
     * @param Result[] $results
     * @return Result[]
     */
    private function validOnly(array $results): array
    {
        return array_values(array_filter($results, fn(Result $result) => $result->isValid($this)));
    }

    /**
     * Take the results off the engines and put them in one pile.
     *
     * Cloned, not referenced: the engine objects go into the loader cache for
     * load-more to pick up again, so everything from here on has to be able to
     * mutate a result — deduplication merges copies into one — without editing
     * what that engine hands out next time.
     */
    private function combineResults()
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
            foreach ($engine->news as $news) {
                $this->news[] = clone $news;
            }
            foreach ($engine->videos as $video) {
                $this->videos[] = clone $video;
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

    public function isBildersuche()
    {
        return app(SearchSettings::class)->fokus === "bilder";
    }

    public function sumaIsDisabled($suma)
    {
        return
            isset($suma['disabled'])
            && $suma['disabled']->__toString() === "1";
    }

    /**
     * Highest result count any engine reported, from Search\EngineOrchestrator.
     *
     * A maximum rather than a sum: engines overlap heavily, so adding them up
     * would claim more distinct results than exist.
     */
    public function reportTotalResults(int $count): void
    {
        $this->totalResults = max($this->totalResults, $count);
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
        $this->sumaFile = app(SearchEngineRegistry::class);
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

        $this->agent = app(\App\Support\Browser::class);
        $this->mobile = $this->agent->isMobile();
        # Sprüche
        if (app(\App\SearchSettings::class)->zitate) {
            $this->sprueche = 'on';
        } else {
            $this->sprueche = 'off';
        }

        $this->newtab = app(\App\SearchSettings::class)->newtab;
        if ($this->newtab === true) {
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

        $this->request = app(QueryParser::class)->sanitizeFilters($this->request);

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

    /**
     * Read the operators out of the query and hand the results to the search.
     *
     * The parsing is in Search\QueryParser; what stays here is the assignment,
     * because these eight fields are what the views and Result::isValid read
     * off the MetaGer object.
     */
    public function checkSpecialSearches(Request $request)
    {
        $query = app(QueryParser::class)->parse($this->q, $request);

        $this->q = $query->q;
        $this->phrases = $query->phrases;
        $this->hostBlacklist = $query->hostBlacklist;
        $this->domainBlacklist = $query->domainBlacklist;
        $this->urlBlacklist = $query->urlBlacklist;
        $this->stopWords = $query->stopWords;

        $this->warnings = array_merge($this->warnings, $query->warnings);
        $this->htmlwarnings = array_merge($this->htmlwarnings, $query->htmlWarnings);
    }

    public function nextSearchLink()
    {
        if (!isset($this->next["engines"]) || count($this->next["engines"]) === 0) {
            return "#";
        }

        return $this->links()->nextPage($this->getSearchUid());
    }

    # Hilfsfunktionen
    public function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
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

    public function canCache()
    {
        return $this->canCache;
    }

    public static function getMGLogFile()
    {
        $logpath = storage_path("metager/" . date("Y") . "/" . date("m") . "/");
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
    #
    # All of them are the same search with one thing changed, and all of them
    # are built by Search\LinkBuilder. They stay here as one-liners because the
    # blades call them on $metager.

    public function generateSearchLink($fokus, $results = true)
    {
        return $this->links()->forFokus($fokus);
    }

    public function generateEingabeLink($eingabe)
    {
        return $this->links()->forQuery($eingabe);
    }

    public function generateSiteSearchLink($host)
    {
        return $this->links()->restrictedToHost($host);
    }

    public function generateRemovedHostLink($host)
    {
        return $this->links()->withoutHost($host);
    }

    public function generateRemovedDomainLink($domain)
    {
        return $this->links()->withoutDomain($domain);
    }

    public function getUnFilteredLink()
    {
        return $this->links()->everyLanguage();
    }

    private function links(): LinkBuilder
    {
        return new LinkBuilder($this->request);
    }

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
        $count = sizeof(app(SearchSettings::class)->user_settings) + sizeof(app(Searchengines::class)->user_settings);
        return $count;
    }

    # Einfache Getter

    public function getNext()
    {
        return $this->next;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * The request as the search reads it — parameters already scrubbed, so not
     * the same object as the incoming one. Search\ResponseFactory needs it to
     * work out which engines the user ticked by hand.
     */
    public function getRequest(): Request
    {
        return $this->request;
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

}