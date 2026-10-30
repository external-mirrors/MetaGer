<?php

namespace Tests\Unit\Search;

use App\Models\Result;
use App\Search\HostCrowding;
use Tests\TestCase;

/**
 * HostCrowding in isolation: at most two results per host stay in the list, in
 * the place they ranked, and the rest are folded under the second.
 *
 * Most tests here switch the page minimum off (`$minimumVisible = 0`), so a
 * handful of links is enough to show a rule; the minimum has tests of its own.
 *
 * The end-to-end version, on a fixture shaped like the search that prompted
 * this, is tests/Feature/Search/HostCrowdingTest.
 *
 * Extends Tests\TestCase rather than PHPUnit's: Result's constructor signs a
 * proxy link with a config value.
 */
class HostCrowdingTest extends TestCase
{
    private function hit(string $link): Result
    {
        return new Result(
            1,
            "Treffer",
            $link,
            $link,
            "Eine Beschreibung, die lang genug ist um nicht gekürzt zu werden.",
            "brave",
            "https://brave.example/",
            1
        );
    }

    /**
     * @param string[] $links
     * @return Result[]
     */
    private function crowd(array $links, string $fokus = "web", bool $siteSearch = false, int $minimumVisible = 0): array
    {
        return (new HostCrowding($minimumVisible))->limit(array_map(fn(string $link) => $this->hit($link), $links), $fokus, $siteSearch);
    }

    /**
     * @param Result[] $results
     * @return string[]
     */
    private function links(array $results): array
    {
        return array_map(fn(Result $result) => $result->link, $results);
    }

    public function testTwoResultsFromOneHostAreLeftAlone(): void
    {
        $results = $this->crowd([
            "https://a.example/1",
            "https://b.example/1",
            "https://a.example/2",
        ]);

        $this->assertSame(
            ["https://a.example/1", "https://b.example/1", "https://a.example/2"],
            $this->links($results)
        );
        $this->assertSame([], $results[2]->moreFromHost);
    }

    /**
     * The point of a cap rather than grouping: the two results of a host that
     * do show stay where they ranked, and so does everything around them. Only
     * the third and later results of a host leave the list, and the results
     * of other hosts close up behind them in their own order.
     */
    public function testTheThirdResultOfAHostLeavesTheListAndTheRestKeepTheirOrder(): void
    {
        $results = $this->crowd([
            "https://a.example/1",
            "https://a.example/2",
            "https://b.example/1",
            "https://a.example/3",
            "https://c.example/1",
            "https://a.example/4",
            "https://b.example/2",
        ]);

        $this->assertSame([
            "https://a.example/1",
            "https://a.example/2",
            "https://b.example/1",
            "https://c.example/1",
            "https://b.example/2",
        ], $this->links($results));
    }

    /**
     * Under the second, not the first: the folded results ranked below the
     * second one, so that is the least promotion they can get while still
     * being reachable from the list. And in the order they ranked.
     */
    public function testTheFoldedResultsHangUnderTheHostsSecondResultInRankOrder(): void
    {
        $results = $this->crowd([
            "https://a.example/1",
            "https://b.example/1",
            "https://a.example/2",
            "https://a.example/3",
            "https://a.example/4",
        ]);

        $this->assertSame([], $results[0]->moreFromHost);
        $this->assertSame(
            ["https://a.example/3", "https://a.example/4"],
            $this->links($results[2]->moreFromHost)
        );
    }

    /**
     * Result::strippedHost drops `www.`, the same normalisation the
     * "search this host" and "hide this host" links use — so the fold's
     * site: link finds exactly the results it folded.
     */
    public function testWwwAndTheBareHostAreOneHost(): void
    {
        $results = $this->crowd([
            "https://www.a.example/1",
            "https://a.example/2",
            "https://www.a.example/3",
        ]);

        $this->assertCount(2, $results);
        $this->assertSame(["https://www.a.example/3"], $this->links($results[1]->moreFromHost));
    }

