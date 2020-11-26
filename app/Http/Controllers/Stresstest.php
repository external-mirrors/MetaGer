<?php

namespace App\Http\Controllers;

use App;
use App\MetaGer;
use Cache;
use Illuminate\Http\Request;
use LaravelLocalization;
use Log;
use View;

class Stresstest extends Controller
{
    public function index(Metager $metager){
        $this->dummySearch($metager);
    }

    private function dummySearch(Metager $metager){

        $timings = null;
        $timings = ['starttime' => microtime(true)];
        $time = microtime(true);
        $spamEntries = [];
        $spamEntry = null;
        if (file_exists(config_path('spam.txt'))) {
            $spamEntries = file(config_path('spam.txt'));
        }

        # If there is no query parameter we redirect to the startpage
        $eingabe = "test";

        # Mit gelieferte Formulardaten parsen und abspeichern:
        $metager->insertDummyFormData();
        if (!empty($timings)) {
            $timings["insertDummyFormData"] = microtime(true) - $time;
        }

        # Suche für alle zu verwendenden Suchmaschinen als Job erstellen,
        # auf Ergebnisse warten und die Ergebnisse laden
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
        //$resultpage = $metager->createView([]);

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
}