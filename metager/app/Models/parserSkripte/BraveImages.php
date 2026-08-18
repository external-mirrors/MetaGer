<?php

namespace app\Models\parserSkripte;

use App\Models\DeepResults\Imagesearchdata;
use App\Models\parserSkripte\Base\BraveBase;
use App\Models\Result;
use Log;

/**
 * Brave's image search. Everything Brave-wide lives in [BraveBase]; this class
 * is the `/images/search` endpoint and its response shape.
 */
class BraveImages extends BraveBase
{
    const CONFIG_OVERLOAD = [
        'brave_images' => [
            ...parent::SHARED_CONFIG,
            'path' => '/res/v1/images/search',
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
                $descr = null;
                $this->counter++;
                $newResult = new Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    [
                        "image" => new Imagesearchdata($result->thumbnail->src, 0, 0, $result->properties->url, 0, 0),
                    ]
                );

                $this->results[] = $newResult;
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}
