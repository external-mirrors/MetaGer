<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Bing extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
        $this->configuration->disabledByDefault = true;
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
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
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

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));

            $perPage = $newConfiguration->getParameter->count;

            $offset = 0;
            if (empty($newConfiguration->getParameter->offset)) {
                $offset = $perPage;
            } else {
                $offset = $newConfiguration->getParameter->offset + $perPage;
            }

            if ($totalMatches < ($offset + $perPage)) {
                return;
            } else {
                $newConfiguration->getParameter->offset = $offset;
            }

            $next = new Bing($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}