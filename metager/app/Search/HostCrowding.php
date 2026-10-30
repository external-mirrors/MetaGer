<?php

namespace App\Search;

use App\Models\Result;

/**
 * No host takes more than two places in the result list.
 *
 * An engine can answer with most of its page from one site — the search that
 * prompted this got eleven of Brave's twenty results from one classifieds
 * site — and deduplication does not help, because they are eleven different
 * pages. Brave can group them itself (`cluster`, which becomes
 * inheritedResults), but only does so when it chooses to.
 *
 * ## A cap, not grouping
 *
 * Folding every result of a host under its first would reorder the list: a
 * host's twentieth-ranked page would appear under its second, and its
 * third-ranked page would disappear into a fold. So the first
 * {@see VISIBLE_PER_HOST} results of a host stay exactly where they ranked,
 * and only those after that leave the list. Every other result keeps its
 * relative order and moves up into the freed places, which is the point: more
 * different sites on one page.
 *
 * ## Where the rest go
 *
 * Under the host's last visible result, as `moreFromHost`, in the order they
 * ranked. The last rather than the first because they ranked below it, so it
 * is the least promotion that still leaves them one click away. The page shows
 * them as a closed <details>, which needs no JavaScript to open.
 *
 * ## What counts as one host
 *
 * Result::strippedHost, lower-cased: `www.` dropped, subdomains kept. Not the
 * registrable domain — `anna.github.io` and `bert.github.io` are two people's
 * sites — and the same normalisation the "search on this site" link uses, so
 * that link finds what the fold holds.
 *
 * Per page, not per search: page two of the same search starts counting again.
 * Carrying the counts across pages would need state that MetaGer does not keep
 * between them.
 */
class HostCrowding
{
    public const VISIBLE_PER_HOST = 2;

    /**
     * @param Result[] $results in ranked order, already deduplicated
     * @param bool $siteSearch whether the query restricts itself to one or more hosts with `site:`
     * @return Result[] at most VISIBLE_PER_HOST per host; the rest are on the host's last kept result
     */
    public function limit(array $results, string $fokus, bool $siteSearch = false): array
    {
        // An image search is a grid: there is nowhere in it to fold into, and
        // several pictures from one site are what an image search looks like.
        if ($fokus === "bilder") {
            return $results;
        }

        // `site:host` asks for that host's results and nothing else, and it is
        // where the fold's own link leads; capping it would leave two.
        if ($siteSearch) {
            return $results;
        }

        $shown = [];
        $lastShown = [];
        $kept = [];

        foreach ($results as $result) {
            $host = strtolower($result->strippedHost);
            $shown[$host] = ($shown[$host] ?? 0) + 1;

            if ($shown[$host] <= self::VISIBLE_PER_HOST) {
                $lastShown[$host] = $result;
                $kept[] = $result;
                continue;
            }

            $lastShown[$host]->moreFromHost[] = $result;
        }

        return $kept;
    }
}
