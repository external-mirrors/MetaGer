<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Opencrawlpolitik extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            $results = $content->xpath('//rss/channel/item');
            $count = 0;
            foreach ($results as $result) {
                if ($count > 10) {
                    break;
                }

                $dateString = $result->{"opencrawlDate"}->__toString();

                $date = date_create_from_format("Ymd-Hi", $dateString);

                $dateVal = $date->getTimestamp();

                $additionalInformation = ['date' => $dateVal];

                $title = $result->{"title"}->__toString();
                $link = $result->{"link"}->__toString();
                $anzeigeLink = $link;
                $descr = strip_tags(htmlspecialchars_decode($result->{"description"}->__toString()));
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
                    $additionalInformation
                );
                $count++;
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}