<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Ebay extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'ebay' => [
            'host' => 'svcs.ebay.com',
            'path' => '/services/search/FindingService/v1',
            'port' => 80,
            'query-parameter' => 'keywords',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'OPERATION-NAME' => 'findItemsByKeywords',
                'SERVICE-VERSION' => '1.0.0',
                'RESPONSE-DATA-FORMAT' => 'XML',
                'REST-PAYLOAD' => '',
                'paginationInput.entriesPerPage' => '20',
                'GLOBAL-ID' => 'EBAY-DE',
            ],
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'de' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1.1,
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'http://www.ebay.de/',
                'index_name' => null,
                'display_name' => 'Ebay',
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

            $results = $content->{"searchResult"};
            $count = 0;
            foreach ($results->{"item"} as $result) {
                $title = $result->{"title"}->__toString();
                $link = $result->{"viewItemURL"}->__toString();
                $anzeigeLink = $link;
                $time = $result->{"listingInfo"}->{"endTime"}->__toString();
                $time = date(DATE_RFC2822, strtotime($time));
                $price = intval($result->{"sellingStatus"}->{"convertedCurrentPrice"}->__toString()) * 100;
                $descr = "Preis: " . $result->{"sellingStatus"}->{"convertedCurrentPrice"}->__toString() . " €";
                $descr .= ", Versandkosten: " . $result->{"shippingInfo"}->{"shippingServiceCost"}->__toString() . " €";
                if (isset($result->{"listingInfo"}->{"listingType"})) {
                    $descr .= ", Auktionsart: " . $result->{"listingInfo"}->{"listingType"}->__toString();
                }

                $descr .= ", Auktionsende: " . $time;
                if (isset($result->{"primaryCategory"}->{"categoryName"})) {
                    $descr .= ", Kategorie: " . $result->{"primaryCategory"}->{"categoryName"}->__toString();
                }

                $image = $result->{"galleryURL"}->__toString();
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
                        'price' => $price,
                        'image' => $image
                    ]
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