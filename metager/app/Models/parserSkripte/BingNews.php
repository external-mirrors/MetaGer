<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Carbon;
use Log;

class BingNews extends Searchengine
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
            if (!empty($results->totalEstimatedMatches)) {
                $this->totalResults = $results->totalEstimatedMatches;
            }

            # Check if the query got altered
            if (!empty($results->{"queryContext"}) && !empty($results->{"queryContext"}->{"alteredQuery"}) && !empty($results->{"queryContext"}->{"alterationOverrideQuery"})) {
                $this->alteredQuery            = $results->{"queryContext"}->{"alteredQuery"};
                $this->alterationOverrideQuery = $results->{"queryContext"}->{"alterationOverrideQuery"};
            }

            $results = $results->value;

            foreach ($results as $result) {
                $title       = $result->name;
                $link        = $result->url;
                $anzeigeLink = $link;
                $descr       = $result->description;

                $additionalInformation = [];
                if (property_exists($result, "datePublished")) {
                    $additionalInformation["date"] = new Carbon($result->datePublished);
                }

                if (property_exists($result, "provider") && sizeof($result->provider) > 0 && property_exists($result->provider[0], "image") && property_exists($result->provider[0]->image, "thumbnail")) {
                    $faviconUrl = $result->provider[0]->image->thumbnail->contentUrl;
                } else {
                    $faviconUrl = parse_url($link, PHP_URL_SCHEME) . "://" . parse_url($link, PHP_URL_HOST) . "/favicon.ico";
                }
                $additionalInformation["favicon_url"] = $faviconUrl;

                if (property_exists($result, "image") && property_exists($result->image, "thumbnail")) {
                    $additionalInformation["image"] = $result->image->thumbnail->contentUrl;
                }

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
                    $additionalInformation
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

            if (empty($results->totalEstimatedMatches)) {
                return;
            }
            $totalMatches = $results->totalEstimatedMatches;

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

            $next       = new BingNews($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}