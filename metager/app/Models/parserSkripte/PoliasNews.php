<?php

namespace app\Models\parserSkripte;

/**
 * Polias' news search: articles from the last 30 days, through the same
 * endpoint, key and billing as the web search, with `result_filter=news`.
 * Paging, dates and parsing are [Polias]'; what differs is below.
 *
 *   - **The results sit under `news`**, as `news.results` and `news.total`.
 *   - **Six languages, chosen by `lang`.** de covers Germany, Austria and
 *     Switzerland, en mostly British, American and Irish outlets, and pl,
 *     it, nl and sv were announced by Polias for news only. French, Danish
 *     and Finnish also answered on 2026-11-25 but were not announced, and
 *     Spanish and Portuguese returned three and five articles, so all of them
 *     stay out. `land` changes nothing here, so a market only picks the
 *     language.
 *   - **No date filter**, although it would be the obvious one for news.
 *     `freshness` is ignored, and every article is from the last 30 days
 *     anyway. A search with a date filter leaves it out.
 */
class PoliasNews extends Polias
{
    /** MetaGer language -> Polias `lang`. */
    public const LANGUAGES = [
        "de" => "de",
        "en" => "en",
        "it" => "it",
        "nl" => "nl",
        "pl" => "pl",
        "sv" => "sv",
    ];

    const CONFIG_OVERLOAD = [
        'polias_news' => [
            'host' => 'polias.eu',
            'path' => '/api/v1/search',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'result_filter' => 'news',
                'offset' => 0,
            ],
            'lang' => [
                'parameter' => 'lang',
                'languages' => self::LANGUAGES,
                'regions' => [],
            ],
            // The web search's key works for news as well.
            'secrets-from' => 'polias',
            'request-header' => [
                'Accept' => 'application/json',
            ],
            // Below Brave News (1.2), as on the web. Polias' news is current
            // and from established outlets, but its snippets are often empty
            // or a list of the article's tags.
            'engine-boost' => 1.0,
            'cache-duration' => -1,
            'disabled' => false,
            'filter-opt-in' => false,
            // The same €1 per 1,000 requests as the web search.
            'cost' => 0.2,
            'infos' => parent::CONFIG_OVERLOAD['polias']['infos'],
        ],
    ];

    protected function page(string $body): ?object
    {
        $response = json_decode($body);
        return self::pageIn(is_object($response) ? ($response->news ?? null) : null);
    }
}
