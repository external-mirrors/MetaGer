<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Ecoshopper extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'ecoshopper' => [
            'host' => 'www.ecoshopper.de',
            'path' => '/ecoshopper/select/',
            'port' => 80,
            'query-parameter' => 'q',
            'input-encoding' => '',
            'output-encoding' => '',
            'get-parameter' => [
                'rows' => '20',
                'fl' => 'artikelName,artikelDeeplink,shopName,artikelBeschreibung,content,artikelPreis,basePriceCurrency,artikelLieferkosten,artikelImageurl',
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
                'homepage' => 'http://www.ecoshopper.de/',
                'index_name' => null,
                'display_name' => 'Ecoshopper',
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

            $results = $content->xpath('//response/result[@name="response"]/doc');
            foreach ($results as $result) {
                $result = \simplexml_load_string($result->saveXML());
                $title = $result->xpath('//doc/str[@name="artikelName"]')[0]->__toString();
                $link = $result->xpath('//doc/str[@name="artikelDeeplink"]')[0]->__toString();
                $anzeigeLink = parse_url($link);
                if (isset($anzeigeLink['query'])) {
                    parse_str($anzeigeLink['query'], $query);
                    if (isset($query['diurl'])) {
                        $anzeigeLink = $query['diurl'];
                    } else {
                        $anzeigeLink = $link;
                    }
                } else {
                    $anzeigeLink = $link;
                }
                $descr = $result->xpath('//doc/str[@name="artikelBeschreibung"]')[0]->__toString();
                $image = $result->xpath('//doc/str[@name="artikelImageurl"]')[0]->__toString();
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
                        'partnershop' => false,
                        'image' => $image
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}