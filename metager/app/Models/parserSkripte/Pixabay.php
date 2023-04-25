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

            if (property_exists($content, "total")) {
                $this->totalResults = $content->total;
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
            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));

            $newConfiguration->getParameter->page = $metager->getPage() + 1;

            if ($newConfiguration->getParameter->page * $newConfiguration->getParameter->per_page > $this->totalResults) {
                return;
            }

            $this->next = new Pixabay($this->name, $newConfiguration);
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}