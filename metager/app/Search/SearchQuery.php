<?php

namespace App\Search;

/**
 * What the user asked for, once the operators have been read out of it.
 *
 * MetaGer's query syntax lets a search carry instructions as well as words:
 *
 *   "eine phrase"      keep these words together
 *   -wort              drop results mentioning this
 *   -site:host.de      drop results from this host
 *   -site:*.domain.de  drop results from this domain and everything under it
 *   -url:fragment      drop results whose URL contains this
 *
 * Those instructions are stripped out of the query, so {@see $q} is the words
 * that are left and the other fields are what was taken out. The warnings are
 * the "you searched for X, we removed Y" lines above the results, and
 * {@see $htmlWarnings} the ones carrying markup — today only the link to the
 * prevention page.
 *
 * A plain readonly carrier, deliberately. The parsing is in
 * {@see QueryParser}, the filtering is in Result::isValid, and what this class
 * does is stop the answer travelling as eight loose properties on a 1300-line
 * object.
 *
 * ## One thing this object is not
 *
 * It is not what gets sent to the search engines. Those read the query from the
 * SearchSettings singleton, which nothing strips — so an engine is asked for
 * `kaffee -site:beispiel.de` verbatim and MetaGer filters the answer again
 * afterwards. See tests/Feature/Search/SpecialSearchesTest, which records that
 * double application and its one visible consequence: `-url:`, which is
 * MetaGer's own invention, reaches engines that can only read it as a word.
 */
final class SearchQuery
{
    /**
     * @param string $q the query with every operator removed
     * @param list<string> $phrases quoted phrases, without their quotes
     * @param list<string> $hostBlacklist hosts not to show results from
     * @param list<string> $domainBlacklist domains not to show results from
     * @param list<string> $urlBlacklist url fragments not to show results for
     * @param list<string> $stopWords words a result may not mention
     * @param list<string> $warnings plain-text notices to show above the results
     * @param list<string> $htmlWarnings notices that carry markup
     */
    public function __construct(
        public readonly string $q,
        public readonly array $phrases = [],
        public readonly array $hostBlacklist = [],
        public readonly array $domainBlacklist = [],
        public readonly array $urlBlacklist = [],
        public readonly array $stopWords = [],
        public readonly array $warnings = [],
        public readonly array $htmlWarnings = [],
    ) {}
}
