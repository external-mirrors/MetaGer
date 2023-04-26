<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;

class Wikipedia extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $result = utf8_decode($result);
        $counter = 0;

        $this->results[] = new \App\Models\Result(
            $this->configuration->engineBoost,
            trim(strip_tags($result[1])),
            $link,
            $result[3],
            $result[2],
            $this->configuration->infos->displayName,
            $this->configuration->infos->homepage,
            $counter
        );
    }
}