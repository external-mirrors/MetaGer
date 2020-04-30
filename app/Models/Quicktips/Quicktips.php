<?php

namespace App\Models\Quicktips;

use Cache;
use LaravelLocalization;
use Log;

class Quicktips
{

    private $quicktipUrl = "/1.1/quicktips.xml";
    private $results = [];
    const QUICKTIP_NAME = "quicktips";
    const CACHE_DURATION = 60 * 60;

    private $hash;

    public function __construct($search/*, $locale, $max_time*/)
    {
        $locale = LaravelLocalization::getCurrentLocale();
        if (env("APP_ENV") === "production") {
            $this->quicktipUrl = "https://quicktips.metager.de" . $this->quicktipUrl;
        } else {
            $this->quicktipUrl = "https://dev.quicktips.metager.de" . $this->quicktipUrl;
        }
        $this->startSearch($search, $locale);
    }

    public function startSearch($search, $locale)
    {
        $url = $this->quicktipUrl . "?search=" . $this->normalize_search($search) . "&locale=" . $locale;
        $this->hash = md5($url);

        if (!Cache::has($this->hash)) {
            $results = file_get_contents($url);
            Cache::put($this->hash, $results, Quicktips::CACHE_DURATION);
        } else {
            $results = Cache::get($this->hash);
        }
        $this->results = $this->loadResults($results);
    }

    /**
     * Load the current Quicktip results
     * 1. Retrieve the raw results
     * 2. Parse the results
     * Returns an empty array if no results are found
     */
    public function loadResults($resultsRaw)
    {
        if ($resultsRaw) {
            $results = $this->parseResults($resultsRaw);
            return $results;
        } else {
            return [];
        }
    }

    public function retrieveResults($hash)
    {
        $body = null;

        if (Cache::has($this->hash)) {
            $body = Cache::get($this->hash);
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

            $quicktips = [];

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
                $quicktips[] = new \App\Models\Quicktips\Quicktip(
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
            return $quicktips;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing quicktips");
            return [];
        }
    }

    public function normalize_search($search)
    {
        return urlencode($search);
    }

    public function getResults()
    {
        return $this->results;
    }
}
