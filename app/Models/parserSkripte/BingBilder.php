<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;

class BingBilder extends Searchengine
{
    public $results = [];

    public function __construct($name, \stdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);
            $this->totalResults = $results->totalEstimatedMatches;
            $results = $results->value;

            foreach ($results as $result) {
                $title = $result->name;
                $link = $result->hostPageUrl;
                $anzeigeLink = $link;
                $descr = "";
                $image = $result->thumbnailUrl;
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->engine,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->engine->{"display-name"},
                    $this->engine->homepage,
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
            throw $e;
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        try {
            $results = json_decode($result);

            $totalMatches = $results->totalEstimatedMatches;
            $nextOffset = $results->nextOffset;

            if ($nextOffset >= $totalMatches) {
                return;
            }

            $newEngine = unserialize(serialize($this->engine));
            $newEngine->{"get-parameter"}->offset = $nextOffset;
            $next = new BingBilder($this->name, $newEngine, $metager);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    public static function generateThumbnailUrl(\App\Models\Result $result)
    {
        $url = $result->image;

        // all images get cropped to a width of 400 px
        // We will calculate the height after cropping
        // All Images in a row get 250px 
        // If the height after cropping is a multiple of that plus the gap between rows
        // we will allow the image to span up to three rows
        $newWidth = 400;
        $newHeight = 250;
        $heightMultiplier = 1; // Can be 1..3 in the end
        $gapPixels = 8;

        $width = $result->imageDimensions["width"];
        $height = $result->imageDimensions["height"];

        $heightAfterCrop = $height * (400 / $width);
        $heightMultiplier = max(1, min(3, floor($heightAfterCrop / 250)));

        $newHeight = $newHeight * $heightMultiplier + (($heightMultiplier - 1) * $gapPixels);

        $requestDataBing = [
            "w" => $newWidth,
            "h" => $newHeight,
            "c" => 7, // Smart Cropping
        ];

        $requestDataBing = http_build_query($requestDataBing, "", "&", PHP_QUERY_RFC3986);
        $url .= "&" . $requestDataBing;

        $requestData = [];
        $requestData["url"] = $url;
        $link = action('Pictureproxy@get', $requestData);
        return [
            "link" => $link,
            "height-multiplier" => $heightMultiplier
        ];
    }
}
