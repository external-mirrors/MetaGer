<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use LaravelLocalization;
use Log;

class Overture extends Searchengine
{
    public $results = [];

    public function __construct($name, \stdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);

        $this->checkLanguage();

        # We need some Affil-Data for the advertisements
        $this->getString = $this->generateGetString($this->query);
        $this->getString .= $this->getOvertureAffilData($metager->getUrl());
        $this->updateHash();
    }

    private function checkLanguage()
    {
        if (LaravelLocalization::getCurrentLocale() === 'en') {
            $supported_default_languages = [
                "en_US" => "us",
                "en_GB" => "gb",
                "en_IE" => "ie",
                "en_AU" => "au",
                "en_NZ" => "nz",
            ];
            $preferred_language = request()->getPreferredLanguage(\array_keys($supported_default_languages));

            if (\array_key_exists($preferred_language, $supported_default_languages)) {
                $this->engine->{"get-parameter"}->mkt = $supported_default_languages[$preferred_language];
            }
        }
    }

    public function loadResults($result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }
            # Yahoo gives us the total Result Count
            $resultCount = $content->xpath('//Results/ResultSet[@id="inktomi"]/MetaData/TotalHits');
            if (sizeof($resultCount) > 0) {
                $resultCount = intval($resultCount[0]->__toString());
            } else {
                $resultCount = 0;
            }
            $this->totalResults = $resultCount;
            $results = $content->xpath('//Results/ResultSet[@id="inktomi"]/Listing');
            foreach ($results as $result) {
                $title = html_entity_decode($result["title"]);
                $link = $result->{"ClickUrl"}->__toString();
                $anzeigeLink = $result["siteHost"];
                $descr = html_entity_decode($result["description"]);
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->engine,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->engine->infos->display_name,
                    $this->engine->infos->homepage,
                    $this->counter,
                    []
                );
            }

            # Nun noch die Werbeergebnisse:
            $ads = $content->xpath('//Results/ResultSet[@id="searchResults"]/Listing');
            foreach ($ads as $ad) {
                $title = html_entity_decode($ad["title"]);
                $link = $ad->{"ClickUrl"}->__toString();
                $anzeigeLink = $ad["siteHost"];
                $descr = html_entity_decode($ad["description"]);
                $this->counter++;
                $this->ads[] = new \App\Models\Result(
                    $this->engine,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->engine->infos->display_name,
                    $this->engine->infos->homepage,
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

        # Erstellen des neuen Suchmaschinenobjekts und anpassen des GetStrings:
        $next = new Overture($this->name, $this->engine, $metager);
        $next->getString = preg_replace("/&Keywords=.*?&/si", "&", $next->getString) . "&" . $nextArgs;
        $next->hash = md5($next->engine->host . $next->getString . $next->engine->port . $next->name);
        $this->next = $next;
    }

    # Liefert Sonderdaten für Yahoo
    private function getOvertureAffilData($url)
    {
        $affil_data = 'ip=' . $this->ip;
        $affil_data .= '&ua=' . $this->useragent;
        $affilDataValue = $this->urlEncode($affil_data);

        if (\preg_match("/https:\/\/.*\.review\.metager\.de\//", $url)) {
            $serve_domain = "https://metager.de/";
            if (LaravelLocalization::getCurrentLocale() === "en") {
                $serve_domain = "https://metager.org/";
            }
            $url = \preg_replace("/https:\/\/.*\.review\.metager\.de\//", $serve_domain, $url);
        }

        # Wir benötigen die ServeUrl:
        $serveUrl = $this->urlEncode($url);

        return "&affilData=" . $affilDataValue . "&serveUrl=" . $serveUrl;
    }
}
