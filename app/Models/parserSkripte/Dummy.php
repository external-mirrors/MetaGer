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
        try {
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            foreach ($content as $result) {
                try {
                    $title = $result->titel;
                    $link = $result->link;
                    $anzeigeLink = $link;
                    $descr = $result->descr;
                    $this->counter++;
                    $this->results[] = new \App\Models\Result(
                        $this->engine,
                        $title,
                        $link,
                        $anzeigeLink,
                        $descr,
                        $this->engine->{"display-name"},$this->engine->homepage,
                        $this->counter
                    );
                } catch (\ErrorException $e) {

                }
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}