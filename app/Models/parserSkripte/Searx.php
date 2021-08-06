<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;

class Searx extends Searchengine
{
    public $results = [];

    public function __construct($name, \StdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);
    }

    public function loadResults($result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            $results = $content->results;
            foreach ($results as $result) {
                try {
                    $title       = $result->title;
                    $link        = $result->url;
                    $anzeigeLink = $result->pretty_url;
                    $descr       = $result->content;
                    $engine       = $result->engine;
                    $this->counter++;
                    $this->results[] = new \App\Models\Result(
                        $this->engine,
                        $title,
                        $link,
                        $anzeigeLink,
                        $descr,
                        $this->engine->{"display-name"} . " (" . $engine . ")",
                        $this->engine->homepage,
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
