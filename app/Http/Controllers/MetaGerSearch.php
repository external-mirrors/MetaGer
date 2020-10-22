<?php

namespace App\Http\Controllers;

use App;
use App\MetaGer;
use Cache;
use Illuminate\Http\Request;
use LaravelLocalization;
use Log;
use View;

class MetaGerSearch extends Controller
{
    public function search(Request $request, MetaGer $metager, $timing = false)
    {
        if ($request->filled("chrome-plugin")) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin"));
        }
        $timings = null;
        if ($timing) {
            $timings = ['starttime' => microtime(true)];
        }
        $time = microtime(true);
        $spamEntries = [];
        $spamEntry = null;
        if (file_exists(config_path('spam.txt'))) {
            $spamEntries = file(config_path('spam.txt'));
        }

        $focus = $request->input("focus", "web");

        if ($focus === "maps") {
            $searchinput = $request->input('eingabe', '');
            return redirect()->to('https://maps.metager.de/map/' . $searchinput . '/1240908.5493525574,6638783.2192695495,6');
        }

        # If there is no query parameter we redirect to the startpage
        $eingabe = $request->input('eingabe', '');
        if (empty(trim($eingabe))) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
        }

        foreach ($spamEntries as $index => $entry) {
            $entry = trim($entry);
            if (empty($entry)) {
                continue;
            }
            if (preg_match("/" . $entry . "/si", $eingabe)) {
                $spamEntry = $entry;
                break;
            }
        }

        # Mit gelieferte Formulardaten parsen und abspeichern:
        $metager->parseFormData($request);
        if (!empty($timings)) {
            $timings["parseFormData"] = microtime(true) - $time;
        }

        # Nach Spezialsuchen überprüfen:
        $metager->checkSpecialSearches($request);
        if (!empty($timings)) {
            $timings["checkSpecialSearches"] = microtime(true) - $time;
        }

        # Search query can be empty after parsing the formdata
        # we will cancel the search in that case and show an error to the user
        if (empty($metager->getQ())) {
            return $metager->createView();
        }

        if ($spamEntry !== null && Cache::has('spam.' . $metager->getFokus() . "." . md5($spamEntry))) {
            $responseContent = Cache::get('spam.' . $metager->getFokus() . "." . md5($spamEntry));
            $responseContent = preg_replace('/(name="eingabe"\s+value=")[^"]+/', "$1$eingabe", $responseContent);
            return response($responseContent);
        }

        $quicktips = $metager->createQuicktips();
        if (!empty($timings)) {
            $timings["createQuicktips"] = microtime(true) - $time;
        }

        # Suche für alle zu verwendenden Suchmaschinen als Job erstellen,
        # auf Ergebnisse warten und die Ergebnisse laden
        $metager->createSearchEngines($request, $timings);

        # Versuchen die Ergebnisse der Quicktips zu laden
        $quicktipResults = $quicktips->loadResults();
        if (!empty($timings)) {
            $timings["loadResults"] = microtime(true) - $time;
        }

        $metager->startSearch($timings);

        $metager->waitForMainResults();
        if (!empty($timings)) {
            $timings["waitForMainResults"] = microtime(true) - $time;
        }

        $metager->retrieveResults();
        if (!empty($timings)) {
            $timings["retrieveResults"] = microtime(true) - $time;
        }

        # Alle Ergebnisse vor der Zusammenführung ranken:
        $metager->rankAll();
        if (!empty($timings)) {
            $timings["rankAll"] = microtime(true) - $time;
        }

        # Ergebnisse der Suchmaschinen kombinieren:
        $metager->prepareResults($timings);

        $finished = true;
        foreach ($metager->getEngines() as $engine) {
            if ($engine->loaded) {
                $engine->setNew(false);
                $engine->markNew();
            }
        }

        try {
            Cache::put("loader_" . $metager->getSearchUid(), [
                "metager" => [
                    "apiAuthorized" => $metager->isApiAuthorized(),
                ],
                "adgoal" => [
                    "loaded" => $metager->isAdgoalLoaded(),
                    "adgoalHash" => $metager->getAdgoalHash(),
                ],
                "engines" => $metager->getEngines(),
            ], 60 * 60);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        if (!empty($timings)) {
            $timings["Filled resultloader Cache"] = microtime(true) - $time;
        }

        # Die Ausgabe erstellen:
        $resultpage = $metager->createView($quicktipResults);
        if ($spamEntry !== null) {
            try {
                Cache::put('spam.' . $metager->getFokus() . "." . md5($spamEntry), $resultpage->render(), 604800);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        if (!empty($timings)) {
            $timings["createView"] = microtime(true) - $time;
        }

        if ($timings) {
            dd($timings);
        }

        $registry = \Prometheus\CollectorRegistry::getDefault();
        $counter = $registry->getOrRegisterCounter('metager', 'result_counter', 'counts total number of returned results', []);
        $counter->incBy(sizeof($metager->getResults()));
        $counter = $registry->getOrRegisterCounter('metager', 'query_counter', 'counts total number of search queries', []);
        $counter->inc();

        // Splitting the response return into multiple parts.
        // This might speed up page view time for users with slow network
        $responseArray = str_split($resultpage->render(), 1024);
        foreach ($responseArray as $responsePart) {
            echo($responsePart);
            flush();
        }
        $requestTime = microtime(true) - $time;
        \App\PrometheusExporter::Duration($requestTime, "request_time");
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
        $request->request->add(["javascript" => true]);
        # Create a MetaGer Instance with the supplied hash
        $hash = $request->input('loadMore', '');

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
            return response()->json(['finished' => true]);
        }

        $engines = $cached["engines"];
        $adgoal = $cached["adgoal"];
        $mg = $cached["metager"];

        $metager = new MetaGer(substr($hash, strpos($hash, "loader_") + 7));
        $metager->setApiAuthorized($mg["apiAuthorized"]);
        $metager->setAdgoalLoaded($adgoal["loaded"]);
        $metager->setAdgoalHash($adgoal["adgoalHash"]);

        $metager->parseFormData($request);
        # Nach Spezialsuchen überprüfen:
        $metager->checkSpecialSearches($request);
        $metager->restoreEngines($engines);

        # Checks Cache for engine Results
        $metager->checkCache();

        $metager->retrieveResults();

        $metager->rankAll();
        $metager->prepareResults();

        $result = [
            'finished' => true,
            'newResults' => [],
            'changedResults' => [],
        ];
        $result["nextSearchLink"] = $metager->nextSearchLink();

        $newResults = 0;
        foreach ($metager->getResults() as $index => $resultTmp) {
            if ($resultTmp->new || $resultTmp->adgoalChanged) {
                if ($metager->getFokus() !== "bilder") {
                    $view = View::make('layouts.result', ['index' => $index, 'result' => $resultTmp, 'metager' => $metager]);
                    $html = $view->render();
                    if (!$resultTmp->new && $resultTmp->adgoalChanged) {
                        $result['changedResults'][$index] = $html;
                    } else {
                        $result['newResults'][$index] = $html;
                    }
                    $result["imagesearch"] = false;
                } else {
                    $view = View::make('layouts.image_result', ['index' => $index, 'result' => $resultTmp, 'metager' => $metager]);
                    $html = $view->render();
                    if (!$resultTmp->new && $resultTmp->adgoalChanged) {
                        $result['changedResults'][$index] = $html;
                    } else {
                        $result['newResults'][$index] = $html;
                    }
                    $result["imagesearch"] = true;
                }
                $newResults++;
            }
        }

        $finished = true;
        $enginesLoaded = [];
        foreach ($engines as $engine) {
            if (!$engine->loaded) {
                $enginesLoaded[$engine->name] = false;
                $finished = false;
            } else {
                $enginesLoaded[$engine->name] = true;
                $engine->setNew(false);
                $engine->markNew();
            }
        }

        if (!$metager->isAdgoalLoaded()) {
            $finished = false;
        }

        $result["finished"] = $finished;
        $result["engines"] = $enginesLoaded;

        if ($newResults > 0) {
            $registry = \Prometheus\CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('metager', 'result_counter', 'counts total number of returned results', []);
            $counter->incBy($newResults);
        }
        // Update new Engines
        Cache::put("loader_" . $metager->getSearchUid(), [
            "metager" => [
                "apiAuthorized" => $metager->isApiAuthorized(),
            ],
            "adgoal" => [
                "loaded" => $metager->isAdgoalLoaded(),
                "adgoalHash" => $metager->getAdgoalHash(),
            ],
            "engines" => $metager->getEngines(),
        ], 1 * 60);

        # JSON encoding will fail if invalid UTF-8 Characters are in this string
        # mb_convert_encoding will remove thise invalid characters for us
        $result = mb_convert_encoding($result, "UTF-8", "UTF-8");
        return response()->json($result);
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
        if (env('APP_ENV') === "development") {
            $tipserver = "https://dev.quicktips.metager.de/1.1/tips.xml";
        } else {
            $tipserver = "https://quicktips.metager.de/1.1/tips.xml";
        }
        if (LaravelLocalization::getCurrentLocale() == "en") {
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

    public function quicktips(Request $request)
    {
        $search = $request->input('search', '');
        $quotes = $request->input('quotes', 'on');
        if (empty($search)) {
            abort(404);
        }

        $quicktips = new \App\Models\Quicktips\Quicktips($search, $quotes);
        return view('quicktips')
            ->with('quicktips', $quicktips->getResults())
            ->with('search', $search);
    }
}
