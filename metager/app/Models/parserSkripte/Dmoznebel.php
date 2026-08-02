<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Dmoznebel extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'dmoznebel_int' => [
            'host' => 'minisucher.suma-lab.de',
            'path' => '/dmoz/dmozintmeta.jsp',
            'port' => 80,
            'query-parameter' => 'query',
            'input-encoding' => 'ISO-8859-1',
            'output-encoding' => '',
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
                'homepage' => 'http://www.dmoz.de/',
                'index_name' => null,
                'display_name' => 'Dmoz-International',
                'founded' => null,
                'headquarter' => null,
                'operator' => null,
                'index_size' => null,
            ],
        ],
        'dmoznebel' => [
            'host' => 'minisucher.suma-lab.de',
            'path' => '/dmoz/dmozmeta.jsp',
            'port' => 80,
            'query-parameter' => 'query',
            'input-encoding' => 'ISO-8859-1',
            'output-encoding' => '',
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
                'homepage' => 'http://www.dmoz.de/',
                'index_name' => null,
                'display_name' => 'Dmoz',
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

        $result = mb_convert_encoding($result, "UTF-8", "ISO-8859-1");
        $results = trim($result);

        foreach (explode("\n", $results) as $result) {
            $res = explode("|", $result);
            if (sizeof($res) < 3) {
                continue;
            }
            $title = $res[1];
            $link = $res[2];
            $anzeigeLink = $link;
            $descr = $res[3];

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