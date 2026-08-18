<?php

namespace App\Search;

use App\SearchSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Reads a search request: the operators inside the query, and the filter
 * parameters around it.
 *
 * Two entry points, called at different moments of the request and kept
 * together because they answer the same question — what is actually being
 * searched for:
 *
 *   {@see parse}           the query box → a {@see SearchQuery}
 *   {@see sanitizeFilters} the `fc`/`ff`/`ft` date range → a request an engine
 *                          may be handed
 *
 * Every method here is a pure function of what it is given. That is the point
 * of the class as much as the grouping is: the five searchCheck* methods it
 * replaces each read and rewrote `$this->q` in place, so what any one of them
 * did depended on the order the others had run in, and none of them could be
 * looked at on its own.
 *
 * ## Order still matters
 *
 * Not between the parser and its caller, but inside {@see parse}. `-site:host`
 * and `-site:*.domain` are told apart by their regexes alone, so the host
 * pattern (which refuses a leading `*`) has to run first; and the stopword pass
 * reads the query *after* the blacklists have taken their parts out of it, so a
 * host in `-site:beispiel.de` is not also read as a stopword. The sequence in
 * parse() is the one MetaGer::checkSpecialSearches used.
 */
class QueryParser
{
    /**
     * Words that make a query read as being about suicide or self-harm.
     *
     * Matched on word boundaries against the query with the operators already
     * removed, so `-einsamkeit` does not trigger it and `einsamkeitsforschung`
     * does not either.
     */
    private const PREVENTION_TRIGGERS = [
        "suizid",
        "selbstmord",
        "Selbstmordgedanken",
        "selbsttötung",
        "Freitod",
        "Sterbehilfe",
        "umbringen",
        "suizidale",
        "depressionen",
        "depressiv",
        "selbstverletzung",
        "einsam",
        "einsamkeit",
        "self harm",
        "self injury",
        "suicidal",
        "suicidality",
        "self-murder",
        "self-slaughter",
        "self-destruction",
        "self-homocide",
        "self-murderer",
        "kill oneself",
        "lonely",
        "depression",
    ];

    /**
     * How far back an engine will accept a custom date range.
     *
     * The rule came in for Bing, which MetaGer no longer queries; it now
     * applies to Brave, which documents no such limit. Kept as it was —
     * removing it is a question for whoever owns the engine configuration, not
     * for a refactor — and pinned in
     * tests/Feature/Search/QueryParsingTest::testADateOlderThanAYearIsPulledForwardToAYearAgo.
     */
    private const OLDEST_ALLOWED_RANGE = "1 year";

    public function parse(string $query, Request $request): SearchQuery
    {
        $warnings = [];
        $htmlWarnings = [];

        $phrases = $this->extractPhrases($query);
        if ($phrases !== []) {
            $warnings[] = trans("metaGer.formdata.phrase", [
                "phrase" => implode(", ", array_map(fn(string $phrase) => "\"$phrase\"", $phrases)),
            ]);
        }

        // The host pattern refuses a leading `*`, so it has to run before the
        // domain one — otherwise `-site:*.beispiel.de` would be read as a host
        // called `*.beispiel.de`.
        [$query, $hosts] = $this->extract("/(^|.*?\s)-site:([^\*\s]\S*)(\s.*|$)/si", $query);
        [$query, $domains] = $this->extract("/(^|.*?\s)-site:\*\.(\S+)(\s.*|$)/si", $query);
        [$query, $urls] = $this->extract("/(^|.*?\s)-url:(\S+)(\s.*|$)/si", $query);

        // A `blacklist=` parameter replaces the operators rather than adding to
        // them: submitting both means only the parameter is applied.
        if ($request->has("blacklist")) {
            $hosts = $this->blacklistParameter($request, wildcard: false);
            $domains = $this->blacklistParameter($request, wildcard: true);
        }

        $hosts = array_unique(array_merge($hosts, app(SearchSettings::class)->blacklist));
        $domains = array_unique(array_merge($domains, app(SearchSettings::class)->blacklist_tld));

        if ($urls !== []) {
            $warnings[] = trans("metaGer.formdata.urlBlacklist", ["url" => implode(", ", $urls)]);
        }

        // Reads the query the blacklists have already taken their parts out of,
        // so `-site:beispiel.de` is not also read as the stopword `site:…`.
        $withoutStopwords = $this->extractStopwords($query);

        if ($request->has("stop")) {
            // Same rule as the blacklist parameter — and it also puts the query
            // back as typed, operators and all, which is why the rewritten one
            // is discarded here rather than in extractStopwords.
            $stopWords = array_map("trim", explode(",", trim($request->input("stop"))));
        } else {
            [$query, $stopWords] = $withoutStopwords;
        }

        if ($stopWords !== []) {
            $warnings[] = trans("metaGer.formdata.stopwords", ["stopwords" => implode(", ", $stopWords)]);
        }

        if ($query === "") {
            $warnings[] = trans("metaGer.formdata.noSearch");
        }

        if ($this->readsAsSelfHarm($query)) {
            $htmlWarnings[] = trans("metaGer.prevention.phrase", [
                "prevurl" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "prevention"),
            ]);
        }

