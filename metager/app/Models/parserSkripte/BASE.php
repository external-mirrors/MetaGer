<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use \Carbon\Carbon;
use Log;

class BASE extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $results = json_decode($result);
        if ($results === null) {
            // Invalid JSON returned
            return;
        }

        try {
            $results = $results->response;

            if (property_exists($results, "numFound")) {
                $this->totalResults = $results->numFound;
            }

            foreach ($results->docs as $result) {
                if (!property_exists($result, "dctitle") || !property_exists($result, "dclink")) {
                    continue;
                }
                $title = $result->dctitle;
                $link = $result->dclink;
                $anzeigeLink = $link;
                $description = "";
                if (property_exists($result, "dctype")) {
                    $description .= "Type: " . implode(",", $result->dctype);
                }
                if (property_exists($result, "dcprovider")) {
                    $description .= " From: " . $result->dcprovider;
                    if (property_exists($result, "dccreator")) {
                        $description .= " (" . implode($result->dccreator) . ")";
                    }
                }
                if (property_exists($result, "dclang") && $result->dclang[0] !== "unknown") {
                    $description .= " Lang: " . implode(",", $result->dclang);
                }
                if (property_exists($result, "dchdate")) {
                    $date = Carbon::createFromFormat("Y-m-d\TH:i:s\Z", $result->dchdate);
                    $description .= " Date: " . $date->format("d.m.Y H:i:s");
                }
                if (property_exists($result, "dcdescription")) {
                    $description .= " " . $result->dcdescription;
                }

                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $description,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:\n" . $e->getMessage() . "\n" . $result);
            return;
        }
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        $results = json_decode($result);
        if ($results === null) {
            // Invalid JSON returned
            return;
        }

        try {
            $results = $results->response;

            $totalMatches = $results->numFound;

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));

            $perPage = $newConfiguration->getParameter->hits;

            $offset = $results->start + $perPage;

            if ($totalMatches < ($offset + $perPage)) {
                return;
            } else {
                $newConfiguration->getParameter->offset = $offset;
            }

            $next = new BASE($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}