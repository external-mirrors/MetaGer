<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

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
            $title = $res[0];
            $link = $res[2];
            $anzeigeLink = $link;
            $descr = $res[1];
            $additionalInformation = sizeof($res) > 3 ? ['date' => intval($res[3])] : [];

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
        $next = new Onenewspage($this->name, $newConfiguration);
        $this->next = $next;
    }
}