<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;
use LaravelLocalization;

class Dummy extends Searchengine
{
    public $results = [];

    public function __construct($name, \stdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);
    }

    public function loadResults($result)
    {
        return;
    }
}