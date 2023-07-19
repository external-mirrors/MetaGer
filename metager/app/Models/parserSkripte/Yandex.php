<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Yandex extends Searchengine
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
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
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
         * Returns true if the result is to be excluded.
         */
        $filtered_domains = [
            "rt.com",
            "sputniknews.com"
        ];
        $target_domain = parse_url($link, PHP_URL_HOST);
        if ($target_domain !== false) {
            foreach ($filtered_domains as $filtered_domain) {
                if (preg_match("/(^|\b|\.){1}" . preg_quote($filtered_domain, "/") . "$/", $target_domain)) {
                    return true;
                }
            }
        }


        // If the query does not contain kyrillic characters then the result must not contain them or they will be filtered
        if (
            preg_match('/[А-Яа-яЁё]/u', $this->configuration->getParameter->query) !== 1 &&
            (preg_match('/[А-Яа-яЁё]/u', $title) === 1 ||
                preg_match('/[А-Яа-яЁё]/u', $description) === 1)
        ) {
            return true;
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
            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->page = $metager->getPage() + 1;
            $next = new Yandex($this->name, $newConfiguration);
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:\n" . $e->getMessage() . "\n" . $result);
            return;
        }
    }
}