<?php

namespace app\Models\parserSkripte;

use App\Models\parserSkripte\Base\BraveBase;
use App\Models\Result;
use Log;

/**
 * Brave's news search. Everything Brave-wide lives in [BraveBase]; this class
 * is the `/news/search` endpoint and its response shape.
 */
class BraveNews extends BraveBase
{
    const CONFIG_OVERLOAD = [
        'brave_news' => [
            ...parent::SHARED_CONFIG,
            'path' => '/res/v1/news/search',
            'get-parameter' => [
                'count' => 100,
                'offset' => 0,
            ],
        ],
    ];

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);

            $this->captureAlteredQuery($results);

            foreach ($results->results as $result) {
                $title = html_entity_decode($result->title);
                $link = $result->url;
                $anzeigeLink = $result->meta_url->netloc . " " . $result->meta_url->path;
                $descr = html_entity_decode($result->description);
                $this->counter++;
                $additionalInformation = [];
                if (property_exists($result, "age")) {
                    $additionalInformation["date_string"] = $result->age;
                }
                if (property_exists($result, "meta_url") && property_exists($result->meta_url, "favicon")) {
                    $additionalInformation["favicon_url"] = $result->meta_url->favicon;
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

                if (property_exists($result, "thumbnail")) {
                    $newResult->image = $result->thumbnail->src;
                }

                $this->results[] = $newResult;
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    /**
     * The news endpoint has no `more_results_available`; an empty page is the
     * only signal that there is nothing further.
     */
    protected function hasMoreResults($results): bool
    {
        return !empty($results->results);
    }
}
