<?php

namespace Tests\Unit\Search;

use App\Search\Blacklists;
use Tests\TestCase;

/**
 * Unit tests for the blacklist lookup itself.
 *
 * tests/Feature/Search/BlacklistFilterTest already pins what a search does with
 * these files, end to end. These are the cases that are awkward to reach that
 * way — an exotic host, a repeated line, an edit between two requests — plus the
 * memoization, which by design is invisible from the outside except when it goes
 * wrong.
 *
 * Blacklists takes its directory as a constructor argument for exactly this: a
 * unit test gets a temporary one instead of config/.
 */
class BlacklistsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . "/blacklists-" . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . "/*") as $file) {
            unlink($file);
        }
        rmdir($this->dir);
        parent::tearDown();
    }

    private function write(string $name, string $contents): void
    {
        file_put_contents($this->dir . "/" . $name, $contents);
        // Same reason TestCase::setUp flushes: within one second, two files of
        // equal length look identical to the stat() the memo is keyed on.
        Blacklists::flush();
    }

    private function lists(): Blacklists
    {
        return new Blacklists($this->dir);
    }

    private function bothFiles(string $domains, string $urls): Blacklists
    {
        $this->write("blacklistDomains.txt", $domains);
        $this->write("blacklistUrl.txt", $urls);

        return $this->lists();
    }

    public function testWithNoFilesNothingIsBlocked(): void
    {
        $this->assertFalse($this->lists()->blocksResult("example.org", "example.org/page", "kaffee"));
        $this->assertFalse($this->lists()->blocksDescription("example.org/page"));
    }

    public function testAHostIsMatchedExactlyAndNotBySuffix(): void
    {
        $lists = $this->bothFiles("example.org\n", "");

        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "q"));
        $this->assertFalse($lists->blocksResult("notexample.org", "notexample.org/page", "q"));
        $this->assertFalse($lists->blocksResult("sub.example.org", "sub.example.org/page", "q"));
    }

    /**
     * This is the one behaviour that is deliberately different from before.
     *
     * The old check was in_array() without the strict flag, so two numeric
     * strings were compared as numbers: an entry of `1.20` blocked the host
     * `1.2`, and `0.0` blocked `0`. Hash lookups compare exactly.
     */
    public function testANumericLookingEntryIsNotComparedAsANumber(): void
    {
        $lists = $this->bothFiles("1.20\n", "");

        $this->assertTrue($lists->blocksResult("1.20", "1.20/page", "q"));
        $this->assertFalse($lists->blocksResult("1.2", "1.2/page", "q"), "1.20 blocked 1.2 — the comparison went back to being loose.");
    }

    public function testARepeatedLineIsHarmless(): void
    {
        $lists = $this->bothFiles("example.org\nexample.org\nexample.org\n", "");

        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "q"));
    }

    /**
     * An empty line becomes an empty entry, and every result whose host the url
     * parser could not read has an empty stripped host. The guard in front of
     * the lookups is what keeps a stray blank line from blocking all of them —
     * and a trailing newline means there is nearly always one.
     */
    public function testAResultWithNoReadableHostIsNeverBlocked(): void
    {
        $lists = $this->bothFiles("\n\n\n", "\n\n\n");

        $this->assertFalse($lists->blocksResult("", "", "q"));
        $this->assertFalse($lists->blocksResult("", "example.org/page", "q"));
    }

    public function testTheQueryIsMatchedCaseInsensitively(): void
    {
        $lists = $this->bothFiles("", "example.org/page|kaffee\n");

        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "KAFFEE"));
        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "Kaffee"));
        $this->assertFalse($lists->blocksResult("example.org", "example.org/page", "tee"));
    }

    /**
     * The url is not lowercased, only the query is. Worth pinning because the
     * two halves of the same line are treated differently.
     */
    public function testTheUrlHalfOfAScopedEntryIsCaseSensitive(): void
    {
        $lists = $this->bothFiles("", "example.org/Page|kaffee\n");

        $this->assertTrue($lists->blocksResult("example.org", "example.org/Page", "kaffee"));
        $this->assertFalse($lists->blocksResult("example.org", "example.org/page", "kaffee"));
    }

    public function testTheDescriptionListIsIndependentOfTheOtherTwo(): void
    {
        $this->write("blacklistDescriptionUrl.txt", "example.org/page\n");

        $lists = $this->lists();

        $this->assertTrue($lists->blocksDescription("example.org/page"));
        $this->assertFalse($lists->blocksResult("example.org", "example.org/page", "q"), "The description list started dropping results.");
    }

    /**
     * The point of the class: a file is parsed once per process, and every
     * later request in that process answers from the parsed copy.
     *
     * Shown by rewriting the file behind the memo's back — same length, same
     * mtime, different content — which is the one edit a stat() cannot see. The
     * old answer surviving is the memo working. Deleting the file would not
     * show it: the two lists are behind an is_file() gate that runs every time.
     */
    public function testAParsedFileIsNotReadAgain(): void
    {
        $lists = $this->bothFiles("aaa.example\n", "");
        $this->assertTrue($lists->blocksResult("aaa.example", "aaa.example/page", "q"));

        $path = $this->dir . "/blacklistDomains.txt";
        $stamp = filemtime($path);
        file_put_contents($path, "bbb.example\n");
        touch($path, $stamp);

        $this->assertTrue(
            $this->lists()->blocksResult("aaa.example", "aaa.example/page", "q"),
            "A new request read the file again — the memo no longer spans requests, and every one of them pays for parsing it."
        );
        $this->assertFalse($this->lists()->blocksResult("bbb.example", "bbb.example/page", "q"));
    }

    /**
     * ...but an edit is picked up by the next request, which is what makes
     * editing the file on a developer machine work without restarting php-fpm.
     *
     * A new instance, because that is what a request gets: the object is a
     * container singleton and the container is per request.
     */
    public function testAnEditToTheFileIsPickedUpByTheNextRequest(): void
    {
        $this->assertTrue($this->bothFiles("example.org\n", "")->blocksResult("example.org", "example.org/page", "q"));

        // A different length, so the change is visible to a stat() even within
        // the same second. Production files are read-only mounts that do not
        // change at all while the pod lives; see the note on Blacklists::$files.
        file_put_contents($this->dir . "/blacklistDomains.txt", "somewhere-else.example\n");

        $this->assertFalse(
            $this->lists()->blocksResult("example.org", "example.org/page", "q"),
            "A removed entry kept blocking; the memo is not noticing the file changed."
        );
    }

    /**
     * Within one request the answer does not change, however the file does.
     *
     * That is the reason the instance holds its own copy: the stat() then costs
     * one call per request rather than one per result — and half a result page
     * filtered by the old list and half by the new one would be worse than
     * either.
     */
    public function testOneRequestSeesOneVersionOfTheFile(): void
    {
        $lists = $this->bothFiles("example.org\n", "");
        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "q"));

        file_put_contents($this->dir . "/blacklistDomains.txt", "somewhere-else.example\n");

        $this->assertTrue($lists->blocksResult("example.org", "example.org/page", "q"));
    }

    public function testFlushForgetsEverything(): void
    {
        $this->assertTrue($this->bothFiles("example.org\n", "")->blocksResult("example.org", "example.org/page", "q"));

        unlink($this->dir . "/blacklistDomains.txt");
        Blacklists::flush();

        $this->assertFalse($this->lists()->blocksResult("example.org", "example.org/page", "q"));
    }
}
