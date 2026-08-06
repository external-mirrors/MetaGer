<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Beammachine extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'beammachine' => [
            'host' => 'www.beammachine.net',
            'path' => '/de/qsearch.php',
            'port' => 80,
            'query-parameter' => 'q',
            'input-encoding' => '',
            'output-encoding' => '',
            'get-parameter' => [
                'strict' => '1',
                'format' => 'csv',
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
                'display_name' => 'Beam Machine',
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
            $res = explode("|", $result);
            if (sizeof($res) < 3) {
                continue;
            }
            $title = $res[0];
            $link = $res[1];
            $anzeigeLink = $link;
            $descr = $res[2];

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