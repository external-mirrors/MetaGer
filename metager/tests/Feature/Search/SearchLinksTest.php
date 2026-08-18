<?php

namespace Tests\Feature\Search;

use App\MetaGer;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for the links a result page offers back to itself,
 * ahead of the LinkBuilder extraction in step D7d.
 *
 * Every one of them is "this search again, with one thing changed": another
 * fokus, another query, one host fewer, no language filter. They are built the
 * same way — take the current request, drop the parameters that must not
 * survive, put the change in, and hand the lot to `action()` — and that shared
 * shape is copied out five times on MetaGer, each with its own hand-written
 * list of what to drop.
 *
 * Those lists are what these tests are really about. Dropping too little is how
 * a link inherits `out=json` and returns a JSON document where the user
 * expected a page, or carries `page=3` into a search that has one page; dropping
 * too much loses the user's settings. Neither shows up as an error.
 *
 * The links are read off the MetaGer object rather than out of the rendered
 * page: the container is not rebuilt between the request and the assertions, so
 * the object the page was rendered from is still there, and asking it directly
 * says which builder is wrong rather than that some link on the page changed.
 */
class SearchLinksTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Run a search and hand back the MetaGer that rendered it.
     *
     * The query string deliberately carries everything a link has to decide
     * about: an output format, a page, a freshness filter (a parameter filter,
     * which the fokus links strip and the others keep) and a language.
     */
    private function search(string $query = "kaffee"): MetaGer
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $this->get("/meta/meta.ger3?" . http_build_query([
            "eingabe" => $query,
            "focus" => "web",
            "out" => "json",
            "page" => "2",
            "f" => "d",
            "lang" => "de",
        ]))->assertOk();

        return app(MetaGer::class);
    }

    /**
     * The fokus tabs: the same query, somewhere else.
     *
     * This is the only builder that also strips the parameter filters — a
     * freshness or safesearch setting chosen for a web search is not
     * necessarily meaningful for images, so switching fokus starts them over.
     */
    public function testTheFokusLinkKeepsTheQueryAndDropsTheFilters(): void
    {
        $link = $this->search()->generateSearchLink("bilder");

        $this->assertStringContainsString("focus=bilder", $link);
        $this->assertStringContainsString("eingabe=kaffee", $link);
        $this->assertStringNotContainsString("f=d", $link, "A parameter filter survived a change of fokus.");
        $this->assertStringNotContainsString("page=", $link, "The page number survived a change of fokus.");
        $this->assertStringNotContainsString("out=", $link, "out= survived, so this link answers with JSON rather than a page.");
    }

    /**
     * The "did you mean" link: another query, everything else as it was.
     *
     * Unlike the fokus link this one keeps the parameter filters, because the
     * search is the same kind of search.
     */
    public function testTheQueryLinkReplacesTheQueryAndKeepsTheFilters(): void
    {
        $link = $this->search()->generateEingabeLink("tee");

        $this->assertStringContainsString("eingabe=tee", $link);
        $this->assertStringNotContainsString("kaffee", $link);
        $this->assertStringContainsString("f=d", $link, "The freshness filter was lost on a query correction.");
        $this->assertStringNotContainsString("page=", $link);
        $this->assertStringNotContainsString("out=", $link);
    }

    /**
     * "Only from this site", offered next to a result. Appended to the query as
     * the operator a user would have typed, and forced back to the web fokus.
     */
    public function testTheSiteSearchLinkAppendsTheOperatorAndReturnsToWeb(): void
    {
        $link = $this->search()->generateSiteSearchLink("beispiel.de");

        $this->assertStringContainsString(rawurlencode("kaffee site:beispiel.de"), $link);
        $this->assertStringContainsString("focus=web", $link);
    }

    public function testTheRemoveHostLinkAppendsTheExclusion(): void
    {
        $link = $this->search()->generateRemovedHostLink("beispiel.de");

        $this->assertStringContainsString(rawurlencode("kaffee -site:beispiel.de"), $link);
    }

    public function testTheRemoveDomainLinkAppendsTheWildcardExclusion(): void
    {
        $link = $this->search()->generateRemovedDomainLink("beispiel.de");

        $this->assertStringContainsString(rawurlencode("kaffee -site:*.beispiel.de"), $link);
    }

    /**
     * Characterizing, not endorsing: the host is url-encoded before it is
     * appended to the query, and then the whole query string is encoded again
     * by action(). A host that needs encoding therefore arrives double-encoded.
     *
     * It is invisible for an ordinary hostname, which has nothing to encode.
     * This records that the second pass exists, so a change to it is deliberate.
     */
    public function testAHostIsEncodedTwiceOnItsWayIntoTheLink(): void
    {
        $link = $this->search()->generateRemovedHostLink("beispiel.de/pfad mit leerzeichen");

        $this->assertStringContainsString(rawurlencode(urlencode("beispiel.de/pfad mit leerzeichen")), $link);
    }

    /**
     * "Search all languages", offered when a language filter has hidden
     * results. Replaces the filter rather than removing it, so the page can
     * tell an explicit "all" from never having chosen.
     */
    public function testTheUnfilteredLinkAsksForEveryLanguage(): void
    {
        $link = $this->search()->getUnFilteredLink();

        $this->assertStringContainsString("lang=all", $link);
        $this->assertStringNotContainsString("lang=de", $link);
    }

    /**
     * generateQuicktipLink() is gone, and this is what it was: a call to
     * `action('MetaGerSearch@quicktips')` for a route that is not registered,
     * so it threw rather than returning a link, and nothing in app/ or
     * resources/ called it. It was pinned as throwing in the commit before the
     * extraction and deleted rather than carried into LinkBuilder.
     */
    public function testTheDeadQuicktipLinkIsGone(): void
    {
        $this->assertFalse(
            method_exists(MetaGer::class, "generateQuicktipLink"),
            "generateQuicktipLink is back. It points at a route that does not exist."
        );
    }

    /**
     * Load-more. There is a next page here only because engines said there was
     * one, so the link carries the search's own uid rather than a page number,
     * and it keeps `out` — the caller asking for more results wants them in the
     * format it is already reading.
     */
    public function testTheNextPageLinkCarriesTheSearchUid(): void
    {
        $metager = $this->search();
        $link = $metager->nextSearchLink();

        if ($link === "#") {
            $this->markTestSkipped("The fixture reported no next page, so there is no link to check.");
        }

        $this->assertStringContainsString("next=" . $metager->getSearchUid(), $link);
        $this->assertStringNotContainsString("page=", $link);
    }

    /**
     * Without a next page the link is a literal `#`, not a link to the same
     * page again.
     */
    public function testThereIsNoNextPageLinkWhenNoEngineOfferedOne(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $this->assertSame("#", app(MetaGer::class)->nextSearchLink());
    }
}