        return new SearchQuery(
            q: $query,
            phrases: $phrases,
            hostBlacklist: $hosts,
            domainBlacklist: $domains,
            urlBlacklist: $urls,
            stopWords: $stopWords,
            warnings: $warnings,
            htmlWarnings: $htmlWarnings,
        );
    }

    /**
     * Pull every occurrence of an operator out of the query.
     *
     * The patterns all have the same three-group shape — what came before, the
     * value, what comes after — so removing a match is putting the first and
     * third back together. Repeated until nothing matches, because an operator
     * may be used more than once.
     *
     * @return array{0: string, 1: list<string>} the query without them, and what was taken out
     */
    private function extract(string $pattern, string $query): array
    {
        $found = [];

        while (preg_match($pattern, $query, $match)) {
            $found[] = $match[2];
            $query = $match[1] . $match[3];
        }

        return [$query, $found];
    }

    /**
     * Quoted phrases, which are read out of the query but not removed from it —
     * an engine that understands quotes should still see them.
     *
     * @return list<string>
     */
    private function extractPhrases(string $query): array
    {
        [, $phrases] = $this->extract("/(^|.*?\s)\"(.+)\"(\s.*|$)/si", $query);

        return $phrases;
    }

    /**
     * `-wort`, anywhere outside a phrase.
     *
     * Quoted sections are cut out before the query is split into words, so a
     * hyphenated term inside `"…"` is left alone.
     *
     * @return array{0: string, 1: list<string>}
     */
    private function extractStopwords(string $query): array
    {
        [$outsidePhrases] = $this->extract("/(^|.*?\s)\"(.+)\"(\s.*|$)/si", $query);

        $stopWords = [];
        $remaining = $query;

        foreach (preg_split("/\s+/si", $outsidePhrases) as $word) {
            if (preg_match("/^-[a-zA-Z0-9]/", $word)) {
                $stopWords[] = substr($word, 1);
                $remaining = str_ireplace($word, "", $remaining);
            }
        }

        return [trim(preg_replace("/(\s)\s+/", "$1", $remaining)), $stopWords];
    }

    /**
     * The `blacklist=` parameter, which carries hosts and whole domains in one
     * comma-separated list and tells them apart by a leading `*.`.
     *
     * @param bool $wildcard true for the `*.`-prefixed entries, false for the rest
     * @return list<string>
     */
    private function blacklistParameter(Request $request, bool $wildcard): array
    {
        $submitted = trim($request->input("blacklist"));

        // A list without a comma is one entry, and is deliberately *not*
        // trimmed the way the entries of a list are — preserved from
        // MetaGer::searchCheckHostBlacklist.
        $entries = str_contains($submitted, ",")
            ? array_map("trim", explode(",", $submitted))
            : [$submitted];

        $found = [];
        foreach ($entries as $entry) {
            if ($wildcard && str_starts_with($entry, "*.")) {
                $found[] = substr($entry, 2);
            } elseif (!$wildcard && !str_starts_with($entry, "*")) {
                $found[] = $entry;
            }
        }

        return $found;
    }

    private function readsAsSelfHarm(string $query): bool
    {
        foreach (self::PREVENTION_TRIGGERS as $trigger) {
            if (preg_match("/\b" . preg_quote($trigger, "/") . "\b/i", $query)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Make the custom date range fit for an engine to be handed, or remove it.
     *
     * `fc=on` turns on a range given as `ff` (from) and `ft` (to), which reach
     * the engine through config/filters.json — `dyn-FreshnessCustomBrave` reads
     * both straight back off the request. So whatever survives this method is
     * what a search engine is asked for, and the request has to be left in a
     * state no engine can be confused by.
     *
     * Five rules, none of which the user is told about:
     *
     *   - a custom range and a quick freshness filter in one request: the quick
     *     one goes, so the two cannot contradict each other
     *   - a half-filled or unparseable range: the whole filter goes
     *   - a range running backwards: swapped, so the search still happens
     *   - a date in the future: pulled back to today
     *   - a date older than {@see OLDEST_ALLOWED_RANGE}: pulled forward
     *   - dates without `fc=on`: removed, so a later page cannot pick them up
     *
     * Note the request is rewritten in place, and that a copy of what was
     * removed lives on in the settings link, which is built from a URL snapshot
     * MetaGer takes before any of this runs.
     */
    public function sanitizeFilters(Request $request): Request
    {
        if ($request->filled("ff") && $request->filled("f")) {
            $request = $request->replace($request->except(["f"]));
        }

        if ($request->input("fc") !== "on") {
            if ($request->filled("ff") || $request->filled("ft")) {
                $request = $request->replace($request->except(["fc", "ff", "ft"]));
            }

            return $request;
        }

        if (!$request->filled("ff") || !$request->filled("ft")) {
            return $request->replace($request->except(["fc", "ff", "ft"]));
        }

        $pattern = "/^\d{4}-\d{2}-\d{2}$/";
        if (!preg_match($pattern, $request->input("ff")) || !preg_match($pattern, $request->input("ft"))) {
            return $request->replace($request->except(["fc", "ff", "ft"]));
        }

        $from = Carbon::createFromFormat("Y-m-d H:i:s", $request->input("ff") . " 00:00:00");
        $to = Carbon::createFromFormat("Y-m-d H:i:s", $request->input("ft") . " 00:00:00");
        $corrected = $this->correctRange($from, $to);

        if ($corrected === null) {
            return $request;
        }

        [$from, $to] = $corrected;

        return $request->replace(array_merge($request->all(), [
            "ff" => $from->format("Y-m-d"),
            "ft" => $to->format("Y-m-d"),
        ]));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null null when the range was already usable
     */
    private function correctRange(Carbon $from, Carbon $to): ?array
    {
        $changed = false;
        $now = Carbon::now();
        $oldest = Carbon::now()->sub(self::OLDEST_ALLOWED_RANGE);

        if ($from > $now) {
            $from = $now->copy();
            $changed = true;
        }
        if ($to > $now) {
            $to = $now->copy();
            $changed = true;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
            $changed = true;
        }

        if ($from < $oldest) {
            $from = $oldest->copy();
            $changed = true;
        }
        if ($to < $oldest) {
            $to = $oldest->copy();
            $changed = true;
        }

        return $changed ? [$from, $to] : null;
    }
}
