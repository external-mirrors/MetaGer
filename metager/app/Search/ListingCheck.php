<?php

namespace App\Search;

use App\MetaGer;
use App\Models\Authorization\Authorization;
use App\Models\Authorization\ListingCheckAuthorization;
use App\Models\Configuration\Searchengines;
use App\Models\Result;
use App\Models\Searchengine;
use App\QueryLogger;
use App\QueryTimer;
use App\SearchSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * Is this page listed on MetaGer for this query, and which engine returns it?
 * The work behind POST /api/blocklist/check (docs/blocklist-api.md).
 *
 * Every query × fokus is a real search through the same steps a result page
 * takes — MetaGer, SearchSettings and Searchengines built from a request,
 * QueryParser via checkSpecialSearches, EngineOrchestrator, ResultRanker,
 * MetaGer::prepareResults (deduplication, host crowding) — with two
 * differences:
 *
 *   - the operator blocklist is bypassed, on this request's Blacklists only,
 *     so the answer is what upstream returns and not what we already hide;
 *   - it waits for every engine, not only the fokus' main ones, because
 *     "which engines return it" is the question.
 *
 * ## Many searches in one request
 *
 * The search path keeps its state in request-scoped container singletons
 * (SearchSettings, Searchengines, MetaGer, Authorization) that read the
 * request through the facade. So each search gets a request of its own and
 * fresh instances of those, and they are swapped in around every step that
 * reads them — the same thing MetaGerSearch::loadMore does to rebuild a search
 * from the cache.
 *
 * The searches run in phases rather than one after the other: every mission
 * is queued first, then each search waits on what is left of one shared
 * budget. Fifteen searches waiting six seconds each would outlast FPM's
 * request_terminate_timeout (30 s in production); queued together, the
 * fetcher works on all of them at once and the whole check costs roughly one
 * search's wait.
 *
 * Only the first result page of each engine is searched, which the response
 * says (`scope: first_page`).
 */
class ListingCheck
{
    /** Seconds all searches of one check may wait for engines, together. */
    public const WAIT_BUDGET = 10.0;

    public function __construct(private Application $app) {}

    /**
     * @param list<string> $urls
     * @param list<string> $queries
     * @param list<string> $foki
     * @return array{results: list<array<string, mixed>>, engines_failed: list<array{fokus: string, name: string}>}
     */
    public function run(array $urls, array $queries, array $foki): array
    {
        $blacklists = $this->app->make(Blacklists::class);
        $original = $this->app->make("request");

        try {
            $searches = $blacklists->withoutBlocking(fn() => $this->search($queries, $foki));
        } finally {
            $this->bind($original, null);
        }

        $rows = [];
        $failed = [];
        foreach ($searches as $search) {
            foreach ($search["failed"] as $name) {
                $failed[$search["fokus"] . "\0" . $name] = ["fokus" => $search["fokus"], "name" => $name];
            }
            foreach ($urls as $url) {
                $rows[] = $this->row($blacklists, $url, $search);
            }
        }

        // Grouped by url, then query, then fokus, as a reader would scan it.
        $order = array_flip($urls);
        usort($rows, fn(array $a, array $b) => [$order[$a["_url"]], $a["_q"], $a["_f"]] <=> [$order[$b["_url"]], $b["_q"], $b["_f"]]);

        return [
            "results" => array_map(function (array $row) {
                unset($row["_url"], $row["_q"], $row["_f"]);
                return $row;
            }, $rows),
            "engines_failed" => array_values($failed),
        ];
    }

