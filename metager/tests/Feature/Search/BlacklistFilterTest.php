<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for the operator blacklists — the three text files
 * MetaGer reads from config/ to drop results the site will not show.
 *
 *   blacklistDomains.txt        one host per line; a result on that host is
 *                               dropped
 *   blacklistUrl.txt            one host+path per line; that page is dropped,
 *                               and a `page|query` line drops it for one query
 *   blacklistDescriptionUrl.txt one host+path per line; the result stays but
 *                               loses its description
 *
 * None of this was covered before, and all of it is about to move: today the
 * files are read and exploded in MetaGer::__construct on every single request,
 * and Result::isBlackListed then in_array()s the resulting flat arrays once per
 * result. Both costs grow with the length of the files.
 *
 * The files are not in the repository. In production they are subPath mounts of
 * keys in the `secrets` Secret (see chart/templates/_helpers.tpl), so a fresh
 * checkout has none and the filter is simply inert. These tests write them.
 *
 * Several assertions below pin behaviour that is inconsistent rather than
 * intended — the two lists disagree about trimming, and neither loads unless
 * both files are present. They are marked where they appear. Pinning them is
 * not the same as endorsing them; it is so that a later fix is a decision
 * someone makes on purpose, with a failing test naming what changed.
 */
class BlacklistFilterTest extends TestCase
{
    use FakesSearchEngines;

    /** @var array<string, string|null> path => contents to restore, null if it did not exist */
    private array $restore = [];

