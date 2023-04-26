<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
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

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));

            $perPage = 0;
            if (isset($newConfiguration->getParameter->count)) {
                $perPage = $newConfiguration->getParameter->count;
            } else {
                $perPage = 10;
            }

            $offset = 0;
            if (empty($newConfiguration->getParameter->skip)) {
                $offset = $perPage;
            } else {
                $offset = $newConfiguration->getParameter->skip + $perPage;
            }

            if (PHP_INT_MAX - $perPage < ($offset + $perPage)) {
                return;
            } else {
                $newConfiguration->getParameter->skip = strval($offset);
            }

            $next = new Dummy($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}