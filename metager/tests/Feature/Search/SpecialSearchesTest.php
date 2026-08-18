<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for the operators MetaGer understands in the query
 * itself: "phrases", -stopwords, -site:host, -site:*.domain and -url:fragment.
 *
 * These are a genuine feature — the syntax is documented on the help pages and
 * users type it — but the implementation is spread across five private methods
 * on MetaGer (searchCheckPhrase, searchCheckStopwords, searchCheckHostBlacklist,
 * searchCheckDomainBlacklist, searchCheckUrlBlacklist) that mutate $this->q as
 * they go, plus a filter pass in Result::isValid that runs much later. Step D7b
 * pulls all of it into a QueryParser, so what each operator does needs to be on
 * record first.
 *
 * The engine fixtures are chosen so that each operator has something to remove:
 * brave-web.json returns example.org/kaffee, beispiel.de/sorten and
 * example.org/espresso.
 *
 * One finding these tests exist to record, because it is invisible from the
 * page and easy to "tidy up" during D7b: **the operators are applied twice.**
 * checkSpecialSearches strips them out of MetaGer::$q, but the query actually
 * sent upstream is read from the SearchSettings singleton
 * (Searchengine::applySettings), which nothing strips — so Brave is asked for
 * `kaffee -site:beispiel.de` verbatim, and MetaGer filters the answer again
 * afterwards. The cleaned MetaGer::$q reaches exactly two consumers: the
 * quicktips lookup and the self-harm trigger check. Even ranking uses the raw
 * query, since Result::rank reads the settings too, and MetaGer::getQ() is
 * never called at all.
 *
 * For `-site:` and `-word` that double application is mostly harmless — the
 * major engines understand both natively, so the local filter is a backstop.
 * For `-url:`, which is MetaGer's own invention, the operator is passed to an
 * engine that can only read it as a search term.
 */
class SpecialSearchesTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>, 2: string}
     *   the results, the warnings, and the URL the fetcher was told to request
     */
    private function search(string $query): array
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=" . urlencode($query) . "&focus=web&out=json");
        $response->assertOk();

        $brave = collect($fetcher->missions())->firstWhere("name", "brave");

        return [$response->json("results"), $response->json("warnings"), $brave["url"] ?? ""];
    }

    /**
     * @param array<int, array<string, mixed>> $results
     */
    private function links(array $results): string
    {
        return collect($results)->pluck("link")->implode(" ");
    }

    public function testAStopwordRemovesMatchingResults(): void
    {
        [$results, $warnings, $url] = $this->search("kaffee -espresso");

        // "Espresso richtig zubereiten" matches in the title, so the result goes.
        $this->assertStringNotContainsString("example.org/espresso", $this->links($results));
        $this->assertStringContainsString("example.org/kaffee", $this->links($results));

        $this->assertNotEmpty($warnings, "A stopword search tells the user what it removed.");

        // The operator also goes upstream verbatim — see the class comment.
        // Harmless here, since `-word` means exclusion to Brave as well, so
        // both ends of the pipeline agree on what it does.
        $this->assertStringContainsString("kaffee", $url);
        $this->assertStringContainsString("-espresso", urldecode($url), "The stopword no longer reaches the engine. If it is now stripped upstream, MetaGer's local filter is the only thing enforcing it.");
    }

    /**
     * A stopword matches the description as well as the title, which is what
     * makes it a blunt instrument — this is characterization, not endorsement.
     */
    public function testAStopwordMatchesTheDescriptionToo(): void
    {
        // "Arabica" appears only in the description of beispiel.de/sorten.
        [$results] = $this->search("kaffee -Arabica");

        $this->assertStringNotContainsString("beispiel.de/sorten", $this->links($results));
    }

    public function testHostBlacklistRemovesResultsFromThatHost(): void
    {
        [$results, , $url] = $this->search("kaffee -site:beispiel.de");

        $this->assertStringNotContainsString("beispiel.de", $this->links($results));
        $this->assertStringContainsString("example.org", $this->links($results));

        // Sent upstream as well. `-site:` is Brave's own syntax, so the engine
        // is being asked to exclude the host too.
        $this->assertStringContainsString("-site:beispiel.de", urldecode($url));
    }

    public function testDomainBlacklistRemovesEveryHostUnderThatDomain(): void
    {
        [$results] = $this->search("kaffee -site:*.example.org");

        $this->assertStringNotContainsString("example.org", $this->links($results));
        $this->assertStringContainsString("beispiel.de", $this->links($results), "The domain blacklist removed more than the named domain.");
    }

    /**
     * -url: matches anywhere in the link, not just the host.
     *
     * Note the implementation detail this pins: Result::isValid tests it with
     * `strpos($link, $word)` and treats the result as truthy, so a match at
     * position 0 does not count. That cannot happen for a real link (which
     * starts with a scheme), but it is exactly the kind of thing that changes
     * meaning when the check is rewritten.
     */
    public function testUrlBlacklistRemovesResultsWhoseLinkContainsTheFragment(): void
    {
        [$results, $warnings] = $this->search("kaffee -url:espresso");

        $this->assertStringNotContainsString("espresso", $this->links($results));
        $this->assertNotEmpty($warnings);
    }

    /**
     * `-url:` is MetaGer's own syntax and means nothing to a search engine, but
     * it is still sent to one.
     *
     * Recorded as its own test because this is the case where passing the
     * operator through actually costs something: Brave is asked for
     * "kaffee -url:espresso" and reads `-url:espresso` as a term to exclude, so
     * the operator silently narrows the upstream result set as well as
     * filtering it locally. The other operators at least mean the same thing at
     * both ends.
     */
    public function testTheUrlOperatorIsSentToTheEngineThatCannotUnderstandIt(): void
    {
        [, , $url] = $this->search("kaffee -url:espresso");

        $this->assertStringContainsString(
            "-url:espresso",
            urldecode($url),
            "MetaGer's own -url: operator no longer reaches the engine — which would be an improvement worth describing here."
        );
    }

    /**
     * A phrase search warns the user and leaves the phrase in the query, so the
     * engines do the phrase matching.
     *
     * MetaGer itself does *not* filter by phrase: the block in Result::isValid
     * that would drop non-matching results is commented out. This test records
     * that — a result which does not contain the phrase still reaches the page.
     *
     * Worth knowing before D7b: the warning says "You are doing a string
     * search", which the user reasonably reads as a promise MetaGer keeps,
     * while it is actually a promise passed on to whichever engines answered.
     */
    public function testAPhraseSearchWarnsAndIsPassedToTheEngineRatherThanFilteredLocally(): void
    {
        [$results, $warnings, $url] = $this->search('kaffee "kalt gebrüht"');

        $this->assertNotEmpty($warnings, "A phrase search tells the user it is happening.");

        // The phrase survives into the upstream query...
        $this->assertStringContainsString("kalt", urldecode($url));

        // ...and nothing is filtered out locally, although no fixture result
        // contains the phrase.
        $this->assertNotEmpty($results, "Results were filtered by phrase locally. If the filter in Result::isValid was switched back on, that is a behaviour change worth its own note.");
    }

    /**
     * Several operators in one query all apply.
     */
    public function testOperatorsCombine(): void
    {
        [$results, , $url] = $this->search("kaffee -espresso -site:beispiel.de");

        $links = $this->links($results);
        $this->assertStringNotContainsString("espresso", $links);
        $this->assertStringNotContainsString("beispiel.de", $links);
        $this->assertStringContainsString("example.org/kaffee", $links);

        $this->assertStringContainsString("kaffee", $url);
    }

    /**
     * A query consisting of nothing but operators leaves an empty search, which
     * is a warning rather than an error or a redirect.
     */
    public function testAQueryOfNothingButOperatorsWarnsInsteadOfSearching(): void
    {
        [, $warnings] = $this->search("-site:example.org");

        $this->assertNotEmpty($warnings, "An empty query after operator parsing must tell the user, not silently return nothing.");
    }
}
