<?php

namespace app\Models\parserSkripte;

use App\MetaGer;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use App\PrometheusExporter;
use Log;
use Illuminate\Support\Facades\Redis;
use SimpleXMLElement;

class Overture extends Searchengine
{
    public $failed_results = false;
    public $results = [];

    /** Advertisement Data */
    /** @var string SearchID */
    public $search_id = null;
    /** @var string ClientID */
    public $client_id = null;
    /** @var string ImpressionGUID */
    public $impression_guid = null;
    /** @var string RGUId */
    public $rguid = null;
    /** @var string When true prints information about configuration in browser console */
    public $test_mode = "false";

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
        if (app()->environment("local")) {
            $this->test_mode = "true";
        }
    }

    public function applySettings()
    {
        parent::applySettings();
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
            // Yahoo gives us the total Result Count
            $resultCount = $content->xpath('//Results/ResultSet[@id="inktomi"]/MetaData/TotalHits');
            if (sizeof($resultCount) > 0) {
                $resultCount = intval($resultCount[0]->__toString());
            } else {
                $resultCount = 0;
            }
            $this->totalResults = $resultCount;
            $results = $content->xpath('//Results/ResultSet[@id="inktomi"]/Listing');
            if (!is_array($results)) {
                $results = [];
            }
            /** @var SimpleXMLElement[] */
            $ads = $content->xpath('//Results/ResultSet[@id="searchResults"]/Listing');
            if (!is_array($ads)) {
                $ads = [];
            }

            foreach ($results as $result) {
                $title = html_entity_decode($result["title"]);
                $link = $result->{"ClickUrl"}->__toString();
                $anzeigeLink = $result["siteHost"];
                $descr = html_entity_decode($result["description"]);
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
                    []
                );
            }

            // Parse Advertisement Data
            $this->search_id = $content->xpath("//Results/SearchID");
            if (is_array($this->search_id)) {
                if (sizeof($this->search_id) > 0) {
                    $this->search_id = $this->search_id[0]->__toString();
                } else {
                    $this->search_id = null;
                }
            }
            $this->client_id = $content->xpath("//Results/ClientID");
            if (is_array($this->client_id)) {
                if (sizeof($this->client_id) > 0) {
                    $this->client_id = $this->client_id[0]->__toString();
                } else {
                    $this->client_id = null;
                }
            }
            $this->impression_guid = $content->xpath("//Results/ImpressionGUID");
            if (is_array($this->impression_guid)) {
                if (sizeof($this->impression_guid) > 0) {
                    $this->impression_guid = $this->impression_guid[0]->__toString();
                } else {
                    $this->impression_guid = null;
                }
            }
            $this->rguid = $content->xpath("//Results/RGUId");
            if (is_array($this->rguid)) {
                if (sizeof($this->rguid) > 0) {
                    $this->rguid = $this->rguid[0]->__toString();
                } else {
                    $this->rguid = null;
                }
            }

            // Nun noch die Werbeergebnisse:
            /** @var SimpleXMLElement $ad */
            foreach ($ads as $ad) {
                $title = html_entity_decode($ad["title"]);
                $link = $ad->{"ClickUrl"}->__toString();
                $anzeigeLink = $ad["siteHost"];
                $descr = html_entity_decode($ad["description"]);
                $this->counter++;

                // Advertisement Data of result
                $yiid = "";
                if (isset($ad["ImpressionId"])) {
                    $yiid = $ad["ImpressionId"]->__toString();
                }
                $appns = null;
                $k = null;
                if (isset($ad["appNs"]) && isset($ad["k"])) {
                    $appns = $ad["appNs"]->__toString();
                    $k = $ad["k"]->__toString();
                }

                $this->ads[] = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    [
                        "ad_data" => [
                            "yiid" => $yiid,
                            "appns" => $appns,
                            "k" => $k
                        ]
                    ]
                );
            }
            if (sizeof($this->results) === 0 && sizeof($this->ads) === 0 && !$this->failed) {
                $this->log_failed_yahoo_search();
                $this->configuration->getParameter->Keywords .= " -qwertzy";
                $this->cached = false;
                Redis::del($this->getHash());
                $this->startSearch();
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
        # Erstellen des neuen Suchmaschinenobjekts und anpassen des GetStrings:
        $next = new Overture($this->name, $newConfiguration);
        $this->next = $next;
    }

    // Liefert Sonderdaten für Yahoo
    private function setOvertureAffilData($url)
    {
        $affil_data = 'ip=' . request()->ip();
        $affil_data .= '&ua=' . request()->useragent();

        $serve_domain = "https://metager.de/";
        if ($this->configuration->getParameter->mkt !== "de") {
            $serve_domain = "https://metager.org/";
        }

        if (\preg_match("/https?:\/\/[^\/]+\/.*/", $url)) {
            $url = \preg_replace("/https?:\/\/[^\/]+\//", $serve_domain, $url);
        }

        $this->configuration->getParameter->affilData = $affil_data;
        $this->configuration->getParameter->serveUrl = $url;
    }

    private function log_failed_yahoo_search()
    {
        PrometheusExporter::OvertureFail();
        $log_file = storage_path("logs/metager/yahoo_fail.csv");

        $data = [
            "time" => now()->format("Y-m-d H:i:s"),
            "query" => $this->configuration->getParameter->Keywords
        ];
        $fh = fopen($log_file, "a");
        try {
            fputcsv($fh, $data);
        } finally {
            fclose($fh);
        }
    }
}