    /**
     * Run every search and reduce each to what the rows need.
     *
     * @return list<array{query: string, fokus: string, q: int, f: int, ranks: array<string, int>, engines: array<string, array<string, string>>, failed: list<string>}>
     */
    private function search(array $queries, array $foki): array
    {
        $searches = [];

        // Phase 1: build each search and queue its missions.
        foreach ($queries as $q => $query) {
            foreach ($foki as $f => $fokus) {
                $request = Request::create("/meta/meta.ger3", "GET", ["eingabe" => $query, "focus" => $fokus]);
                $this->bind($request, null);

                $state = [
                    "request" => $request,
                    "authorization" => new ListingCheckAuthorization(),
                ];
                $this->app->instance(Authorization::class, $state["authorization"]);

                $state["metager"] = $this->app->make(MetaGer::class);
                $state["settings"] = $this->app->make(SearchSettings::class);
                $state["metager"]->checkSpecialSearches($request);
                $state["engines"] = $this->app->make(Searchengines::class);

                $searchable = trim((string) $state["settings"]->q) !== ""
                    && $state["engines"]->getEnabledSearchengines() !== [];
                if ($searchable) {
                    $this->app->make(EngineOrchestrator::class)->start($state["metager"]);
                }

                $searches[] = $state + ["query" => $query, "fokus" => $fokus, "q" => $q, "f" => $f, "searchable" => $searchable];
            }
        }

        // Phase 2: wait, out of one budget for all of them.
        $deadline = microtime(true) + self::WAIT_BUDGET;
        foreach ($searches as $state) {
            if (!$state["searchable"]) {
                continue;
            }
            $this->bind($state["request"], $state);
            $left = $deadline - microtime(true);
            if ($left > 0) {
                $this->app->make(EngineOrchestrator::class)->waitForMainResults($state["metager"], everyEngine: true, budget: $left);
            }
        }

        // Phase 3: read the rest, rank, merge, and keep what the rows need.
        $outcomes = [];
        foreach ($searches as $state) {
            $outcome = ["query" => $state["query"], "fokus" => $state["fokus"], "q" => $state["q"], "f" => $state["f"], "ranks" => [], "engines" => [], "failed" => []];

            if ($state["searchable"]) {
                $this->bind($state["request"], $state);
                $this->app->make(EngineOrchestrator::class)->collectResults($state["metager"]);
                $this->app->make(ResultRanker::class)->rankEngineResults();
                $state["metager"]->prepareResults();

                $outcome["ranks"] = $this->ranks($state["metager"]->getResults());
                foreach ($state["engines"]->getEnabledSearchengines() as $engine) {
                    if (!$engine->loaded || $engine->failed) {
                        $outcome["failed"][] = $engine->name;
                        continue;
                    }
                    $valid = array_filter($engine->results, fn(Result $result) => $result->valid);
                    foreach ($this->links($valid) as $link) {
                        $outcome["engines"][$link][$engine->name] = $engine->getDisplayName();
                    }
                }
            }

            $outcomes[] = $outcome;
        }

        return $outcomes;
    }

    /**
     * Swap one search's request and request-scoped services into the
     * container, or — with null — drop them so the next resolve builds fresh
     * ones.
     */
    private function bind(Request $request, ?array $state): void
    {
        $this->app->instance("request", $request);
        Facade::clearResolvedInstance("request");

        foreach ([QueryTimer::class, QueryLogger::class, SearchSettings::class, Searchengines::class, MetaGer::class, Authorization::class] as $service) {
            $this->app->forgetInstance($service);
        }

        if ($state !== null) {
            $this->app->instance(Authorization::class, $state["authorization"]);
            $this->app->instance(SearchSettings::class, $state["settings"]);
            $this->app->instance(MetaGer::class, $state["metager"]);
            $this->app->instance(Searchengines::class, $state["engines"]);
        }
    }

    /**
     * 1-based position in the merged list, for every link that ended up in it.
     * A page folded under another — another copy in its cluster, or a page of
     * the same host crowded below it — counts at the position of the result
     * it is shown under: that is where a visitor finds it.
     *
     * @param Result[] $results
     * @return array<string, int> normalised link => rank
     */
    private function ranks(array $results): array
    {
        $ranks = [];
        foreach (array_values($results) as $index => $result) {
            foreach ($this->links([$result]) as $link) {
                $ranks[$link] ??= $index + 1;
            }
        }

        return $ranks;
    }

    /**
     * Normalised links of these results and of everything folded under them.
     *
     * @param Result[] $results
     * @return list<string>
     */
    private function links(array $results): array
    {
        $links = [];
        foreach ($results as $result) {
            $links[] = BlocklistNormalizer::strippedLink((string) $result->strippedLink);
            foreach (array_merge($result->inheritedResults ?? [], $result->moreFromHost ?? []) as $member) {
                $links[] = BlocklistNormalizer::strippedLink((string) $member->strippedLink);
            }
        }

        return array_values(array_unique($links));
    }

    /**
     * @param array{query: string, fokus: string, q: int, f: int, ranks: array<string, int>, engines: array<string, array<string, string>>, failed: list<string>} $search
     * @return array<string, mixed>
     */
    private function row(Blacklists $blacklists, string $url, array $search): array
    {
        $link = BlocklistNormalizer::link($url);
        $rank = $search["ranks"][$link] ?? null;
        $engines = $search["engines"][$link] ?? [];
        ksort($engines);

        $blocking = $blacklists->blockingEntryIds(BlocklistNormalizer::host($url), $link, $search["query"]);

        return [
            "url" => $link,
            "query" => $search["query"],
            "fokus" => $search["fokus"],
            "found" => $rank !== null || $engines !== [],
            "rank" => $rank,
            "engines" => array_map(
                fn(string $name, string $display) => ["name" => $name, "display_name" => $display],
                array_keys($engines),
                array_values($engines)
            ),
            "blocked_now" => $blocking !== [],
            "blocking_entry_ids" => $blocking,
            "_url" => $url,
            "_q" => $search["q"],
            "_f" => $search["f"],
        ];
    }
}
