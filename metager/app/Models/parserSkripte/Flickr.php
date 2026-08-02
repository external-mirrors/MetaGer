<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Flickr extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'flickr' => [
            'host' => 'api.flickr.com',
            'path' => '/services/rest/',
            'port' => 443,
            'query-parameter' => 'text',
            'input-encoding' => 'utf8',
            'output-encoding' => '',
            'get-parameter' => [
                'method' => 'flickr.photos.search',
                'license' => '1,2,3,4,5,6,7',
                'sort' => 'relevance',
                'per_page' => '20',
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
                'homepage' => 'https://www.flickr.com',
                'index_name' => null,
                'display_name' => 'flickr',
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

            $results = $content->xpath('//photos/photo');
            foreach ($results as $result) {
                $title = $result["title"]->__toString();
                $link = "https://www.flickr.com/photos/" . $result["owner"]->__toString() . "/" . $result["id"]->__toString();
                $anzeigeLink = $link;
                $descr = "";
                $image = "http://farm" . $result["farm"]->__toString() . ".staticflickr.com/" . $result["server"]->__toString() . "/" . $result["id"]->__toString() . "_" . $result["secret"]->__toString() . "_t.jpg";
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
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            $page = $metager->getPage() + 1;
            $results = $content->xpath('//photos')[0];
            if ($page >= intval($results["pages"]->__toString())) {
                return;
            }
            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->page = $page;
            $next = new Flickr($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}