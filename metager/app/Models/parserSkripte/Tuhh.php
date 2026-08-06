<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Tuhh extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'tuhh' => [
            'host' => 'tubdok.tub.tuhh.de',
            'path' => '/open-search/simple-search',
            'port' => 443,
            'query-parameter' => 'query',
            'input-encoding' => '',
            'output-encoding' => 'utf8',
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
                'homepage' => 'https://tubdok.tub.tuhh.de/',
                'index_name' => null,
                'display_name' => 'TUBdok',
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
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            $count = 0;
            foreach ($content->{"entry"} as $result) {
                if ($count > 10) {
                    break;
                }

                $title = $result->{"title"}->__toString();
                $link = $result->{"link"}["href"]->__toString();
                $anzeigeLink = $link;
                $descr = strip_tags($result->{"summary"}->__toString());
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
                $count++;
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}