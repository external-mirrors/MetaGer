<?php

namespace app\Models\parserSkripte;

use App\Models\DeepResults\Imagesearchdata;
use App\Models\parserSkripte\Base\SerperBase;
use App\Models\Result;
use Log;

/**
 * Google's image search.
 *
 * Everything Serper-wide lives in [SerperBase]; this class is the `/images`
 * endpoint and its response shape.
 */
class SerperImages extends SerperBase
{
    const CONFIG_OVERLOAD = [
        'serper_images' => [
            ...parent::SHARED_CONFIG,
            'path' => '/images',
        ],
    ];

    protected function resultsKey(): string
    {
        return "images";
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);


            $images = $results->images;
            foreach ($images as $result) {
                $title = $result->title;
                $link = $result->link;
                $anzeigeLink = $link;
                $descr = "";
                $this->counter++;

                $additionalInformation = [
                    "image" => new Imagesearchdata($result->thumbnailUrl, $result->thumbnailWidth, $result->thumbnailHeight, $result->imageUrl, $result->imageWidth, $result->imageHeight)
                ];

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

                $this->results[] = $newResult;
            }

            // Check if news are relevant to this query
            if (property_exists($results, "topStories") && is_array($results->topStories)) {
                foreach ($results->topStories as $index => $news_result) {
                    $new_news_result = new Result(
                        1,
                        $news_result->title,
                        $news_result->link,
                        $news_result->link,
                        $news_result->title,
                        $this->configuration->infos->displayName,
                        $this->configuration->infos->homepage,
                        $index + 1,
                        []
                    );
                    if (property_exists($news_result, "imageUrl")) {
                        $new_news_result->image = $news_result->imageUrl;
                    }
                    if (property_exists($news_result, "date")) {
                        $new_news_result->age = $news_result->date;
                    }
                    $this->news[] = $new_news_result;
                }
            }

            // Check if videos are relevant to this query
            if (property_exists($results, "videos") && property_exists($results->videos, "results") && is_array($results->videos->results)) {
                foreach ($results->videos->results as $index => $video_result) {
                    $new_video_result = new Result(
                        1,
                        $video_result->title,
                        $video_result->url,
                        $video_result->meta_url->netloc . " " . $video_result->meta_url->path,
                        $video_result->description,
                        $this->configuration->infos->displayName,
                        $this->configuration->infos->homepage,
                        $index + 1,
                        []
                    );
                    if (property_exists($video_result, "thumbnail")) {
                        $new_video_result->image = $video_result->thumbnail->src;
                    }
                    if (property_exists($video_result, "age")) {
                        $new_video_result->age = $video_result->age;
                    }
                    $this->videos[] = $new_video_result;
                }
            }


        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}
