<?php

namespace App\Models\parserSkripte;

use App\MetaGer;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class OvertureAds extends Searchengine
{

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
        # We need some Affil-Data for the advertisements
        $this->setOvertureAffilData(app(MetaGer::class)->getUrl());
    }

    public function loadResults($result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            $ads = $content->xpath('//Results/ResultSet[@id="searchResults"]/Listing');
            foreach ($ads as $ad) {
                $title = html_entity_decode($ad["title"]);
                $link = $ad->{"ClickUrl"}->__toString();
                $anzeigeLink = $ad["siteHost"];
                $descr = html_entity_decode($ad["description"]);
                $this->counter++;
                $this->ads[] = new \App\Models\Result(
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
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }

        if (!$content) {
            return;
        }

        // Yahoo liefert, wenn es keine weiteren Ergebnisse hat immer wieder die gleichen Ergebnisse
        // Wir müssen also überprüfen, ob wir am Ende der Ergebnisse sind
        $resultCount = $content->xpath('//Results/ResultSet[@id="inktomi"]/MetaData/TotalHits');
        $results = $content->xpath('//Results/ResultSet[@id="inktomi"]/Listing');
        if (isset($resultCount[0]) && sizeof($results) > 0) {
            $resultCount = intval($resultCount[0]->__toString());
            $lastResultOnPage = intval($results[sizeof($results) - 1]["rank"]);
            if ($resultCount <= $lastResultOnPage) {
                return;
            }
        } else {
            return;
        }

        $nextArgs = $content->xpath('//Results/NextArgs');
        if (isset($nextArgs[0])) {
            $nextArgs = $nextArgs[0]->__toString();
        } else {
            $nextArgs = $content->xpath('//Results/ResultSet[@id="inktomi"]/NextArgs');
            if (isset($nextArgs[0])) {
                $nextArgs = $nextArgs[0]->__toString();
            } else {
                return;
            }
        }

        parse_str($nextArgs, $query_data);
        /** @var SearchEngineConfiguration */
        $newConfiguration = unserialize(serialize($this->configuration));
        foreach ($query_data as $key => $value) {
            $newConfiguration->getParameter->$key = $value;
        }
        // Erstellen des neuen Suchmaschinenobjekts und anpassen des GetStrings:
        $next = new OvertureAds($this->name, $newConfiguration);
        $this->next = $next;
    }

    # Liefert Sonderdaten für Yahoo
    private function setOvertureAffilData($url)
    {
        $affil_data = 'ip=' . $this->ip;
        $affil_data .= '&ua=' . $this->useragent;
        $this->configuration->getParameter->affilData = $affil_data;
        $this->configuration->getParameter->serveUrl = $url;
    }
}