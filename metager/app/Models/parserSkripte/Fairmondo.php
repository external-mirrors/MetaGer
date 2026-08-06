<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Fairmondo extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'fairmondo' => [
            'host' => 'www.fairmondo.de',
            'path' => '/articles.json',
            'port' => 443,
            'query-parameter' => 'article_search_form[q]',
            'input-encoding' => '',
            'output-encoding' => '',
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
                'homepage' => 'https://www.fairmondo.de',
                'index_name' => null,
                'display_name' => 'Fairmondo',
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
        try {
            $results = json_decode($result, true);

            if (empty($results)) {
                return;
            }

            $results = $results["articles"];
            foreach ($results as $result) {
                if ($this->counter >= 10) {
                    break;
                }

                $title = $result["title"];
                $link = "https://www.fairmondo.de/articles/" . $result["id"];
                $anzeigeLink = $link;
                $price = 0;
                $descr = "";
                if (isset($result['price_cents'])) {
                    $price = intval($result['price_cents']);
                    $descr .= "<p>Preis: " . (intval($result['price_cents']) / 100.0) . " €</p>";
                }
                if (isset($result['title_image_url'])) {
                    $image = $result['title_image_url'];
                }

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
                        'price' => $price,
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