<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Zeitde extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'zeitde' => [
            'host' => 'api.zeit.de',
            'path' => '/content',
            'port' => 80,
            'query-parameter' => 'q',
            'input-encoding' => '',
            'output-encoding' => '',
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
                'homepage' => 'http://www.zeit.de/index',
                'index_name' => null,
                'display_name' => 'Die ZEIT',
                'founded' => '1997',
                'headquarter' => 'Mäntsälä, Finnland (Europa)',
                'operator' => 'Yandex OY (Aktiengesellschaft)',
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

        $results = json_decode($result);
        if (!$results) {
            return;
        }

        foreach ($results->{"matches"} as $result) {
            if (!isset($result->{"title"}) || !isset($result->{"href"}) || !isset($result->{"snippet"})) {
                continue;
            }

            $title = $result->{"title"};
            $link = $result->{"href"};
            $anzeigeLink = $link;
            $descr = $result->{"snippet"};

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