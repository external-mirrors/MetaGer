<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Dailymotion extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'dailymotion' => [
            'host' => 'api.dailymotion.com',
            'path' => '/videos',
            'port' => 443,
            'query-parameter' => 'search',
            'input-encoding' => 'utf8',
            'output-encoding' => '',
            'get-parameter' => [
                'fields' => 'title,thumbnail_240_url,url,description',
            ],
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'de' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1,
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'http://www.dailymotion.com',
                'index_name' => null,
                'display_name' => 'Dailymotion',
                'founded' => null,
                'headquarter' => null,
                'operator' => null,
                'index_size' => null,
            ],
        ],
    ];
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            $results = $content->list;
            foreach ($results as $result) {
                $title = $result->title;
                $link = $result->url;
                $anzeigeLink = $link;
                $descr = $result->description;
                $image = $result->thumbnail_240_url;
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
                    ['partnershop' => false]
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}