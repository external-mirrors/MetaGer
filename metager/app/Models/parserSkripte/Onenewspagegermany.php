<?php

namespace app\Models\parserSkripte;

use App\Http\Controllers\Pictureproxy;
use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use App\Models\SearchEngineInfos;
use Carbon;

class Onenewspagegermany extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'onenewspagegermany' => [
            'host' => 'suche.newsdeutschland.com',
            'path' => '/suche.php',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'e' => '1',
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
                'homepage' => 'http://www.newsdeutschland.com/',
                'index_name' => null,
                'display_name' => 'OneNewspage (Deutschland)',
                'founded' => '2008',
                'headquarter' => 'Wales, England',
                'operator' => 'One News Page Ltd.',
                'index_size' => null,
            ],
        ],
        'onenewspagegermanyAll' => [
            'host' => 'suche.newsdeutschland.com',
            'path' => '/suche.php',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'e' => '1',
                'f' => '1',
                'n' => '100',
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
                'homepage' => 'http://www.newsdeutschland.com/',
                'index_name' => null,
                'display_name' => 'OneNewspage (Deutschland, mit Archiv)',
                'founded' => '2008',
                'headquarter' => 'Wales, England',
                'operator' => 'One News Page Ltd.',
                'index_size' => null,
            ],
        ],
        'onenewspagegermanyvideo' => [
            'host' => 'suche.newsdeutschland.com',
            'path' => '/videosuche.php',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'e' => '1',
                'n' => '10',
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
                'homepage' => 'http://www.newsdeutschland.com/videos.htm',
                'index_name' => null,
                'display_name' => 'OneNewspage Video (Deutschland)',
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

        $this->configuration->cost = 0;
        $this->configuration->infos = new SearchEngineInfos("http://www.newsdeutschland.com/", null, "OneNewspage", "2008", "Wales, England", "One News Page Ltd.", null, );
    }

    public function loadResults($result)
    {
        $counter = 0;
        foreach (explode("\n", $result) as $line) {
            $line = trim($line);
            if (strlen($line) > 0) {
                # Hier bekommen wir jedes einzelne Ergebnis
                $result = explode("|", $line);
                if (sizeof($result) < 3) {
                    continue;
                }
                $title = $result[0];
                $link = $result[2];
                $anzeigeLink = $link;
                $descr = $result[1];
                $additionalInformation = sizeof($result) > 3 ? ['date' => Carbon::createFromTimestamp(intval($result[3]))] : [];

                $faviconUrl = parse_url($link, PHP_URL_SCHEME) . "://" . parse_url($link, PHP_URL_HOST) . "/favicon.ico";
                $additionalInformation["favicon_url"] = $faviconUrl;

                $counter++;
                $this->results[] = new Result(
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
        }

        uasort($this->results, function (Result $a, Result $b) {
            $diff = $a->getDate()->diffInSeconds($b->getDate(), false);
            return $diff;
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
        $next = new Onenewspagegermany($this->name, $newConfiguration);
        $this->next = $next;
    }
}