<?php

namespace app\Models\parserSkripte;

use App\Models\DeepResults\Button;
use App\Models\parserSkripte\Base\BraveBase;
use App\Models\Result;
use Log;

/**
 * Brave's web search — and, in the same response, the news and video blocks it
 * decides are relevant to the query.
 *
 * Everything Brave-wide lives in [BraveBase]; this class is the `/web/search`
 * endpoint and its response shape.
 */
class Brave extends BraveBase
{
    const CONFIG_OVERLOAD = [
        'brave' => [
            ...parent::SHARED_CONFIG,
            'path' => '/res/v1/web/search',
            'get-parameter' => [
                'count' => 20,
                'offset' => 0,
            ],
        ],
    ];

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);

            $this->captureAlteredQuery($results);

            $web = $results->web;
            foreach ($web->results as $result) {
                $title = html_entity_decode($result->title);
                $link = $result->url;
                $anzeigeLink = $result->meta_url->netloc . " " . $result->meta_url->path;
                $descr = html_entity_decode($result->description);
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
                    []
                );

                if (property_exists($result, "thumbnail")) {
                    $newResult->image = $result->thumbnail->src;
                }

                if (property_exists($result, "cluster")) {
                    foreach ($result->cluster as $index => $clusterMember) {
                        $clustertitle = $clusterMember->title;
                        $clusterlink = $clusterMember->url;
                        $clusterdescr = html_entity_decode($clusterMember->description);
                        if (strlen($clusterdescr) > 100) {
                            $clusterdescr = substr($clusterdescr, 0, 100) . "...";
                        }
                        $newResult->inheritedResults[] = new \App\Models\Result($this->configuration->engineBoost, $clustertitle, $clusterlink, $clusterlink, $clusterdescr, $this->configuration->infos->displayName, $this->configuration->infos->homepage, ($index + 1), []);
                    }
                }

                if (property_exists($result, "deep_results")) {
                    if (property_exists($result->deep_results, "buttons")) {
                        foreach ($result->deep_results->buttons as $button) {
                            $newResult->deepResults["buttons"][] = new Button($button->title, $button->url);
                        }
                    }
                }

                $this->results[] = $newResult;
            }

            // Check if news are relevant to this query
            if (property_exists($results, "news") && property_exists($results->news, "results") && is_array($results->news->results)) {
                foreach ($results->news->results as $index => $news_result) {
                    $new_news_result = new Result(
                        1,
                        $news_result->title,
                        $news_result->url,
                        $news_result->meta_url->netloc . " " . $news_result->meta_url->path,
                        $news_result->description,
                        $this->configuration->infos->displayName,
                        $this->configuration->infos->homepage,
                        $index + 1,
                        []
                    );
                    if (property_exists($news_result, "thumbnail")) {
                        $new_news_result->image = $news_result->thumbnail->src;
                    }
                    if (property_exists($news_result, "age")) {
                        $new_news_result->age = $news_result->age;
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
