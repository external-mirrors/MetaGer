<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Openclipart extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'openclipart' => [
            'host' => 'openclipart.org',
            'path' => '/search/json/',
            'port' => 443,
            'query-parameter' => 'query',
            'input-encoding' => 'utf8',
            'output-encoding' => '',
            'get-parameter' => [
                'amount' => '20',
            ],
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'de' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 0.4,
            'cache-duration' => -1,
            'disabled' => true,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'https://openclipart.org/',
                'index_name' => null,
                'display_name' => 'Openclipart',
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

            $results = $content->payload;
            foreach ($results as $result) {
                $title = $result->title;
                $link = $result->detail_link;
                $anzeigeLink = $link;
                $descr = $result->description;
                $image = $result->svg->png_thumb;
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
                    ['image' => $image]
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
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            if ($content->info->current_page > $content->info->pages) {
                return;
            }
            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->page = $metager->getPage() + 1;
            $next = new Openclipart($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}