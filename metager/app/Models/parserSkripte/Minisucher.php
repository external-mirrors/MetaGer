<?php

namespace App\Models\parserSkripte;

use App\MetaGer;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;

class Minisucher extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'minism_science' => [
            'host' => 'minisucher.suma-lab.de',
            'path' => '/solr-minisucher/select/',
            'port' => 80,
            'query-parameter' => 'q',
            'input-encoding' => 'ISO-8859-1',
            'output-encoding' => '',
            'get-parameter' => [
                'version' => '2.2',
                'start' => '0',
                'rows' => '20',
                'indent' => 'on',
                'fl' => 'title,url,subcollection,documentDate',
                'hl' => 'true',
                'hl.fl' => 'content',
                'hl.snippets' => '4',
                'hl.mergeContiguous' => 'true',
                'fq' => 'subcollection:(campussearch OR unisde OR qubit OR hss OR unisusa)',
            ],
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'de' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1,
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'https://metager.org/search-engine',
                'index_name' => null,
                'display_name' => 'Minisucher Wissenschaft',
                'founded' => null,
                'headquarter' => null,
                'operator' => null,
                'index_size' => null,
            ],
        ],
        'minism_news' => [
            'host' => 'minisucher.suma-lab.de',
            'path' => '/solr-minisucher/select/',
            'port' => 80,
            'query-parameter' => 'q',
            'input-encoding' => 'ISO-8859-1',
            'output-encoding' => '',
            'get-parameter' => [
                'version' => '2.2',
                'start' => '0',
                'rows' => '20',
                'indent' => 'on',
                'fl' => 'title,url,subcollection,documentDate',
                'hl' => 'true',
                'hl.fl' => 'content',
                'hl.snippets' => '4',
                'hl.mergeContiguous' => 'true',
                'fq' => 'subcollection:(bundestagsdrucksache OR unisbundesratde OR twitter)',
            ],
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'de' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1,
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'https://metager.org/search-engine',
                'index_name' => null,
                'display_name' => 'Minisucher Nachrichten',
                'founded' => null,
                'headquarter' => null,
                'operator' => null,
                'index_size' => null,
            ],
        ],
    ];
    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
        # Für die Newssuche stellen wir die Minisucher auf eine Sortierung nach Datum um.
        if (app(MetaGer::class)->getFokus() === "nachrichten") {
            $this->configuration->getParameter->sort = "documentDate desc";
        }
    }

    public function loadResults($content)
    {
        try {
            $content = \simplexml_load_string($content);
        } catch (\Exception $e) {
            return;
        }
        if (!$content) {
            return;
        }

        $results = $content->xpath('//response/result/doc');

        $string = "";

        $counter = 0;
        $providerCounter = [];
        foreach ($results as $result) {
            try {
                $counter++;
                $result = \simplexml_load_string($result->saveXML());

                $title = $result->xpath('//doc/arr[@name="title"]/str')[0]->__toString();
                $link = $result->xpath('//doc/str[@name="url"]')[0]->__toString();
                $anzeigeLink = $link;
                $descr = "";

                $descriptions = $content->xpath("//response/lst[@name='highlighting']/lst[@name='$link']/arr[@name='content']/str");
                foreach ($descriptions as $description) {
                    $descr .= $description->__toString();
                }

                $descr = strip_tags($descr);

                $dateString = $result->xpath('//doc/date[@name="documentDate"]')[0]->__toString();

                $date = date_create_from_format("Y-m-d\TH:i:s\Z", $dateString);

                $dateVal = $date->getTimestamp();

                $additionalInformation = ['date' => $dateVal];

                $minism = $this->configuration->infos->displayName;
                $gefVon = "Minisucher: $minism";
                $subcollection = $result->xpath('//doc/str[@name="subcollection"]')[0]->__toString();

                $this->results[] = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $link,
                    $descr,
                    $gefVon,
                    $counter,
                    $additionalInformation
                );
            } catch (\ErrorException $e) {
                continue;
            }
        }
    }
}