    /**
     * By host, not by registrable domain. Two subdomains of a hosting
     * platform — `*.github.io`, `*.blogspot.com` — are two different sites,
     * and folding one under the other would credit one author with another's
     * page.
     */
    public function testSubdomainsAreDifferentHosts(): void
    {
        $results = $this->crowd([
            "https://anna.blog.example/1",
            "https://bert.blog.example/1",
            "https://carla.blog.example/1",
        ]);

        $this->assertCount(3, $results);
    }

    /**
     * Upper and lower case are one host; DNS does not tell them apart.
     */
    public function testHostsAreComparedWithoutCase(): void
    {
        $results = $this->crowd([
            "https://A.example/1",
            "https://a.example/2",
            "https://a.EXAMPLE/3",
        ]);

        $this->assertCount(2, $results);
    }

    /**
     * An image search is a grid of pictures, and the grid has nowhere to
     * fold anything into. Several pictures from one site are also simply what
     * an image search looks like.
     */
    public function testAnImageSearchIsNotCrowded(): void
    {
        $results = $this->crowd([
            "https://a.example/1",
            "https://a.example/2",
            "https://a.example/3",
        ], "bilder");

        $this->assertCount(3, $results);
    }

    /**
     * `site:a.example` asks for that host and nothing else. Capping it would
     * leave two results — and the fold links to exactly this search. Whether
     * the query is a site search is MetaGer's to say (it reads the query
     * filters); tests/Feature/Search/HostCrowdingTest covers that wiring.
     */
    public function testASiteSearchIsNotCrowded(): void
    {
        $results = $this->crowd(["https://a.example/1", "https://a.example/2", "https://a.example/3"], "web", true);

        $this->assertCount(3, $results);
    }

    /**
     * The page minimum is a production default, not something each test sets:
     * a page of five results, three of them from one host, folds nothing.
     */
    public function testByDefaultAShortPageFoldsNothing(): void
    {
        $results = (new HostCrowding())->limit(array_map(fn(string $link) => $this->hit($link), [
            "https://a.example/1",
            "https://a.example/2",
            "https://a.example/3",
            "https://b.example/1",
            "https://c.example/1",
        ]), "web");

        $this->assertCount(5, $results);
    }

    /**
     * A page dominated by a few hosts must not shrink to a handful of visible
     * results. The best-ranked folded results go back into the list, in the
     * place they ranked, until the minimum is met; the rest stay folded.
     */
    public function testTheBestRankedFoldedResultsReturnUntilThePageHasItsMinimum(): void
    {
        $results = $this->crowd([
            "https://a.example/1",
            "https://a.example/2",
            "https://b.example/1",
            "https://b.example/2",
            "https://a.example/3",
            "https://b.example/3",
            "https://a.example/4",
            "https://b.example/4",
        ], minimumVisible: 6);

        $this->assertSame([
            "https://a.example/1",
            "https://a.example/2",
            "https://b.example/1",
            "https://b.example/2",
            "https://a.example/3",
            "https://b.example/3",
        ], $this->links($results));
        $this->assertSame(["https://a.example/4"], $this->links($results[4]->moreFromHost));
        $this->assertSame(["https://b.example/4"], $this->links($results[5]->moreFromHost));
    }

    /**
     * Released in rank order across the page, not a fair share per host: a's
     * third result outranks b's third, so a gets a third place and b does not.
     * a's fold then hangs under that third place, its last visible result.
     */
    public function testReleasedResultsAreChosenByRankAcrossHosts(): void
    {
        $results = $this->crowd([
            "https://a.example/1",
            "https://a.example/2",
            "https://a.example/3",
            "https://b.example/1",
            "https://b.example/2",
            "https://b.example/3",
            "https://a.example/4",
        ], minimumVisible: 5);

        $this->assertSame([
            "https://a.example/1",
            "https://a.example/2",
            "https://a.example/3",
            "https://b.example/1",
            "https://b.example/2",
        ], $this->links($results));
        $this->assertSame(["https://a.example/4"], $this->links($results[2]->moreFromHost));
        $this->assertSame(["https://b.example/3"], $this->links($results[4]->moreFromHost));
    }
}
