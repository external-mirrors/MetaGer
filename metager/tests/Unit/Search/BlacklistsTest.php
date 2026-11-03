<?php

namespace Tests\Unit\Search;

use App\Models\BlocklistEntry;
use App\Search\Blacklists;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * The blocklist lookup itself.
 *
 * tests/Feature/Search/BlacklistFilterTest pins what a search does with the
 * entries, end to end, and BlocklistSnapshotTest how the compiled list is kept
 * and what it falls back to. These are the cases that are awkward to reach
 * through a search — an exotic host, a repeated entry, a result without a host.
 *
 * Rows are written straight into the table here, already normalised, so these
 * are about the lookup and not about what the API makes of its input.
 */
class BlacklistsTest extends TestCase
{
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    private function entry(string $kind, string $value, ?string $terms = null): void
    {
        BlocklistEntry::create(["kind" => $kind, "value" => $value, "terms" => $terms, "reason" => "other"]);
    }

    private function lists(): Blacklists
    {
        Blacklists::flush();

        return new Blacklists();
    }

    public function testWithNoEntriesNothingIsBlocked(): void
    {
        $this->assertFalse($this->lists()->blocksResult("example.org", "example.org/page", "kaffee"));
        $this->assertFalse($this->lists()->blocksDescription("example.org/page"));
    }

    public function testAHostIsMatchedExactlyAndNotBySuffix(): void
    {
        $this->entry("domain", "example.org");
        $lists = $this->lists();

        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "q"));
        $this->assertFalse($lists->blocksResult("notexample.org", "notexample.org/page", "q"));
        $this->assertFalse($lists->blocksResult("sub.example.org", "sub.example.org/page", "q"));
    }

    /**
     * Results keep the host's case as the engine sent it; entries are stored
     * lowercased. Both sides are folded before the lookup.
     */
    public function testTheResultsHostIsCaseFolded(): void
    {
        $this->entry("domain", "example.org");

        $this->assertTrue($this->lists()->blocksResult("Example.ORG", "Example.ORG/page", "q"));
    }

    /**
     * Long before the database: the check was once in_array() without the
     * strict flag, so `1.20` blocked the host `1.2`. Hash lookups compare
     * exactly, and keep doing so.
     */
    public function testANumericLookingEntryIsNotComparedAsANumber(): void
    {
        $this->entry("domain", "1.20");
        $lists = $this->lists();

        $this->assertTrue($lists->blocksResult("1.20", "1.20/page", "q"));
        $this->assertFalse($lists->blocksResult("1.2", "1.2/page", "q"), "1.20 blocked 1.2 — the comparison went back to being loose.");
    }

    public function testARepeatedEntryIsHarmless(): void
    {
        $this->entry("domain", "example.org");
        $this->entry("domain", "example.org");

        $lists = $this->lists();
        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "q"));
        $this->assertCount(2, $lists->blockingEntryIds("example.org", "example.org/page", "q"));
    }

    /**
     * Every result whose host the url parser could not read has an empty
     * stripped host. The guard in front of the lookups is what keeps a stray
     * empty value from blocking all of them.
     */
    public function testAResultWithNoReadableHostIsNeverBlocked(): void
    {
        $this->entry("domain", "");
        $this->entry("url", "");
        $lists = $this->lists();

        $this->assertFalse($lists->blocksResult("", "", "q"));
        $this->assertFalse($lists->blocksResult("", "example.org/page", "q"));
    }

    public function testTheQueryIsMatchedCaseInsensitively(): void
    {
        $this->entry("url_terms", "example.org/page", "kaffee");
        $lists = $this->lists();

        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "KAFFEE"));
        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "Kaffee"));
        $this->assertFalse($lists->blocksResult("example.org", "example.org/page", "tee"));
    }

    /**
     * Paths are case-sensitive on the web, so the path is not folded — only
     * the host is.
     */
    public function testThePathIsCaseSensitive(): void
    {
        $this->entry("url", "example.org/Page");
        $lists = $this->lists();

        $this->assertTrue($lists->blocksResult("example.org", "example.org/Page", "q"));
        $this->assertFalse($lists->blocksResult("example.org", "example.org/page", "q"));
    }

    public function testATrailingSlashOnTheResultDoesNotMatter(): void
    {
        $this->entry("url", "example.org/page");

        $this->assertTrue($this->lists()->blocksResult("example.org", "example.org/page/", "q"));
    }

    public function testAUrlEntryDoesNotCoverThePagesBelowIt(): void
    {
        $this->entry("url", "example.org/page");

        $this->assertFalse($this->lists()->blocksResult("example.org", "example.org/page/sub", "q"));
    }

    public function testAUrlTermsEntryWithoutTermsBlocksNothing(): void
    {
        $this->entry("url_terms", "example.org/page", "");

        $this->assertFalse($this->lists()->blocksResult("example.org", "example.org/page", "q"));
    }

    public function testTheDescriptionListIsIndependentOfTheOthers(): void
    {
        $this->entry("description", "example.org/page");
        $lists = $this->lists();

        $this->assertTrue($lists->blocksDescription("example.org/page"));
        $this->assertFalse($lists->blocksResult("example.org", "example.org/page", "q"), "The description list started dropping results.");
    }

    public function testBlockingEntryIdsNamesEveryEntryThatApplies(): void
    {
        $this->entry("domain", "example.org");
        $this->entry("url", "example.org/page");
        $this->entry("url_terms", "example.org/page", "max mustermann");
        $this->entry("url_terms", "example.org/page", "erika");

        $ids = $this->lists()->blockingEntryIds("example.org", "example.org/page", "Mustermann Max Anwalt");

        $this->assertSame([1, 2, 3], $ids);
    }
}
