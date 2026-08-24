<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Tootnews extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'tootnews' => [
            'host' => 'toot.suma-lab.de',
            'path' => '/search',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'en' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1,
            'cost' => 1,
            'get-parameter' => [
                'out' => 'api',
            ],
            'infos' => [
                'homepage' => 'https://toot.suma-lab.de',
                'index_name' => null,
                'display_name' => 'TootNews',
                'founded' => '2026',
                'headquarter' => 'Hannover',
                'operator' => 'SUMA-EV',
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
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            $results = $content->xpath("//feed[1]/entry");
            foreach ($results as $result) {
                $title = strip_tags($result->{"title"}->asXML());
                $link = $result->{"link"}['href'];
                $anzeigeLink = $link;
                $descr = strip_tags($result->{"content"}->asXML());
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
            Log::error("A problem occurred parsing results from $this->name:\n" . $e->getMessage() . "\n" . $result);
            return;
        }
    }


    public function getNext(\App\MetaGer $metager, $result)
    {
        return;
    }
}