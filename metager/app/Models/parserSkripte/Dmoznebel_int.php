<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Dmoznebel_int extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {

        $title = "";
        $link = "";
        $anzeigeLink = $link;
        $descr = "";

        $this->counter++;
        $this->results[] = new \App\Models\Result(
            $this->engine,
            $title,
            $link,
            $anzeigeLink,
            $descr,
            $this->engine->infos->display_name,
            $this->engine->infos->homepage,
            $this->counter
        );
    }
}