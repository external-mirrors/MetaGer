<?php

namespace app\Models\parserSkripte;

use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Carbon\Carbon;
use Log;

/**
 * Staan, the web search API of European Search Perspective, which is the
 * index behind Qwant and Ecosia. Documented at https://docs.staan.ai/docs/web-search.
 *
 * What shapes this class:
 *
 *   - **Ten results per call, at most four pages.** `count` is fixed at 10
 *     and `offset` takes 0, 10, 20 and 30, nothing else, so [getNext] stops
 *     after the fourth page.
 *   - **Three languages.** The index covers French, German and English. A
 *     market is a language plus the country that weights results in that
 *     language: `de-de`, `fr-fr` and ten English markets, from `en-us` to
 *     `en-sg`. Every other locale disables Staan with `INCOMPATIBLE_LOCALE`
 *     rather than searching an index in a language the user did not ask for.
 *   - **German and French outside Germany and France.** There is no `de-at`,
 *     `de-ch` or `fr-ca`. Staan's own FAQ says to use `de-de` for Austria and
 *     notes that Austria-specific pages may rank lower than they should. That
 *     is a better result than no Staan at all, so these locales use the
 *     language's home market.
 *   - **Billed per request, at €2 per 1,000.** At our usual 100% markup that is
 *     €0.004 per request, so the cost is 0.4 Token. The engine is
 *     `disabledByDefault`, so a user has to opt in before a search pays for it.
 */
class Staan extends Searchengine
{
    /** The highest `offset` Staan accepts. */
    public const MAX_OFFSET = 30;

    /** Results per page. Staan does not let a caller change it. */
    public const PAGE_SIZE = 10;

    /**
     * MetaGer market -> Staan market.
     *
     * The full list Staan accepts is in the enum of
     * https://docs.staan.ai/api/search: fr-fr, de-de, en-us, en-gb, en-fr,
     * en-ca, en-au, en-in, en-ie, en-nz, en-za, en-sg. The docs page hides the
     * last four behind "show more".
     */
    public const REGIONS = [
        "de_DE" => "de-de",
        "de_AT" => "de-de",
        "de_CH" => "de-de",
        "fr_FR" => "fr-fr",
        "fr_CA" => "fr-fr",
        "fr_BE" => "fr-fr",
        "fr_CH" => "fr-fr",
        "en_US" => "en-us",
        "en_GB" => "en-gb",
        "en_CA" => "en-ca",
        "en_AU" => "en-au",
        "en_IN" => "en-in",
        "en_IE" => "en-ie",
        "en_NZ" => "en-nz",
        "en_ZA" => "en-za",
    ];

    const CONFIG_OVERLOAD = [
        'staan' => [
            'host' => 'api.staan.ai',
            'path' => '/v2/search/web',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'offset' => 0,
            ],
            'lang' => [
                'parameter' => 'market',
                'languages' => [],
                'regions' => self::REGIONS,
            ],
            // The API key comes from config/sumas.json as an
            // `Authorization: Bearer …` request header.
            'request-header' => [
                'Accept' => 'application/json',
            ],
            // Same weight as the other full web indexes (Brave, Google via
            // Serper, Mojeek). Staan serves Qwant and Ecosia from its own crawl
            // and fills gaps from a US fallback index, so for its three
            // languages it is a primary web index, not a niche source. Ten
            // results per page need no compensation: Result::rank() scores by
            // position, so Staan's 10th result scores what Brave's 10th does.
            'engine-boost' => 1.2,
            // Paid per request, so cache for the default hour (Brave and Serper
            // do the same).
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0.4,
            'infos' => [
                'homepage' => 'https://staan.ai/',
                'index_name' => 'Staan',
                'display_name' => 'Staan',
                'founded' => '2024',
                'headquarter' => '42 avenue de la Porte de Clichy, 75017 Paris, Frankreich',
                'operator' => 'European Search Perspective SAS',
                'index_size' => null,
            ],
        ],
    ];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);

        $this->configuration->disabledByDefault = true;
    }

    public function loadResults($resultstring)
    {
        $response = json_decode($resultstring);
        if (!is_object($response) || !isset($response->web->results) || !is_array($response->web->results)) {
            return;
        }

        try {
            foreach ($response->web->results as $result) {
                if (empty($result->url) || empty($result->title)) {
                    continue;
                }

                $additionalInformation = [];
                $date = $this->parseDate($result->published_date ?? null);
                if ($date !== null) {
                    $additionalInformation["date"] = $date;
                }

                $this->counter++;
                $newResult = new Result(
                    $this->configuration->engineBoost,
                    html_entity_decode($result->title),
                    $result->url,
                    $result->display_url ?? $result->url,
                    html_entity_decode($result->snippet ?? ""),
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    $additionalInformation
                );

                if (!empty($result->thumbnail)) {
                    $newResult->image = $result->thumbnail;
                }

                $this->results[] = $newResult;
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
        }
    }

    /**
     * The next page, if Staan may have one.
     *
     * Staan does not report a total, so a full page is taken to mean there is
     * more. The offset limit is the other bound: there is no fifth page.
     */
    public function getNext(\App\MetaGer $metager, $result)
    {
        $response = json_decode($result);
        if (!is_object($response) || !isset($response->web->results) || !is_array($response->web->results)) {
            return;
        }
        if (count($response->web->results) < self::PAGE_SIZE) {
            return;
        }

        $offset = (int) ($this->configuration->getParameter->offset ?? 0) + self::PAGE_SIZE;
        if ($offset > self::MAX_OFFSET) {
            return;
        }

        /** @var SearchengineConfiguration $newConfiguration */
        $newConfiguration = unserialize(serialize($this->configuration));
        $newConfiguration->getParameter->offset = $offset;
        $this->next = new static($this->name, $newConfiguration);
    }

    private function parseDate($value): ?Carbon
    {
        if (!is_string($value) || $value === "") {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
