<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Loklak extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'loklak' => [
            'host' => 'api.loklak.org',
            'path' => '/api/search.json',
            'port' => 80,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => '',
            'get-parameter' => [
                'timezoneOffset' => '-120',
                'source' => 'cache',
                'count' => '10',
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
                'homepage' => 'http://loklak.org/',
                'index_name' => null,
                'display_name' => 'loklak',
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