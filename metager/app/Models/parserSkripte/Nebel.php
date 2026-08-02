<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Nebel extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'nebel' => [
            'host' => 'www.netluchs.de',
            'path' => '/netluchsJsf/meta',
            'port' => 80,
            'query-parameter' => 'query',
            'input-encoding' => 'ISO-8859-1',
            'output-encoding' => 'Latin1',
            'get-parameter' => [
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
            'disabled' => true,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'http://www.netluchs.de/',
                'index_name' => null,
                'display_name' => 'Netluchs',
                'founded' => '2005',
                'headquarter' => 'Hamburg, Deutschland',
                'operator' => 'Michael Nebel (Privatperson)',
                'index_size' => '6 Millionen (Stand: 2005)',
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

            $title = $res[1];
            $link = $res[0];
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