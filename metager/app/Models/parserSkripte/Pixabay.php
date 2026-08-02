<?php

namespace app\Models\parserSkripte;

use App\Http\Controllers\Pictureproxy;
use App\Models\DeepResults\Imagesearchdata;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Pixabay extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'pixabay' => [
            'host' => 'pixabay.com',
            'path' => '/api',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'per_page' => 50,
            ],
            'lang' => [
                'parameter' => 'lang',
                'languages' => [
                    'de' => 'de',
                    'en' => 'en',
                    'es' => 'es',
                    'it' => 'it',
                    'fi' => 'fi',
                    'sv' => 'sv',
                    'no' => 'no',
                    'nl' => 'nl',
                    'da' => 'da',
                    'fr' => 'fr',
                    'pl' => 'pl',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1.0,
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'https://pixabay.com/',
                'index_name' => 'Pixabay',
                'display_name' => 'Pixabay',
                'founded' => '24. November 2010',
                'headquarter' => 'Berlin, Germany',
                'operator' => 'Pixabay GmbH',
                'index_size' => '3,1 Million',
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
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            if (property_exists($content, "total")) {
                $this->totalResults = $content->total;
            }

            $results = $content->hits;
            foreach ($results as $result) {
                $title       = $result->tags;
                $link        = $result->pageURL;
                $anzeigeLink = $link;
                $descr       = "";
                $image       = Pictureproxy::generateUrl($result->previewURL);
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
                    [
                        'image' => new Imagesearchdata($result->previewURL, $result->previewWidth, $result->previewHeight, $result->largeImageURL, $result->imageWidth, $result->imageHeight),
                    ]
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
            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));

            $newConfiguration->getParameter->page = $metager->getPage() + 1;

            if ($newConfiguration->getParameter->page * $newConfiguration->getParameter->per_page > $this->totalResults) {
                return;
            }

            $this->next = new Pixabay($this->name, $newConfiguration);
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}