<?php

namespace App\Search;

use App\Models\Configuration\Searchengines;
use App\Models\Result;

/**
 * The order the results come in.
 *
 * Two steps, run either side of the merge:
 *
 *   1. {@see rankEngineResults} — every engine scores the results it returned,
 *      while they are still grouped by engine and still carry that engine's
 *      boost
 *   2. {@see order} — the combined pile is sorted, best first
 *
 * They are separate because deduplication sits between them and needs the list
 * already ordered: the first copy of a page is the one that survives, so "first"
 * has to mean "highest ranked" rather than "whichever engine answered first".
 *
 * The arithmetic itself is not here. It is on Result::rank — source rank, a URL
 * match boost, a search-word boost, multiplied by the engine's boost — and it is
 * a tuning surface. What this class owns is the guarantee the page depends on:
 * that the list is in rank order at all.
 */
class ResultRanker
{
    /**
     * Score everything the engines returned.
     *
     * Delegated per engine rather than per result: an engine ranks its own
     * results because it is what knows its boost and its result count.
     */
    public function rankEngineResults(): void
    {
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            $engine->rank();
        }
    }

    /**
     * Highest rank first.
     *
     * @param Result[] $results
     * @return Result[]
     */
    public function order(array $results): array
    {
        // usort, not uasort: nothing downstream reads the keys, and every
        // consumer of this list indexes it from zero.
        usort($results, fn(Result $a, Result $b) => $b->getRank() <=> $a->getRank());

        return $results;
    }
}
