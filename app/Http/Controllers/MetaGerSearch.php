<?php

namespace App\Http\Controllers;

use App;
use App\MetaGer;
use Cache;
use Illuminate\Http\Request;
use LaravelLocalization;
use View;

class MetaGerSearch extends Controller
{

    public function search(Request $request, MetaGer $metager, $timing = false)
    {
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

        if ($spamEntry !== null && Cache::has('spam.' . $metager->getFokus() . "." . md5($spamEntry))) {
            $responseContent = Cache::get('spam.' . $metager->getFokus() . "." . md5($spamEntry));
            $responseContent = preg_replace('/(name="eingabe"\s+value=")[^"]+/', "$1$eingabe", $responseContent);
            return response($responseContent);
        }

        # Suche für alle zu verwendenden Suchmaschinen als Job erstellen,
        # auf Ergebnisse warten und die Ergebnisse laden
        $metager->createSearchEngines($request, $timings);

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
        $metager->prepareResults();
        if (!empty($timings)) {
            $timings["prepareResults"] = microtime(true) - $time;
        }

        $finished = true;
        foreach ($metager->getEngines() as $engine) {
            if ($engine->loaded) {
                $engine->setNew(false);
                $engine->markNew();
            }
        }

        Cache::put("loader_" . $metager->getSearchUid(), $metager->getEngines(), 60 * 60);
        if (!empty($timings)) {
            $timings["Filled resultloader Cache"] = microtime(true) - $time;
        }

        # Die Ausgabe erstellen:
        $resultpage = $metager->createView();
        if ($spamEntry !== null) {
            Cache::put('spam.' . $metager->getFokus() . "." . md5($spamEntry), $resultpage->render(), 604800);
        }

        if (!empty($timings)) {
            $timings["createView"] = microtime(true) - $time;
        }

        if ($timings) {
            dd($timings);
        }

        return $resultpage;
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

        $engines = Cache::get($hash);
        if ($engines === null) {
            return response()->json(['finished' => true]);
        }

        $metager = new MetaGer(substr($hash, strpos($hash, "loader_") + 7));

        $metager->parseFormData($request);
        # Nach Spezialsuchen überprüfen:
        $metager->checkSpecialSearches($request);
        $metager->restoreEngines($engines);

        $metager->retrieveResults();
        $metager->rankAll();
        $metager->prepareResults();

        $result = [
            'finished' => true,
            'newResults' => [],
        ];
        $result["nextSearchLink"] = $metager->nextSearchLink();

        foreach ($metager->getResults() as $index => $resultTmp) {
            if ($resultTmp->new) {
                if ($metager->getFokus() !== "bilder") {
                    $view = View::make('layouts.result', ['index' => $index, 'result' => $resultTmp, 'metager' => $metager]);
                    $html = $view->render();
                    $result['newResults'][$index] = $html;
                    $result["imagesearch"] = false;
                } else {
                    $view = View::make('layouts.image_result', ['index' => $index, 'result' => $resultTmp, 'metager' => $metager]);
                    $html = $view->render();
                    $result['newResults'][$index] = $html;
                    $result["imagesearch"] = true;
                }
            }
        }

        $finished = true;
        foreach ($engines as $engine) {
            if (!$engine->loaded) {
                $finished = false;
            } else {
                $engine->setNew(false);
                $engine->markNew();
            }
        }

        $result["finished"] = $finished;

        // Update new Engines
        Cache::put("loader_" . $metager->getSearchUid(), $metager->getEngines(), 1 * 60);
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
        if(empty($search)){
            abort(404);
        }

        $quicktips = new \App\Models\Quicktips\Quicktips($search);
        return view('quicktips')
            ->with('quicktips', $quicktips->getResults())
            ->with('search', $search);
    }
}
