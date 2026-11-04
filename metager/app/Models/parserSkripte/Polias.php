<?php

namespace app\Models\parserSkripte;

use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Carbon\Carbon;
use Log;

/**
 * Polias, a German web index run by Beyer Software. Documented at
 * https://polias.eu/schnittstelle.
 *
 * What shapes this class:
 *
 *   - **German only.** `lang=de` is the general index. The only other one,
 *     `lang=en`, covers developer documentation and IT, and Polias says it
 *     is not meant for general queries. So `lang` is fixed at `de` and every
 *     locale outside de-DE, de-AT and de-CH disables Polias with
 *     `INCOMPATIBLE_LOCALE`. The region becomes `land`, which prefers that
 *     country's pages without filtering to them.
 *   - **Ten results per call, at most ten pages.** `offset` stops at 90.
 *     Polias does not reject a higher one, it answers with results 91 to 100
 *     again, so [getNext] has to stop at 90 by itself.
 *   - **No `site:`, no date filter.** Neither is in filters.json for Polias,
 *     so a search using either leaves Polias out instead of sending a filter
 *     it would ignore.
 *   - **Billed per request, at €1 per 1,000.** That is €0.001 per request, and
 *     €0.002 at our usual 100% markup, so the cost is 0.2 Token. The engine is
 *     `disabledByDefault`, so a user has to opt in before a search pays for it.
 */
class Polias extends Searchengine
{
    /** The highest `offset` Polias serves a page for. */
    public const MAX_OFFSET = 90;

    /** Results per page, Polias' default and its maximum. */
    public const PAGE_SIZE = 10;

    /** MetaGer market -> Polias `land`. */
    public const REGIONS = [
        "de_DE" => "de",
        "de_AT" => "at",
        "de_CH" => "ch",
    ];

    /** `page_age` has no offset; Polias documents it as German local time. */
    private const PAGE_AGE_TIMEZONE = "Europe/Berlin";

    const CONFIG_OVERLOAD = [
        'polias' => [
            'host' => 'polias.eu',
            'path' => '/api/v1/search',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'lang' => 'de',
                'offset' => 0,
            ],
            'lang' => [
                'parameter' => 'land',
                'languages' => [],
                'regions' => self::REGIONS,
            ],
            // The API key comes from config/sumas.json as an
            // `Authorization: Bearer …` request header.
            'request-header' => [
                'Accept' => 'application/json',
            ],
            // Below Staan (1.1). Measured on 2026-11-04 across 20 German
            // queries: Polias' top 10 shared 1.5 URLs with Google's on average,
            // Brave's 6.0, and 15 of Polias' 60 top-3 results were in Google's
            // top 10. The results were relevant, with official sources ranked
            // well (verwaltung.bund.de, familienportal.de), but they differ
            // from the consensus much more than Staan's did, and navigational
            // queries were weaker ("ikea öffnungszeiten" put an aggregator
            // first). At 1.0 a user who opted in still sees Polias' results
            // between those of the larger indexes.
            'engine-boost' => 1.0,
            // Paid per request, so cache for the default hour (Brave, Serper
            // and Staan do the same). Polias allows storing results for 30 days.
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0.2,
            'infos' => [
                'homepage' => 'https://polias.eu/',
                'index_name' => 'Polias',
                'display_name' => 'Polias',
                'founded' => null,
                'headquarter' => 'Weiherhausstraße 10, 79618 Rheinfelden (Baden), Deutschland',
                'operator' => 'Beyer Software, Inhaber Moritz Beyer',
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
        if (!is_object($response) || !isset($response->results) || !is_array($response->results)) {
            return;
        }

        try {
            foreach ($response->results as $result) {
                if (empty($result->url) || empty($result->title)) {
                    continue;
                }

                $additionalInformation = [];
                $date = $this->parseDate($result->page_age ?? null);
                if ($date !== null) {
                    $additionalInformation["date"] = $date;
                }

                $this->counter++;
                $this->results[] = new Result(
                    $this->configuration->engineBoost,
                    html_entity_decode($result->title),
                    $result->url,
                    $result->url,
                    html_entity_decode($result->snippet ?? ""),
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    $additionalInformation
                );
            }
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
        }
    }

    /**
     * The next page, if Polias has one.
     *
     * A short page is the last one, so is a page that reaches `total`, and
     * there is no page after offset 90.
     */
    public function getNext(\App\MetaGer $metager, $result)
    {
        $response = json_decode($result);
        if (!is_object($response) || !isset($response->results) || !is_array($response->results)) {
            return;
        }
        if (count($response->results) < self::PAGE_SIZE) {
            return;
        }

        $offset = (int) ($this->configuration->getParameter->offset ?? 0) + self::PAGE_SIZE;
        if ($offset > self::MAX_OFFSET) {
            return;
        }
        if (isset($response->total) && is_numeric($response->total) && $offset >= (int) $response->total) {
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
            return Carbon::parse($value, self::PAGE_AGE_TIMEZONE);
        } catch (\Throwable) {
            return null;
        }
    }
}
