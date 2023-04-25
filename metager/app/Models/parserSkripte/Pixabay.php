<?php

namespace app\Models\parserSkripte;

use App\Http\Controllers\Pictureproxy;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Pixabay extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        try {
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            $results = $content->hits;
            foreach ($results as $result) {
                $title = $result->tags;
                $link = $result->pageURL;
                $anzeigeLink = $link;
                $descr = "";
                $image = Pictureproxy::generateUrl($result->previewURL);
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
                            "width" => $result->previewWidth,
                            "height" => $result->previewHeight
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
            $content = json_decode($result);
            if (!$content) {
                return;
            }

            $page = $metager->getPage() + 1;
            try {
                $content = json_decode($result);
            } catch (\Exception $e) {
                Log::error("Results from $this->name are not a valid json string");
                return;
            }
            if (!$content) {
                return;
            }
            if ($page * 20 > $content->total) {
                return;
            }
            $next = new Pixabay($this->name, $this->engine, $metager);
            $next->getString .= "&page=" . $page;
            $next->hash = md5($next->engine->host . $next->getString . $next->engine->port . $next->name);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}