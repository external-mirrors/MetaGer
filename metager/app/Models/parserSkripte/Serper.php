<?php

namespace app\Models\parserSkripte;

use App\Models\DeepResults\Button;
use App\Models\parserSkripte\Base\SerperBase;
use App\Models\Result;
use Log;

/**
 * Brave's web search equivalent: Google's organic results, with the sitelinks Google attaches to them.
 *
 * Everything Serper-wide lives in [SerperBase]; this class is the `/search`
 * endpoint and its response shape.
 */
class Serper extends SerperBase
{
    const CONFIG_OVERLOAD = [
        'serper_web' => [
            ...parent::SHARED_CONFIG,
            'path' => '/search',
        ],
    ];

    protected function resultsKey(): string
    {
        return "organic";
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);


            $web = $results->organic;
            foreach ($web as $result) {
                $title = $result->title;
                $link = $result->link;
                $anzeigeLink = $link;
                $descr = property_exists($result, "snippet") ? $result->snippet : "";
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

                if (property_exists($result, "thumbnail")) {
                    $newResult->image = $result->thumbnail;
                }
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
