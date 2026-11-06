<?php

namespace Tests\Feature\Search;

use App\Search\BlocklistNormalizer;
use Tests\Concerns\FakesSearchEngines;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\FakesBlocklist;
use Tests\TestCase;

/**
 * What the operator blocklist does to a real search, end to end.
 *
 * The list comes from the blocklist service (App\Search\Blacklists,
 * BlocklistSync). It used to be three text files in config/, and this test began as the
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
 * Comment lines went with the files: the blocklist service's import skips
 * them, and a snapshot has no such thing.
 *
 * Entries are put into Valkey the way `blocklist:sync` stores them, with the
 * values normalised as the blocklist service normalises them.
 */
#[Group('serial')]
class BlacklistFilterTest extends TestCase
{
    use FakesSearchEngines;
    use FakesBlocklist;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @param ?string $terms the query words, for url_terms
     */
    /**
     * An entry as the blocklist service would send it: the value normalised
     * the way the service normalises what suma-crm sends.
     */
    private function block(string $kind, string $value, ?string $terms = null): int
    {
        return $this->blocklistEntry(
            $kind,
            $kind === "domain" ? BlocklistNormalizer::host($value) : BlocklistNormalizer::link($value),
            $terms === null ? null : implode(" ", BlocklistNormalizer::terms($terms))
        );
    }

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
        $this->block("domain", "https://beispiel.de/sorten");

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
        $this->removeBlocklistEntry($this->block("domain", "beispiel.de"));

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
