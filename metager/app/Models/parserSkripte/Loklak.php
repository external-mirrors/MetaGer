<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Loklak extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        if (!$result) {
            return;
        }
        $results = json_decode($result, true);
        if (!isset($results['statuses'])) {
            return;
        }

        foreach ($results['statuses'] as $result) {
            $title = $result["screen_name"];
            $link = $result['link'];
            $anzeigeLink = $link;
            $descr = $result["text"];
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