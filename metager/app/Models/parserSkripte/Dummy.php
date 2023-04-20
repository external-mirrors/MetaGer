<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;
use LaravelLocalization;

class Dummy extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        try {
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            foreach ($content as $result) {
                $title = $result->title;
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
                    $this->engine->infos->display_name,
                    $this->engine->infos->homepage,
                    $this->counter
                );
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
            $results = json_decode($result);

            $newEngine = unserialize(serialize($this->engine));

            $perPage = 0;
            if (isset($newEngine->{"get-parameter"}->count)) {
                $perPage = $newEngine->{"get-parameter"}->count;
            } else {
                $perPage = 10;
            }

            $offset = 0;
            if (empty($newEngine->{"get-parameter"}->skip)) {
                $offset = $perPage;
            } else {
                $offset = $newEngine->{"get-parameter"}->skip + $perPage;
            }

            if (PHP_INT_MAX - $perPage < ($offset + $perPage)) {
                return;
            } else {
                $newEngine->{"get-parameter"}->skip = strval($offset);
            }

            $next = new Dummy($this->name, $newEngine, $metager);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}