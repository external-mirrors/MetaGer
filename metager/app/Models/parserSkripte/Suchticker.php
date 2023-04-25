<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Suchticker extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $results = trim($result);

        foreach (explode("\n", $results) as $result) {
            $res = explode("';'", $result);
            if (sizeof($res) < 3) {
                continue;
            }
            $title = trim($res[0], "'");
            $link = trim($res[1], "'");
            $anzeigeLink = $link;
            $descr = trim($res[2], "'");

            $this->counter++;
            $this->results[] = new \App\Models\Result(
                $this->configuration->engineBoost,
                $title,
                $link,
                $anzeigeLink,
                $descr,
                $this->configuration->infos->displayName,
                $this->configuration->infos->homepage,
                $this->counter
            );
        }
    }
}