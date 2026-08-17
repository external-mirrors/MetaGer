<?php

namespace App\Search;

use App\Models\Configuration\SearchEngineRegistry;
use Illuminate\Http\Request;

/**
 * The links a result page offers back to itself.
 *
 * Another fokus, another query, one host fewer, no language filter, the next
 * page — every one of them is this search again with one thing changed, and
 * every one is built the same way: take the request as it stands, drop the
 * parameters that must not survive, put the change in, hand the lot to
 * `action()`.
 *
 * The interesting part is what gets dropped, which is why it is named here
 * rather than written out per link. Three sets:
 *
 *   {@see NEVER_INHERITED}  paging and plumbing — `page`, `next`, `out` and the
 *                           form's own leftovers. A link that kept `out=json`
 *                           would answer a click with a JSON document; one that
 *                           kept `page` would open a page-three of a search
 *                           that has one page.
 *   the parameter filters   freshness, safesearch, image colour and so on.
 *                           Dropped only when the *fokus* changes, because a
 *                           filter chosen for web results need not exist for
 *                           images.
 *   `lang`                  replaced rather than removed by
 *                           {@see everyLanguage}, so the page can tell an
 *                           explicit "all languages" from never having chosen.
 *
 * Behaviour is pinned in tests/Feature/Search/SearchLinksTest, including the
 * double-encoding of a host on its way into a query.
 */
class LinkBuilder
{
    /**
     * Parameters no link inherits.
     *
     * `mgv` and `ua` are leftovers of retired authentication flows, kept in the
     * list because a stale one arriving in a bookmark should not be handed on.
     */
    private const NEVER_INHERITED = ["page", "next", "out", "submit-query", "mgv", "ua"];

    public function __construct(private readonly Request $request) {}

    /**
     * The same query in another fokus — the tabs above the results.
     *
     * The only link that also drops the parameter filters: they are declared
     * per fokus in config/filters.json, so carrying a web search's freshness
     * setting into an image search would apply a filter that fokus may not
     * have.
     */
    public function forFokus(string $fokus): string
    {
        $except = self::NEVER_INHERITED;
        foreach (app(SearchEngineRegistry::class)->filter->{"parameter-filter"} as $filter) {
            $except[] = $filter->{"get-parameter"};
        }

        $parameters = $this->request->except($except);
        $parameters["focus"] = $fokus;

        return $this->to($parameters);
    }

    /**
     * Another query, everything else as it was — the "did you mean" link.
     */
    public function forQuery(string $query): string
    {
        $parameters = $this->request->except([...self::NEVER_INHERITED, "eingabe"]);
        $parameters["eingabe"] = $query;

        return $this->to($parameters);
    }

    /**
     * "Only results from this site", offered next to a result. Written as the
     * operator a user would have typed, and forced back to the web fokus
     * because that is where a site search makes sense.
     */
    public function restrictedToHost(string $host): string
    {
        return $this->appendingToQuery(" site:" . urlencode($host), ["focus" => "web"]);
    }

    public function withoutHost(string $host): string
    {
        return $this->appendingToQuery(" -site:" . urlencode($host));
    }

    public function withoutDomain(string $domain): string
    {
        return $this->appendingToQuery(" -site:*." . urlencode($domain));
    }

    /**
     * Drop the language restriction — offered when it is what hid the results.
     *
     * Everything else is kept, `page` and `out` included: this is the same
     * search asked a second way, not a fresh one.
     */
    public function everyLanguage(): string
    {
        $parameters = $this->request->except(["lang"]);
        $parameters["lang"] = "all";

        return $this->to($parameters);
    }

    /**
     * The next page of the same search.
     *
     * Identified by the search's own uid rather than a page number, because
     * "more" here means the engines that have not answered yet rather than an
     * offset — and it keeps `out`, since whoever asks for more results wants
     * them in the format they are already reading.
     */
    public function nextPage(string $searchUid): string
    {
        $parameters = $this->request->except(["page", "out", "submit-query", "mgv"]);

        $out = $this->request->input("out", "");
        if ($out !== "results" && $out !== "") {
            $parameters["out"] = $out;
        }

        $parameters["next"] = $searchUid;

        return $this->to($parameters);
    }

    /**
     * Add an operator to the query the user typed.
     *
     * Note the value has already been url-encoded by the caller and will be
     * encoded again by the URL generator. Preserved from MetaGer, where each of
     * these three links did the same, and recorded in SearchLinksTest — a host
     * with nothing to encode, which is every ordinary hostname, is unaffected.
     *
     * @param array<string, string> $additional
     */
    private function appendingToQuery(string $operator, array $additional = []): string
    {
        $parameters = $this->request->except(self::NEVER_INHERITED);
        $parameters["eingabe"] = ($parameters["eingabe"] ?? "") . $operator;

        return $this->to(array_merge($parameters, $additional));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function to(array $parameters): string
    {
        return action("MetaGerSearch@search", $parameters);
    }
}
