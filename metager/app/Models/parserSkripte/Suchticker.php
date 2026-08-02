<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Suchticker extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'suchticker' => [
            'host' => 'www.suchticker.de',
            'path' => '/',
            'port' => 80,
            'query-parameter' => 'qu',
            'input-encoding' => '',
            'output-encoding' => 'Latin1',
            'get-parameter' => [
                'mg' => '1',
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
                'homepage' => 'https://metager.org/search-engine',
                'index_name' => null,
                'display_name' => 'Suchticker',
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
        $results = trim($result);

        foreach (explode("\n", $results) as $result) {
            $res = explode("';'", $result);
            if (sizeof($res) < 3) {
                continue;
            }
            $title = trim($res[0], "'");
            $link = trim($res[1], "'");
            $anzeigeLink = $link;
            $descr = trim($res[2], "'");

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