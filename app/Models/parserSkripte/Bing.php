<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;
use LaravelLocalization;

class Bing extends Searchengine
{
    public $results = [];

    public function __construct($name, \stdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);

        if (LaravelLocalization::getCurrentLocale() === 'en') {
            $langFile = $metager->getLanguageFile();
            $langFile = json_decode(file_get_contents($langFile));
            $acceptLanguage = $metager->request->headers->all();
            if (!empty($acceptLanguage["accept-language"]) && is_array($acceptLanguage["accept-language"]) && sizeof($acceptLanguage["accept-language"]) > 0) {
                $acceptLanguage = $acceptLanguage['accept-language'][0];
                foreach ($langFile->filter->{'parameter-filter'}->language->sumas->bing->values as $key => $value) {
                    if (stripos($acceptLanguage, "en") === 0 && stripos($acceptLanguage, $value) === 0) {
                        $this->engine->{"get-parameter"}->mkt =  $value;
                    }
                }
            }
        }
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);
            if (!empty($results->webPages->totalEstimatedMatches)) {
                $this->totalResults = $results->webPages->totalEstimatedMatches;
            }

            # Check if the query got altered
            if (!empty($results->{"queryContext"}) && !empty($results->{"queryContext"}->{"alteredQuery"}) && !empty($results->{"queryContext"}->{"alterationOverrideQuery"})) {
                $this->alteredQuery = $results->{"queryContext"}->{"alteredQuery"};
                $this->alterationOverrideQuery = $results->{"queryContext"}->{"alterationOverrideQuery"};
            }

            $results = $results->webPages->value;

            foreach ($results as $result) {
                $title = $result->name;
                $link = $result->url;
                $anzeigeLink = $result->displayUrl;
                $descr = $result->snippet;
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->engine,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->engine->infos->display_name,
                    $this->engine->infos->homepage,
                    $this->counter,
                    []
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        try {
            $results = json_decode($result);

            if (empty($results->webPages->totalEstimatedMatches)) {
                return;
            }
            $totalMatches = $results->webPages->totalEstimatedMatches;

            $newEngine = unserialize(serialize($this->engine));

            $perPage = $newEngine->{"get-parameter"}->count;

            $offset = 0;
            if (empty($newEngine->{"get-parameter"}->offset)) {
                $offset = $perPage;
            } else {
                $offset = $newEngine->{"get-parameter"}->offset + $perPage;
            }

            if ($totalMatches < ($offset + $perPage)) {
                return;
            } else {
                $newEngine->{"get-parameter"}->offset = $offset;
            }

            $next = new Bing($this->name, $newEngine, $metager);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}
