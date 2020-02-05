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
    public function search(Request $request, MetaGer $metager)
    {
        $time = microtime(true);
        $spamEntries = [];
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

        # Mit gelieferte Formulardaten parsen und abspeichern:
        $metager->parseFormData($request);

        # Nach Spezialsuchen überprüfen:
        $metager->checkSpecialSearches($request);

        if (Cache::has('spam.' . $metager->getFokus() . "." . md5($metager->getQ()))) {
            return response(Cache::get('spam.' . $metager->getFokus() . "." . md5($metager->getEingabe())));
        }

        # Die Quicktips als Job erstellen
        $quicktips = $metager->createQuicktips();

        # Suche für alle zu verwendenden Suchmaschinen als Job erstellen,
        # auf Ergebnisse warten und die Ergebnisse laden
        $metager->createSearchEngines($request);

        $metager->startSearch();

        $metager->waitForMainResults();

        $metager->retrieveResults();

        # Versuchen die Ergebnisse der Quicktips zu laden
        $quicktipResults = $quicktips->loadResults();
        # Alle Ergebnisse vor der Zusammenführung ranken:
        $metager->rankAll();

        # Ergebnisse der Suchmaschinen kombinieren:
        $metager->prepareResults();

        $finished = true;
        foreach ($metager->getEngines() as $engine) {
            if ($engine->loaded) {
                $engine->setNew(false);
                $engine->markNew();
            }
        }

        \App\CacheHelper::put("loader_" . $metager->getSearchUid(), $metager->getEngines(), 60 * 60);

        # Die Ausgabe erstellen:
        $resultpage = $metager->createView($quicktipResults);
        foreach ($spamEntries as $index => $entry) {
            $entry = trim($entry);
            if (empty($entry)) {
                continue;
            }
            if (preg_match("/" . $entry . "/si", $metager->getEingabe())) {
                Cache::put('spam.' . $metager->getFokus() . "." . md5($metager->getEingabe()), $resultpage->render(), 604800);
            }
        }
        return $resultpage;
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
        \App\CacheHelper::put("loader_" . $metager->getSearchUid(), $metager->getEngines(), 1 * 60);
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
            $tips_xml = simplexml_load_string($tips_text);

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