    protected function tearDown(): void
    {
        foreach ($this->restore as $path => $contents) {
            if ($contents === null) {
                @unlink($path);
            } else {
                file_put_contents($path, $contents);
            }
        }
        $this->restore = [];

        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Write one of the blacklist files for the duration of the test.
     *
     * A developer machine may have real ones; the previous contents are put
     * back in tearDown either way.
     */
    private function writeBlacklist(string $name, string $contents): void
    {
        $path = config_path($name);

        if (!array_key_exists($path, $this->restore)) {
            $this->restore[$path] = is_file($path) ? file_get_contents($path) : null;
        }

        file_put_contents($path, $contents);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function search(string $query = "kaffee"): array
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=" . urlencode($query) . "&focus=web&out=json");
        $response->assertOk();

        return $response->json("results");
    }

    /**
     * @param array<int, array<string, mixed>> $results
     * @return array<int, string>
     */
    private function links(array $results): array
    {
        return array_map(fn(array $r) => $r["link"], $results);
    }

    /**
     * The baseline every other test here is measured against. Without it, a
     * blacklist test passes just as well when the search returns nothing at all.
     */
    public function testWithoutAnyBlacklistFileEveryResultSurvives(): void
    {
        $links = $this->links($this->search());

        $this->assertContains("https://example.org/kaffee", $links);
        $this->assertContains("https://beispiel.de/sorten", $links);
    }

    public function testAHostOnTheDomainBlacklistIsDropped(): void
    {
        // Both files, because neither list loads unless both exist — see the
        // dedicated test below.
        $this->writeBlacklist("blacklistDomains.txt", "beispiel.de\n");
        $this->writeBlacklist("blacklistUrl.txt", "");

        $links = $this->links($this->search());

        $this->assertNotContains("https://beispiel.de/sorten", $links);
        $this->assertContains("https://example.org/kaffee", $links, "The whole search was dropped, not just the blacklisted host.");
    }

    /**
     * Entries are matched against the *stripped* host: scheme, `www.`, port,
     * query and fragment removed. So `https://beispiel.de/sorten` is blocked by
     * the line `beispiel.de` and by nothing more elaborate.
     */
    public function testTheDomainBlacklistMatchesTheStrippedHostAndNotTheFullUrl(): void
    {
        $this->writeBlacklist("blacklistDomains.txt", "https://beispiel.de/sorten\n");
        $this->writeBlacklist("blacklistUrl.txt", "");

        $this->assertContains("https://beispiel.de/sorten", $this->links($this->search()));
    }

    public function testAPageOnTheUrlBlacklistIsDropped(): void
    {
        $this->writeBlacklist("blacklistDomains.txt", "");
        $this->writeBlacklist("blacklistUrl.txt", "example.org/kaffee\n");

        $links = $this->links($this->search());

        $this->assertNotContains("https://example.org/kaffee", $links);
        $this->assertContains("https://example.org/espresso", $links, "The whole host was dropped; the url list is meant to block one page.");
    }

    /**
     * `<page>|<query>` blocks a page for one search term only. The query is
     * lowercased before the comparison, the page is not.
     *
     * Split over two methods rather than searching twice in one, which cannot
     * work: SearchSettings and QueryTimer are both container singletons and the
     * container is not rebuilt between two $this->get() calls, so the second
     * search would run with the first one's query — and QueryTimer would throw
     * on the repeated event name before it got that far.
     */
    public function testAQueryScopedUrlBlacklistEntryBlocksThatQuery(): void
    {
        $this->writeBlacklist("blacklistDomains.txt", "");
        $this->writeBlacklist("blacklistUrl.txt", "example.org/espresso|kaffee\n");

        $links = $this->links($this->search("kaffee"));

        $this->assertNotContains("https://example.org/espresso", $links);
        $this->assertContains("https://example.org/kaffee", $links, "Everything went; the entry is meant to block one page.");
    }

    public function testAQueryScopedUrlBlacklistEntryLeavesOtherQueriesAlone(): void
    {
        $this->writeBlacklist("blacklistDomains.txt", "");
        $this->writeBlacklist("blacklistUrl.txt", "example.org/espresso|kaffee\n");

        $this->assertContains("https://example.org/espresso", $this->links($this->search("tee")));
    }

    public function testACommentLineInTheUrlBlacklistIsNotAnEntry(): void
    {
        $this->writeBlacklist("blacklistDomains.txt", "");
        $this->writeBlacklist("blacklistUrl.txt", "# example.org/kaffee is fine now\n#example.org/espresso\n");

        $links = $this->links($this->search());

        $this->assertContains("https://example.org/kaffee", $links);
        $this->assertContains("https://example.org/espresso", $links);
    }

    /**
     * The two lists disagree, and this is the inconsistency, not a typo:
     * blacklistDomains.txt runs every line through trim(), blacklistUrl.txt
     * does not. So an indented domain blocks and an indented url silently does
     * nothing — which also means a file saved with CRLF line endings blocks
     * nothing at all through the url list.
     */
    public function testTheDomainListIsTrimmedAndTheUrlListIsNot(): void
    {
        $this->writeBlacklist("blacklistDomains.txt", "  beispiel.de  \n");
        $this->writeBlacklist("blacklistUrl.txt", "  example.org/kaffee  \n");

        $links = $this->links($this->search());

        $this->assertNotContains("https://beispiel.de/sorten", $links, "A padded domain no longer blocks — the domain list stopped being trimmed.");
        $this->assertContains("https://example.org/kaffee", $links, "A padded url now blocks. That is an improvement, but it is a change: say so on purpose.");
    }

    /**
     * Both files are behind one `&&`, so an operator who ships only a domain
     * blacklist gets no filtering whatsoever — including from the file they did
     * ship.
     */
    public function testNeitherListLoadsUnlessBothFilesExist(): void
    {
        $this->writeBlacklist("blacklistDomains.txt", "beispiel.de\n");

        $this->assertContains(
            "https://beispiel.de/sorten",
            $this->links($this->search()),
            "The domain list now loads on its own. That is the sensible behaviour, but it is a change in what a half-configured deployment does."
        );
    }

    /**
     * The third list is the odd one out: it does not drop the result, it blanks
     * the description and leaves the link in place.
     */
    public function testTheDescriptionBlacklistEmptiesTheDescriptionAndKeepsTheResult(): void
    {
        $this->writeBlacklist("blacklistDescriptionUrl.txt", "example.org/kaffee\n");

        $results = $this->search();

        $kaffee = array_values(array_filter($results, fn(array $r) => $r["link"] === "https://example.org/kaffee"));

        $this->assertCount(1, $kaffee, "The description blacklist dropped the result instead of blanking its description.");
        $this->assertSame("", $kaffee[0]["description"]);

        $others = array_values(array_filter($results, fn(array $r) => $r["link"] !== "https://example.org/kaffee"));
        $this->assertNotEmpty($others);
        $this->assertNotSame("", $others[0]["description"], "Every description was blanked, so this proves nothing about the list.");
    }

    /**
     * Unlike the other two, this one loads on its own.
     */
    public function testTheDescriptionBlacklistLoadsWithoutTheOtherTwoFiles(): void
    {
        $this->writeBlacklist("blacklistDescriptionUrl.txt", "example.org/kaffee\n");

        $this->assertFileDoesNotExist(config_path("blacklistDomains.txt"));

        $kaffee = array_values(array_filter($this->search(), fn(array $r) => $r["link"] === "https://example.org/kaffee"));
        $this->assertSame("", $kaffee[0]["description"]);
    }
}
