<?php

namespace app\Models\parserSkripte;

use App\Models\parserSkripte\Base\SerperBase;
use App\Models\Result;
use App\Search\GoogleRedirectResolver;
use Log;

/**
 * Google Shopping — the only Serper endpoint whose results are products rather than pages.
 *
 * Everything Serper-wide lives in [SerperBase]; this class is the `/shopping`
 * endpoint and its response shape.
 *
 * ## A product that links to Google is dropped
 *
 * Serper links products to Google's own product page
 * (`www.google.com/search?ibp=oshop&prds=…`) rather than to the shop. That is
 * not a redirect [GoogleRedirectResolver] could resolve: it answers with
 * Google's cookie consent and then a captcha, and the answer names no shop
 * URL to use instead. Shown, it would send the user to Google with their IP
 * and cookies. Products with a link to the shop itself are kept, should
 * Serper send those again.
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
                if (GoogleRedirectResolver::pointsAtGoogle($result->link)) {
                    continue;
                }

                $title = $result->title;
                $link = $result->link;
                $anzeigeLink = $link;
                $descr = property_exists($result, "delivery") ? $result->delivery : "";
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
