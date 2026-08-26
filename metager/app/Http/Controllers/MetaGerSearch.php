<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HttpCache;
use App\Localization;
use App\MetaGer;
use App\Models\Authorization\Authorization;
use App\Models\Configuration\Searchengines;
use App\Models\Quicktips\Quicktips;
use App\PrometheusExporter;
use App\QueryTimer;
use App\Search\EngineOrchestrator;
use App\Search\ResultRanker;
use App\SearchSettings;
use Auth;
use Blade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Prometheus\CollectorRegistry;

class MetaGerSearch extends Controller
{
    public function search(Request $request, MetaGer $metager, $timing = false)
    {
        $query_timer = \app()->make(QueryTimer::class);
        $language = Localization::getLanguage();

        $preferredLanguage = array($request->getPreferredLanguage());
        if (!empty($preferredLanguage) && !empty($language)) {
            PrometheusExporter::PreferredLanguage($language, $preferredLanguage);
        }

        if ($request->filled("chrome-plugin")) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin"));
        }

        $settings = app(SearchSettings::class);

        if ($settings->fokus === "maps") {
            return redirect()->to('https://maps.metager.de/' . rawurlencode($settings->q) . '/guess?locale=' . Localization::getLanguage());
        }

        # If there is no query parameter we redirect to the startpage
        if (empty(trim($settings->q))) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
        }

        # Nach Spezialsuchen überprüfen:
        $query_timer->observeStart("Search_CheckSpecialSearches");
        $metager->checkSpecialSearches($request);
        $query_timer->observeEnd("Search_CheckSpecialSearches");

        # Search query can be empty after parsing the formdata
        # we will cancel the search in that case and show an error to the user
        if (empty($settings->q)) {
            return $metager->createView();
        }

        if (empty(app(Searchengines::class)->getEnabledSearchengines())) {
            return redirect(route("settings", ["focus" => $settings->fokus]) . "#engines");
        }

        app(Searchengines::class)->checkPagination();

        $query_timer->observeStart("Search_CreateQuicktips");

        /** @var Quicktips */
        $quicktips = $metager->createQuicktips();
        $query_timer->observeEnd("Search_CreateQuicktips");

        $orchestrator = app(EngineOrchestrator::class);

        $query_timer->observeStart("Search_StartSearch");
        $orchestrator->start($metager);
        $query_timer->observeEnd("Search_StartSearch");

        $query_timer->observeStart("Search_WaitForMainResults");
        $orchestrator->waitForMainResults($metager);
        $query_timer->observeEnd("Search_WaitForMainResults");

        $query_timer->observeStart("Search_RetrieveResults");
        $orchestrator->collectResults($metager);
        $query_timer->observeEnd("Search_RetrieveResults");

        // Versuchen die Ergebnisse der Quicktips zu laden
        if ($quicktips !== null) {
            $query_timer->observeStart("Search_LoadQuicktips");
            $quicktips->loadResults();
            $query_timer->observeEnd("Search_LoadQuicktips");
        }

        # Alle Ergebnisse vor der Zusammenführung ranken:
        $query_timer->observeStart("Search_RankAll");
        app(ResultRanker::class)->rankEngineResults();
        $query_timer->observeEnd("Search_RankAll");

        # Ergebnisse der Suchmaschinen kombinieren:
        $query_timer->observeStart("Search_PrepareResults");
        $metager->prepareResults();
        $query_timer->observeEnd("Search_PrepareResults");

        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            if ($engine->loaded) {
                $engine->setNew(false);
                $engine->markNew();
            }
        }
        $query_timer->observeStart("Search_CacheFiller");
        try {
            $authorization = app(Authorization::class);
            $searchengines = app(Searchengines::class);
            $settings = app(SearchSettings::class);
            Cache::put("loader_" . $metager->getSearchUid(), [
                "metager" => [
                    "authorization" => $authorization,
                    "searchengines" => $searchengines,
                    "settings" => $settings,
                    "quicktips" => $quicktips,
                ],
                "engines" => $metager->getEngines(),
            ], 60 * 60);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        $query_timer->observeEnd("Search_CacheFiller");

        $registry = CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'result_counter', 'counts total number of returned results', []);
        $counter->incBy(sizeof($metager->getResults()));
        $counter = $registry->getOrRegisterCounter('metager', 'query_counter', 'counts total number of search queries', []);
        $counter->inc();

        $query_timer->observeTotal();
        if ($quicktips !== null) {
            $quicktip_results = $quicktips->quicktips;
        } else {
            $quicktip_results = null;
        }
        if ($quicktip_results === null) {
            $quicktip_results = [];
        }

        $csp = "'self'";

        // --- Payment logic for used searchengines ---
        $engines = app(Searchengines::class)->getEnabledSearchengines();
        $rawCost = app(Searchengines::class)->getRawSearchCost();
        $searchCost = app(Searchengines::class)->getSearchCost();
        if (is_array($engines)) {
            // One discharge for the whole search, not one per engine.
            //
            // makePayment() POSTs to the keyserver synchronously, and this runs
            // while the user is waiting for the page — so paying engine by
            // engine put a network round trip on the result path for every paid
            // engine a fokus uses. The keyserver discharges an amount, not an
            // engine, so the sum is the same money in one call.
            //
            // The metrics stay per engine: they are the record of which engine
            // was used, which is the question they answer.
            $due = 0.0;
            foreach ($engines as $engine) {
                // Only pay for engines that are used, not loaded, and not cached
                if (!$engine->cached && $engine->configuration->cost > 0) {
                    // Remove namespace before passing engine to exporter
                    PrometheusExporter::KeyUsed($engine->configuration->cost, preg_replace("/^.*\\\/", "", get_class($engine)), $engine->cached);
                    $due += $engine->configuration->cost;
                }
            }
            if ($due > 0) {
                // All or nothing now, where each engine used to be refused on its
                // own. That is the better failure too: the search has already
                // happened by this point, so charging for part of it was never
                // the more correct outcome.
                if (($user = Auth::guard("key")->user()) !== null) {
                    $user->makePayment($due);
                } else {
                    app(Authorization::class)->makePayment($due);
                }
            }
            // If rawCost < 1, pay the difference between searchCost and rawCost
            if ($rawCost < 1 && $searchCost > $rawCost) {
                $diff = $searchCost - $rawCost;
                app(Authorization::class)->makePayment($diff);
            }
        }

        $headers = [
            // `private`: this page embeds per-user markup (the SafeBrowse link carries the key on
            // a query login), so a shared cache must never hand it to a second user. The ETag is
            // what lets the browser keep caching aggressively while still noticing a changed key
            // or a redeployed frontend bundle — see HttpCache::resultPageEtag.
            "Cache-Control" => HttpCache::resultPageCacheControl(true),
            "ETag" => HttpCache::resultPageEtag($request),
            "Vary" => HttpCache::resultPageVary(),
            "Content-Security-Policy" => "default-src $csp",
            "Last-Modified" => gmdate("D, d M Y H:i:s T"),
        ];
        # Die Feed- und Textausgaben sind kein HTML und brauchen einen passenden Content-Type
        switch ($metager->getOut()) {
            case 'rss20':
                $headers["Content-Type"] = "application/rss+xml; charset=UTF-8";
                break;
            case 'atom10':
            case 'api':
                $headers["Content-Type"] = "application/atom+xml; charset=UTF-8";
                break;
            case 'result-count':
                $headers["Content-Type"] = "text/plain; charset=UTF-8";
                break;
            case 'json':
                $headers["Content-Type"] = "application/json; charset=UTF-8";
                break;
        }

        return response($metager->createView($quicktip_results), 200, $headers);
    }

    /**
     * Firefox suggests https://google.de/search?q=%s as Search-URL
     * which is not MetaGers search but to catch people calling that path
     * on MetaGer lets make it work
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse;
     */
    public function searchGoogle(Request $request)
    {
        $request->merge(["eingabe" => $request->input("q", "")]);
        $request->request->remove('q');
        $resultpage_url = route('resultpage', $request->all());
        return redirect($resultpage_url);
    }

    public function searchTimings(Request $request, MetaGer $metager)
    {
        $request->merge([
            'eingabe' => "Hannover",
        ]);
        return $this->search($request, $metager, true);
    }

    public function loadMore(Request $request)
    {
        /**
         * There are three forms of requests to the resultpage
         * 1. Initial Request: Loads the fastest searchengines and sends them to the user
         * 2. Load more results (with JS): Loads new search engines that answered after the initial request was send
         * 3. Load more results (without JS): Loads new search engines that answered within 1s timeout
         */
        if ($request->filled('loadMore') && $request->filled('script') && $request->input('script') === "yes") {
            return $this->loadMoreJS($request);
        }
    }

    private function loadMoreJS(Request $request)
    {
        \app(SearchSettings::class)->javascript_enabled = true;
        # Create a MetaGer Instance with the supplied hash
        $hash = $request->input('loadMore', '');
        unset($request["loadMore"]);
        unset($request["script"]);

        # Parser Skripte einhängen
        $dir = app_path() . "/Models/parserSkripte/";
        foreach (scandir($dir) as $filename) {
            $path = $dir . $filename;
            if (is_file($path)) {
                require_once $path;
            }
        }

        $cached = Cache::get($hash);
        if ($cached === null) {
            if ($request->header("If-Modified-Since") !== null) {
                return response("", 304, [
                    // Loader state is per-user (it carries the caller's authorization), so a
                    // shared cache must not store it.
                    "Cache-Control" => HttpCache::resultPageCacheControl(true),
                    "Last-Modified" => gmdate("D, d M Y H:i:s T"),
                ]);
            } else {
                return response()->json(['finished' => true]);
            }
        }

        $engines = $cached["engines"];
        $mg = $cached["metager"];

        $metager = new MetaGer(substr($hash, strpos($hash, "loader_") + 7));
        $authorization = $mg["authorization"];
        app()->singleton(Authorization::class, function ($app) use ($authorization) {
            return $authorization;
        });
        $settings = $mg["settings"];
        app()->singleton(SearchSettings::class, function ($app) use ($settings) {
            return $settings;
        });
        $searchengines = $mg["searchengines"];
        app()->singleton(Searchengines::class, function ($app) use ($searchengines) {
            return $searchengines;
        });
        /** @var Quicktips|null */
        $quicktips = $mg["quicktips"];
        if ($quicktips !== null) {
            $quicktips->loadResults();
        }


        # Nach Spezialsuchen überprüfen:
        $metager->checkSpecialSearches($request);

        $engineCountBefore = 0;
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            if ($engine->loaded) {
                $engineCountBefore++;
            }
        }

        # Checks Cache for engine Results
        $orchestrator = app(EngineOrchestrator::class);
        $orchestrator->loadFromCache($metager);
        $orchestrator->collectResults($metager);

        app(ResultRanker::class)->rankEngineResults();
        $metager->prepareResults();

        $result = [
            'finished' => true,
            'results' => "",
            'nextSearchLink' => $metager->nextSearchLink(),
            'imagesearch' => false,
        ];

        if ($quicktips !== null && $quicktips->new) {
            $result["quicktips"] = Blade::render("parts.quicktips", ["quicktips" => $quicktips->quicktips]);
        }

        $newResults = 0;
        $viewResults = [];
        foreach ($metager->getResults() as $index => $resultTmp) {
            $viewResults[] = get_object_vars($resultTmp);
            if ($resultTmp->new) {
                $newResults++;
            }
            if ($metager->getFokus() === "bilder") {
                $result["imagesearch"] = true;
            }
        }

        $finished = true;
        $enginesLoaded = [];
        $enginesLoadedAfter = 0;
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            if (!$engine->loaded) {
                $enginesLoaded[$engine->name] = false;
                $finished = false;
            } else {
                $enginesLoaded[$engine->name] = true;
                $engine->setNew(false);
                $engine->markNew();
                $enginesLoadedAfter++;
            }
        }

        if ($request->header("If-Modified-Since") !== null && $engineCountBefore === $enginesLoadedAfter && !array_key_exists("quicktips", $result)) {
            // Nothing changed but we are not finished yet either
            return response("", 304);
        }

        $result["finished"] = $finished;
        $result["engines"] = $enginesLoaded;

        if ($newResults > 0) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('metager', 'result_counter', 'counts total number of returned results', []);
            $counter->incBy($newResults);
        }
        // Update new Engines
        $authorization = app(Authorization::class);
        $searchengines = app(Searchengines::class);
        $cacheControl = HttpCache::resultPageCacheControl(false);
        if ($finished) {
            Cache::forget("loader_" . $metager->getSearchUid());
            $cacheControl = HttpCache::resultPageCacheControl(true);
        } else {
            Cache::put("loader_" . $metager->getSearchUid(), [
                "metager" => [
                    "authorization" => $authorization,
                    "searchengines" => $searchengines,
                    "settings" => $settings,
                    "quicktips" => $quicktips,
                ],
                "engines" => $metager->getEngines(),
            ], 60 * 60);
        }

        # Nicht-Browser-Clients (die App) bekommen dieselben Ergebnisse im
        # API-Schema statt als gerendertes HTML. Sonst müsste ein nativer
        # Client die Ergebnisseite parsen, um an genau die Treffer zu kommen,
        # die out=json ihm eine Anfrage vorher strukturiert geliefert hat.
        #
        # `results` enthält wie im HTML-Fall die **vollständige, neu gerankte**
        # Liste, nicht nur die neu hinzugekommenen Treffer: das Nachladen kann
        # die Reihenfolge ändern, und eine Differenz ließe sich nicht mehr
        # einsortieren. Was ein Client damit macht, entscheidet er selbst.
        #
        # Quicktips bleiben außen vor — sie existieren nur als Blade-Fragment,
        # und HTML in einer JSON-Antwort wäre wieder genau das Problem, das
        # dieser Zweig löst.
        if ($metager->getOut() === "json") {
            return response()->json($metager->toApiArray([
                # false heißt: es fehlen noch Engines, ein weiterer Aufruf
                # lohnt sich. true heißt, der Suchzustand ist gerade verworfen
                # worden — ein weiterer Aufruf antwortet dann finished ohne
                # Ergebnisse.
                "finished" => $finished,
                # Engine-Name => hat geantwortet. Damit kann ein Client
                # anzeigen, worauf er noch wartet.
                "engines" => $enginesLoaded,
            ]), 200, [
                "Cache-Control" => $cacheControl,
                "Last-Modified" => gmdate("D, d M Y H:i:s T"),
                "Content-Type" => "application/json; charset=UTF-8",
            ], MetaGer::JSON_API_FLAGS);
        }

        $result["results"] = view('resultpages.results')
            ->with('results', $viewResults)
            ->with('eingabe', $metager->getEingabe())
            ->with('mobile', $metager->isMobile())
            ->with('warnings', $metager->warnings)
            ->with('htmlwarnings', $metager->htmlwarnings)
            ->with('errors', $metager->errors)
            ->with('apiAuthorized', $metager->isApiAuthorized())
            ->with('metager', $metager)
            ->with('fokus', $metager->getFokus())->render();

        # JSON encoding will fail if invalid UTF-8 Characters are in this string
        # mb_convert_encoding will remove thise invalid characters for us
        $result = mb_convert_encoding($result, "UTF-8", "UTF-8");
        return response()->json($result, 200, [
            "Cache-Control" => $cacheControl,
            "Last-Modified" => gmdate("D, d M Y H:i:s T"),
        ]);
    }

    public function botProtection($redirect)
    {
        $hash = md5(date('YmdHi'));
        return view('botProtection')
            ->with('hash', $hash)
            ->with('r', $redirect);
    }

    public function get($url)
    {
        $ctx = stream_context_create(array('http' => array('timeout' => 2)));
        return file_get_contents($url, false, $ctx);
    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function tips(Request $request)
    {
        $tipserver = '';
        if (App::environment() === "development") {
            $tipserver = "https://dev.quicktips.metager.de/1.1/tips.xml";
        } else {
            $tipserver = "https://quicktips.metager.de/1.1/tips.xml";
        }
        if (Localization::getLanguage() == "en") {
            $tipserver .= "?locale=en";
        }
        $tips_text = file_get_contents($tipserver);
        $tips = [];
        try {
            $tips_xml = \simplexml_load_string($tips_text);

            $tips_xml->registerXPathNamespace('mg', 'http://metager.de/tips/');
            $tips_xml = $tips_xml->xpath('mg:tip');
            foreach ($tips_xml as $tip_xml) {
                $tips[] = $tip_xml->__toString();
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred loading tips from the tip server.");
            Log::error($e->getMessage());
            abort(500);
        }
        return view('tips')
            ->with('title', trans('tips.title'))
            ->with('tips', $tips);
    }
}