<?php

namespace app\Models\parserSkripte;

use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Carbon;

class Onenewspage extends Searchengine
{
    public $results = [];
    public $resultCount = 0;

    private $offset = 0;
    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $results = trim($result);

        foreach (explode("\n", $results) as $result) {
            $res = explode("|", $result);
            if (sizeof($res) < 3) {
                continue;
            }
            $title                                = $res[0];
            $link                                 = $res[2];
            $anzeigeLink                          = $link;
            $descr                                = $res[1];
            $additionalInformation                = sizeof($res) > 3 ? ['date' => Carbon::createFromTimestamp(intval($res[3]))] : [];
            $faviconUrl                           = parse_url($link, PHP_URL_SCHEME) . "://" . parse_url($link, PHP_URL_HOST) . "/favicon.ico";
            $additionalInformation["favicon_url"] = $faviconUrl;
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

        uasort($this->results, function (Result $a, Result $b) {
            $diff = $a->getDate()->diffInSeconds($b->getDate(), false);
            return $diff;
        });

        foreach ($this->results as $index => $result) {
            $this->results[$index]->sourceRank = 20 - $index;
        }

        if (count($this->results) > $this->resultCount) {
            $this->resultCount += count($this->results);
        }
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        if (count($this->results) <= 0) {
            return;
        }

        /** @var SearchEngineConfiguration */
        $newConfiguration = unserialize(serialize($this->configuration));
        if (property_exists($newConfiguration->getParameter, "o")) {
            $newConfiguration->getParameter->o += count($this->results);
        } else {
            $newConfiguration->getParameter->o = count($this->results);
        }
        $next       = new Onenewspage($this->name, $newConfiguration);
        $this->next = $next;
    }
}