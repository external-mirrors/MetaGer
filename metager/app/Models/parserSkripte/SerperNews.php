<?php

namespace app\Models\parserSkripte;

use App\Models\DeepResults\Button;
use App\Models\parserSkripte\Base\SerperBase;
use App\Models\Result;
use Log;

/**
 * Google News.
 *
 * Everything Serper-wide lives in [SerperBase]; this class is the `/news`
 * endpoint and its response shape.
 */
class SerperNews extends SerperBase
{
    const CONFIG_OVERLOAD = [
        'serper_news' => [
            ...parent::SHARED_CONFIG,
            'path' => '/news',
        ],
    ];

    protected function resultsKey(): string
    {
        return "news";
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);


            $news = $results->news;
            foreach ($news as $result) {
                $title = $result->title;
                $link = $result->link;
                $anzeigeLink = $link;
                $descr = $result->snippet;
                $this->counter++;

                $additionalInformation = [];
                if (property_exists($result, "date")) {
                    $additionalInformation["date_string"] = $result->date;
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

                if (property_exists($result, "sitelinks")) {
                    foreach ($result->sitelinks as $index => $clusterMember) {
                        $newResult->deepResults["buttons"][] = new Button($clusterMember->title, $clusterMember->link);
                    }
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
