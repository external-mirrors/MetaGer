<?php

namespace app\Models\parserSkripte;

use App\Http\Controllers\Pictureproxy;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Crypt;
use Log;

class BingBilder extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);
            if (!empty($results->totalEstimatedMatches)) {
                $this->totalResults = $results->totalEstimatedMatches;
            }
            $results = $results->value;

            foreach ($results as $result) {
                $title = $result->name;
                $link = $result->hostPageUrl;
                $anzeigeLink = $link;
                $descr = "";
                $image = $this->generateThumbnailUrl($result->thumbnailUrl);
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
                        'image' => $image,
                        'imagedimensions' => [
                            "width" => $result->width,
                            "height" => $result->height
                        ]
                    ]
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
        try {
            $results = json_decode($result);

            if (empty($results->totalEstimatedMatches)) {
                return;
            }
            $totalMatches = $results->totalEstimatedMatches;
            $nextOffset = $results->nextOffset;

            if ($nextOffset >= $totalMatches) {
                return;
            }

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));

            $newConfiguration->getParameter->offset = $nextOffset;
            $next = new BingBilder($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    public static function generateThumbnailUrl(string $url)
    {

        $newHeight = 150;

        $requestDataBing = [
            "h" => $newHeight,
        ];

        $requestDataBing = http_build_query($requestDataBing, "", "&", PHP_QUERY_RFC3986);
        $url .= "&" . $requestDataBing;

        return Pictureproxy::generateUrl($url);
    }
}