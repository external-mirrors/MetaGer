<?php

namespace App\Models\Quicktips;

use App\SearchSettings;
use Cache;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Redis;
use Log;

class Quicktips
{
    use DispatchesJobs;

    private $quicktipUrl = "/1.1/quicktips.xml";
    const QUICKTIP_NAME = "quicktips";
    const CACHE_DURATION = 60;

    private $hash;
    private $startTime;
    private $enableQuotes;
    public $quicktips;
    public $new = true;

    public function __construct($search, $locale, $max_time, $enableQuotes = true)
    {
        if (\App::environment() === "production") {
            $this->quicktipUrl = "https://quicktips.metager.de" . $this->quicktipUrl;
        } else {
            $this->quicktipUrl = "https://dev.quicktips.metager.de" . $this->quicktipUrl;
        }
        $this->enableQuotes = $enableQuotes;
        $this->startTime = microtime(true);
        $this->startSearch($search, $locale, $max_time);
    }

    public function startSearch($search, $locale, $max_time)
    {
        if (\preg_match("/^([a-zA-Z]+)/", $locale, $matches)) {
            $locale = $matches[1];
        }
        $url = $this->quicktipUrl . "?search=" . $this->normalize_search($search) . "&locale=" . $locale . "&quotes=" . ($this->enableQuotes ? "on" : "off");
        $this->hash = md5($url);

        if (!Cache::has($this->hash)) {
            if (!Redis::exists($this->hash)) {

                // Queue this search
                $mission = [
                    "resulthash" => $this->hash,
                    "url" => $url,
                    "useragent" => "",
                    "username" => null,
                    "password" => null,
                    "headers" => [],
                    "cacheDuration" => self::CACHE_DURATION,
                    "name" => "Quicktips",
                ];

                $mission = json_encode($mission);

                Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
            }
        }
    }

    /**
     * Load the current Quicktip results
     * 1. Retrieve the raw results
     * 2. Parse the results
     * Returns an empty array if no results are found
     */
    public function loadResults()
    {
        if (is_array($this->quicktips)) {
            $this->new = false;
            return;
        }
        $resultsRaw = $this->retrieveResults($this->hash, !app(SearchSettings::class)->javascript_enabled);
        if ($resultsRaw) {
            $this->parseResults($resultsRaw);
        }
    }

    /**
     * Retrieves queried quicktip results
     *
     * @param string $hash
     * @param bool $wait
     *
     * @return bool
     */
    public function retrieveResults($hash, $wait)
    {
        $body = null;
        if (Cache::has($this->hash)) {
            return Cache::get($this->hash, false);
        }

        do {
            $body = Redis::rpoplpush($this->hash, $this->hash);
            Redis::expire($this->hash, 60);
            if ($body === false || $body === null) {
                if ($wait) {
                    usleep(50 * 1000);
                }
            } else {
                break;
            }
        } while ($wait && microtime(true) - $this->startTime < 0.5);

        if ($body === false) {
            return false;
        }

        if ($body === "no-result") {
            return false;
        }

        if ($body !== null) {
            return $body;
        } else {
            return false;
        }
    }

    public function parseResults($quicktips_raw)
    {
        $quicktips_raw = preg_replace("/\r\n/si", "", $quicktips_raw);
        try {
            $content = \simplexml_load_string($quicktips_raw);
            if (!$content) {
                return;
            }

            $content->registerXPathNamespace('main', 'http://www.w3.org/2005/Atom');

            $this->quicktips = [];

            $quicktips_xpath = $content->xpath('main:entry');
            foreach ($quicktips_xpath as $quicktip_xml) {
                // Type
                $quicktip_xml->registerXPathNamespace('mg', 'http://metager.de/opensearch/quicktips/');
                $types = $quicktip_xml->xpath('mg:type');
                if (sizeof($types) > 0) {
                    $type = $types[0]->__toString();
                } else {
                    $type = "";
                }

                // Title
                $title = $quicktip_xml->title->__toString();

                // Link
                $link = $quicktip_xml->link['href']->__toString();

                // gefVon
                $quicktip_xml->registerXPathNamespace('mg', 'http://metager.de/opensearch/quicktips/');
                $gefVonTitles = $quicktip_xml->xpath('mg:gefVonTitle');
                if (sizeof($gefVonTitles) > 0) {
                    $gefVonTitle = $gefVonTitles[0]->__toString();
                } else {
                    $gefVonTitle = "";
                }
                $gefVonLinks = $quicktip_xml->xpath('mg:gefVonLink');
                if (sizeof($gefVonLinks) > 0) {
                    $gefVonLink = $gefVonLinks[0]->__toString();
                } else {
                    $gefVonLink = "";
                }

                $quicktip_xml->registerXPathNamespace('mg', 'http://metager.de/opensearch/quicktips/');
                $author = $quicktip_xml->xpath('mg:author');
                if (sizeof($author) > 0) {
                    $author = $author[0]->__toString();
                } else {
                    $author = "";
                }

                // Description
                $descr = $quicktip_xml->content->__toString();

                // Details
                $details = [];
                $details_xpath = $quicktip_xml->xpath('mg:details');
                if (sizeof($details_xpath) > 0) {
                    foreach ($details_xpath[0] as $detail_xml) {
                        $details_title = $detail_xml->title->__toString();
                        $details_link = $detail_xml->url->__toString();
                        $details_descr = $detail_xml->text->__toString();
                        $details[] = new \App\Models\Quicktips\Quicktip_detail(
                            $details_title,
                            $details_link,
                            $details_descr
                        );
                    }
                }
                $this->quicktips[] = new \App\Models\Quicktips\Quicktip(
                    $type,
                    $title,
                    $link,
                    $gefVonTitle,
                    $gefVonLink,
                    $author,
                    $descr,
                    $details
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing quicktips");
        }
    }

    public function normalize_search($search)
    {
        return urlencode($search);
    }
}