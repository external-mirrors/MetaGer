<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use Log;

class Yandex extends Searchengine
{
    public $results = [];

    public function __construct($name, \StdClass $engine, \App\MetaGer $metager)
    {
        parent::__construct($name, $engine, $metager);
    }

    public function loadResults($result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }

            # let's check if the query got unquoted
            # in that case we will ignore all results because that would mean
            # a string search (query between "") was wished and no results for that foudn
            $reask = $content->xpath("//yandexsearch/response/reask");
            if (sizeof($reask) !== 0 && $reask[0]->{"rule"}->__toString()) {
                return;
            }

            $results = $content->xpath("//yandexsearch/response/results/grouping/group");
            foreach ($results as $result) {
                $title = strip_tags($result->{"doc"}->{"title"}->asXML());
                $link = $result->{"doc"}->{"url"}->__toString();
                $anzeigeLink = $link;
                $descr = strip_tags($result->{"doc"}->{"headline"}->asXML());
                if (!$descr) {
                    $descr = strip_tags($result->{"doc"}->{"passages"}->asXML());
                }
                if ($this->filterYandexResult($title, $descr, $link)) {
                    continue;
                }
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->engine,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->engine->infos->display_name,
                    $this->engine->infos->homepage,
                    $this->counter
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:\n" . $e->getMessage() . "\n" . $result);
            return;
        }
    }

    private function filterYandexResult($title, $description, $link)
    {
        /**
         * Yandex is currently not expected to have neutral results regarding this domains
         * Thats why we filter those out here.
         * Important: We do not filter out those domains completely as other search engines do have them in the index
         */
        $filtered_domains = [
            "rt.com",
            "sputnik.com"
        ];
        $target_domain = parse_url($link, PHP_URL_HOST);
        if ($target_domain !== false) {
            foreach ($filtered_domains as $filtered_domain) {
                if (preg_match("/[\b\.]{1}" . preg_quote($filtered_domain, "$/") . "/", $target_domain)) {
                    return true;
                }
            }
        }

        // Filters kyrillic results when the query is not kyrillic
        $maxRatio = 0.1; # Gibt den Prozentsatz von Kyrillischen Zeichen in Titel und Beschreibung an, ab dem das Ergebnis runter gerankt werden soll
        if (!preg_match('/[А-Яа-яЁё]/u', $this->query) === 1) {
            # Wir überprüfen das Verhältnis von Kyrillischen Zeichen im Titel
            if (preg_match_all('/[А-Яа-яЁё]/u', $title, $matches)) {
                $count = sizeof($matches[0]);
                $titleSize = strlen($title);
                $percKyr = $count / $titleSize;
                if ($percKyr > $maxRatio) {
                    return true;
                }
            }
            # Wir überprüfen das Verhältnis von Kyrillischen Zeichen in der Beschreibung
            if (preg_match_all('/[А-Яа-яЁё]/u', $description, $matches)) {
                $count = sizeof($matches[0]);
                $descrSize = strlen($description);
                $percKyr = $count / $descrSize;
                if ($percKyr > $maxRatio) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        $result = preg_replace("/\r\n/si", "", $result);
        try {
            $content = \simplexml_load_string($result);
            if (!$content) {
                return;
            }
            $resultCount = $content->xpath('//yandexsearch/response/results/grouping/found[@priority="all"]');
            if (!$resultCount || sizeof($resultCount) <= 0) {
                return;
            }
            $resultCount = intval($resultCount[0]->__toString());
            $pageLast = $content->xpath('//yandexsearch/response/results/grouping/page')[0];
            $pageLast = intval($pageLast["last"]->__toString());
            if (count($this->results) <= 0 || $pageLast >= $resultCount) {
                return;
            }
            $next = new Yandex($this->name, $this->engine, $metager);
            $next->getString .= "&page=" . ($metager->getPage() + 1);
            $next->hash = md5($next->engine->host . $next->getString . $next->engine->port . $next->name);
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:\n" . $e->getMessage() . "\n" . $result);
            return;
        }
    }
}
