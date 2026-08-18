<?php

namespace app\Models\parserSkripte;

use App\Models\parserSkripte\Base\SerperBase;
use App\Models\Result;
use Log;

/**
 * Google Shopping — the only Serper endpoint whose results are products rather than pages.
 *
 * Everything Serper-wide lives in [SerperBase]; this class is the `/shopping`
 * endpoint and its response shape.
 */
class SerperShopping extends SerperBase
{
    const CONFIG_OVERLOAD = [
        'serper_shopping' => [
            ...parent::SHARED_CONFIG,
            'path' => '/shopping',
        ],
    ];

    protected function resultsKey(): string
    {
        return "shopping";
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);


            $shopping = $results->shopping;
            foreach ($shopping as $result) {
                $title = $result->title;
                $link = $result->link;
                $anzeigeLink = $link;
                $descr = $result->delivery;
                $this->counter++;

                $additionalInformation = [];
                if (property_exists($result, "price")) {
                    $additionalInformation["price"] = $result->price;
                }

                $newResult = new Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    $additionalInformation
                );

                if (property_exists($result, "imageUrl")) {
                    $newResult->image = $result->imageUrl;
                }

                $this->results[] = $newResult;
            }

        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}
