<?php

namespace app\Models\parserSkripte;

use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Carbon;

class Onenewspage extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'onenewspage' => [
            'host' => 'search.onenewspage.com',
            'path' => '/search.php',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => '',
            'output-encoding' => '',
            'get-parameter' => [
                'e' => '1',
            ],
            'lang' => [
                'parameter' => '',
                'languages' => [
                    'en' => '',
                ],
                'regions' => [],
            ],
            'engine-boost' => 1,
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0,
            'infos' => [
                'homepage' => 'http://www.onenewspage.com/',
                'index_name' => null,
                'display_name' => 'OneNewspage',
                'founded' => '2008',
                'headquarter' => 'Wales, England',
                'operator' => 'One News Page Ltd.',
                'index_size' => null,
            ],
        ],
    ];
    public $results = [];
    public $resultCount = 0;

    private $offset = 0;
    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($result)
    {
        $results = trim($result);

        foreach (explode("\n", $results) as $result) {
            $res = explode("|", $result);
            if (sizeof($res) < 3) {
                continue;
            }
            $title                                = $res[0];
            $link                                 = $res[2];
            $anzeigeLink                          = $link;
            $descr                                = $res[1];
            $additionalInformation                = sizeof($res) > 3 ? ['date' => Carbon::createFromTimestamp(intval($res[3]))] : [];
            $faviconUrl                           = parse_url($link, PHP_URL_SCHEME) . "://" . parse_url($link, PHP_URL_HOST) . "/favicon.ico";
            $additionalInformation["favicon_url"] = $faviconUrl;
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
        }

        uasort($this->results, function (Result $a, Result $b) {
            // Der Feed liefert den Zeitstempel nur für Zeilen mit mehr als drei
            // Feldern, `Result::getDate()` ist deshalb `Carbon|null`. Eine
            // einzige undatierte Zeile hat hier den kompletten Nachrichten-Fokus
            // mit einem Aufruf auf null umgebracht — HTTP 500 für jede englische
            // Nachrichtensuche, im Web genauso wie in der App. Undatierte Treffer
            // sortieren jetzt hinter jeden datierten und behalten untereinander
            // ihre Reihenfolge.
            $dateA = $a->getDate();
            $dateB = $b->getDate();
            if ($dateA === null || $dateB === null) {
                return ($dateA === null ? 1 : 0) <=> ($dateB === null ? 1 : 0);
            }
            return (int) $dateA->diffInSeconds($dateB, false);
        });

        foreach ($this->results as $index => $result) {
            $this->results[$index]->sourceRank = 20 - $index;
        }

        if (count($this->results) > $this->resultCount) {
            $this->resultCount += count($this->results);
        }
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        if (count($this->results) <= 0) {
            return;
        }

        /** @var SearchEngineConfiguration */
        $newConfiguration = unserialize(serialize($this->configuration));
        if (property_exists($newConfiguration->getParameter, "o")) {
            $newConfiguration->getParameter->o += count($this->results);
        } else {
            $newConfiguration->getParameter->o = count($this->results);
        }
        $next       = new Onenewspage($this->name, $newConfiguration);
        $this->next = $next;
    }
}