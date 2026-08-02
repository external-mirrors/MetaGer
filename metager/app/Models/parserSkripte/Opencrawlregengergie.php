<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Opencrawlregengergie extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'opencrawlregengergie' => [
            'host' => 'www.opencrawl.de',
            'path' => '/opencrawl/opensearch.jsp',
            'port' => 80,
            'query-parameter' => 'query',
            'input-encoding' => 'ISO-8859-1',
            'output-encoding' => 'Latin1',
            'get-parameter' => [
                'subcollection' => 'ernenerg',
                'hitsPerPage' => '10',
                'hitsPerSite' => '2',
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
                'homepage' => 'http://www.opencrawl.de',
                'index_name' => null,
                'display_name' => 'OpenCrawl (Regenerative Energien)',
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

            $results = $content->xpath('//rss/channel/item');
            $count = 0;
            foreach ($results as $result) {
                if ($count > 10) {
                    break;
                }

                $title = $result->{"title"}->__toString();
                $link = $result->{"link"}->__toString();
                $anzeigeLink = $link;
                $descr = strip_tags($result->{"description"}->__toString());
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