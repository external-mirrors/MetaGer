<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Europeana extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            $results = $content->items;
            foreach ($results as $result) {
                if (isset($result->edmPreview)) {
                    $title = $result->title[0];
                    if (preg_match("/(.+)\?.*/si", $result->guid, $match)) {
                        $link = $match[1];
                    } else {
                        $link = "";
                    }
                    $anzeigeLink = $link;
                    $descr = "";
                    $image = urldecode($result->edmPreview[0]);
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
                        ['image' => $image]
                    );
                }
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
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            $start = ($metager->getPage()) * 10 + 1;
            if ($start > $content->totalResults) {
                return;
            }
            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->start = $start;
            $next = new Europeana($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}