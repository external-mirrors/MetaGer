<?php

namespace Tests\Feature\Search;

use App\Models\BlocklistEntry;
use App\Search\BlocklistNormalizer;
use Tests\Concerns\FakesSearchEngines;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * What the operator blocklist does to a real search, end to end.
 *
 * The list is the `blocklist_entries` table (App\Search\Blacklists). It used
 * to be three text files in config/, and this test began as the
 * characterization of those files before they moved. The tests whose names
 * say "now" are the ones that pinned a quirk of the files and were flipped,
 * on purpose, by the move:
 *
 *   - the url list was not trimmed, so a padded line matched nothing;
 *   - neither of the domain and url lists loaded unless both files existed;
 *   - a domain entry written with `www.` or as a full URL matched nothing;
 *   - a `page|query` line matched only the exact lowercased query, so a
 *     capitalised entry never matched and a reordered query was not caught.
 *
 * Comment lines went with the files: the importer skips them
 * (BlocklistImportFilesTest), and the table has no such thing.
 *
 * Entries are written the way the API writes them — normalised by
 * BlocklistEntry::normalizeValue / BlocklistNormalizer::terms — into an
 * in-memory database, never the developer's real one.
 */
class BlacklistFilterTest extends TestCase
{
    use FakesSearchEngines;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @param ?string $terms the query words, for url_terms
     */
    private function block(string $kind, string $value, ?string $terms = null): BlocklistEntry
    {
        return BlocklistEntry::create([
            "kind" => $kind,
            "value" => BlocklistEntry::normalizeValue($kind, $value),
            "terms" => $terms === null ? null : implode(" ", BlocklistNormalizer::terms($terms)),
            "reason" => "other",
        ]);
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
     * blocklist test passes just as well when the search returns nothing at all.
     */
    public function testWithoutAnyEntryEveryResultSurvives(): void
    {
        $links = $this->links($this->search());

        $this->assertContains("https://example.org/kaffee", $links);
        $this->assertContains("https://beispiel.de/sorten", $links);
    }

    /**
     * Also what replaced "neither list loads unless both files exist": a
     * domain entry is enough on its own.
     */
    public function testAHostOnTheDomainListIsDropped(): void
    {
        $this->block("domain", "beispiel.de");

        $links = $this->links($this->search());

        $this->assertNotContains("https://beispiel.de/sorten", $links);
        $this->assertContains("https://example.org/kaffee", $links, "The whole search was dropped, not just the blocked host.");
    }

    /**
     * Used to match nothing: the line was compared with the stripped host
     * as written. A domain entry is now normalised to the host it names.
     */
    public function testADomainEntryGivenAsAFullUrlNowBlocksItsHost(): void
    {
        $entry = $this->block("domain", "https://beispiel.de/sorten");

        $this->assertSame("beispiel.de", $entry->value);
        $this->assertNotContains("https://beispiel.de/sorten", $this->links($this->search()));
    }

    public function testADomainEntryWrittenWithWwwNowBlocks(): void
    {
        $this->block("domain", "www.Beispiel.de");

        $this->assertNotContains("https://beispiel.de/sorten", $this->links($this->search()));
    }

    public function testAPageOnTheUrlListIsDropped(): void
    {
        $this->block("url", "example.org/kaffee");

        $links = $this->links($this->search());

        $this->assertNotContains("https://example.org/kaffee", $links);
        $this->assertContains("https://example.org/espresso", $links, "The whole host was dropped; a url entry is meant to block one page.");
    }

    /**
     * Used to match nothing: the url file was not trimmed, so neither did an
     * indented line or any line of a file saved with CRLF endings.
     */
    public function testAPaddedUrlEntryNowBlocks(): void
    {
        $this->block("url", "  https://www.example.org/kaffee/  ");

        $this->assertNotContains("https://example.org/kaffee", $this->links($this->search()));
    }

    /**
     * Split over two methods rather than searching twice in one, which cannot
     * work: SearchSettings and QueryTimer are both container singletons and the
     * container is not rebuilt between two $this->get() calls.
     */
    public function testAUrlTermsEntryBlocksAQueryContainingItsTerms(): void
    {
        $this->block("url_terms", "example.org/espresso", "kaffee");

        $links = $this->links($this->search("kaffee"));

        $this->assertNotContains("https://example.org/espresso", $links);
        $this->assertContains("https://example.org/kaffee", $links, "Everything went; the entry is meant to block one page.");
    }

    public function testAUrlTermsEntryLeavesOtherQueriesAlone(): void
    {
        $this->block("url_terms", "example.org/espresso", "kaffee");

        $this->assertContains("https://example.org/espresso", $this->links($this->search("tee")));
    }

    public function testTheQueryIsCaseFoldedBeforeTheComparison(): void
    {
        $this->block("url_terms", "example.org/espresso", "kaffee");

        $this->assertNotContains("https://example.org/espresso", $this->links($this->search("KaFFee")));
    }

    /**
     * Used to never match: the query was lowercased and the entry was not.
     */
    public function testAUrlTermsEntryWrittenWithCapitalsNowMatches(): void
    {
        $this->block("url_terms", "example.org/espresso", "Kaffee");

        $this->assertNotContains("https://example.org/espresso", $this->links($this->search("Kaffee")));
    }

    /**
     * Used to need the exact query. Terms are a set now: every one of them
     * has to be among the query's words, in any order, among others.
     */
    public function testAUrlTermsEntryNowMatchesItsTermsInAnyOrderAmongOthers(): void
    {
        $this->block("url_terms", "example.org/espresso", "kaffee bohnen");

        $this->assertNotContains("https://example.org/espresso", $this->links($this->search("frische Bohnen, Kaffee")));
    }

    public function testAUrlTermsEntryNeedsEveryTerm(): void
    {
        $this->block("url_terms", "example.org/espresso", "kaffee bohnen");

        $this->assertContains("https://example.org/espresso", $this->links($this->search("kaffee")));
    }

    public function testUmlautsAndTheirSpelledOutFormAreOneTerm(): void
    {
        $this->block("url_terms", "example.org/espresso", "Kaffeemühle");

        $this->assertNotContains("https://example.org/espresso", $this->links($this->search("kaffeemuehle")));
    }

    /**
     * The description list does not drop the result, it blanks the
     * description and leaves the link in place.
     */
    public function testTheDescriptionListEmptiesTheDescriptionAndKeepsTheResult(): void
    {
        $this->block("description", "example.org/kaffee");

        $results = $this->search();

        $kaffee = array_values(array_filter($results, fn(array $r) => $r["link"] === "https://example.org/kaffee"));

        $this->assertCount(1, $kaffee, "The description list dropped the result instead of blanking its description.");
        $this->assertSame("", $kaffee[0]["description"]);

        $others = array_values(array_filter($results, fn(array $r) => $r["link"] !== "https://example.org/kaffee"));
        $this->assertNotEmpty($others);
        $this->assertNotSame("", $others[0]["description"], "Every description was blanked, so this proves nothing about the list.");
    }

    /**
     * Soft-deleted entries stay in the table as history and do nothing.
     */
    public function testADeletedEntryNoLongerBlocks(): void
    {
        $this->block("domain", "beispiel.de")->delete();

        $this->assertContains("https://beispiel.de/sorten", $this->links($this->search()));
    }

    /**
     * The point of moving the list into the database: an edit applies on the
     * next request, without a deploy or a restart.
     */
    public function testAnEntryAddedBetweenTwoSearchesAppliesToTheSecond(): void
    {
        $this->assertContains("https://beispiel.de/sorten", $this->links($this->search()));

        $this->block("domain", "beispiel.de");
        $this->forgetRequestScopedServices();
        $this->app->forgetInstance(\App\Search\Blacklists::class);

        $this->assertNotContains("https://beispiel.de/sorten", $this->links($this->search()));
    }
}
