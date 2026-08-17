<?php

namespace Tests\Unit;

use App\SearchSettings;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SearchSettings::parseBlacklistCookie().
 *
 * Deliberately extends PHPUnit's TestCase rather than Tests\TestCase: the method
 * under test is static and pure, so booting the framework would only make the
 * Unit suite slow for no gain.
 */
class SearchSettingsBlacklistTest extends TestCase
{
    public function testNullYieldsTwoEmptyLists(): void
    {
        $this->assertSame([[], []], SearchSettings::parseBlacklistCookie(null));
    }

    public function testEmptyStringYieldsTwoEmptyLists(): void
    {
        $this->assertSame([[], []], SearchSettings::parseBlacklistCookie(""));
    }

    public function testBareHostnameLandsInTheHostList(): void
    {
        [$hosts, $tlds] = SearchSettings::parseBlacklistCookie("example.com");

        $this->assertSame(["example.com"], $hosts);
        $this->assertSame([], $tlds);
    }

    public function testSchemeAndPathAreStrippedToTheHost(): void
    {
        [$hosts] = SearchSettings::parseBlacklistCookie("https://example.com/path?q=1");

        $this->assertSame(["example.com"], $hosts);
    }

    public function testPortIsStrippedFromTheHost(): void
    {
        [$hosts] = SearchSettings::parseBlacklistCookie("http://example.com:8080/y");

        $this->assertSame(["example.com"], $hosts);
    }

    public function testWildcardEntriesLandInTheTldListWithoutTheWildcard(): void
    {
        [$hosts, $tlds] = SearchSettings::parseBlacklistCookie("*.example.com");

        $this->assertSame([], $hosts);
        $this->assertSame(["example.com"], $tlds);
    }

    public function testEntriesAreCommaSeparatedAndSplitAcrossBothLists(): void
    {
        [$hosts, $tlds] = SearchSettings::parseBlacklistCookie("b.example,*.wild.example,a.example");

        $this->assertSame(["a.example", "b.example"], $hosts);
        $this->assertSame(["wild.example"], $tlds);
    }

    public function testResultsAreDeduplicatedAndSorted(): void
    {
        [$hosts, $tlds] = SearchSettings::parseBlacklistCookie(
            "z.example,a.example,z.example,*.w.example,*.w.example"
        );

        $this->assertSame(["a.example", "z.example"], $hosts);
        $this->assertSame(["w.example"], $tlds);
    }

    public function testHostIsTruncatedTo255Characters(): void
    {
        $longLabel = str_repeat("a", 300);

        [$hosts] = SearchSettings::parseBlacklistCookie($longLabel . ".example");

        $this->assertCount(1, $hosts);
        $this->assertSame(255, strlen($hosts[0]));
    }

    public function testInputIsTruncatedTo2048Characters(): void
    {
        // 2048 chars of "aa.example," entries, then a marker that must fall outside
        // the truncation window and therefore never appear in the result.
        $filler = str_repeat("aa.example,", 200); // 2200 chars
        $input = $filler . "marker.example";

        [$hosts] = SearchSettings::parseBlacklistCookie($input);

        $this->assertNotContains("marker.example", $hosts);
    }

    /**
     * Characterization test, not an endorsement.
     *
     * The scheme check in parseBlacklistCookie is `/^https?:\/\//` — case
     * sensitive. An entry written with an uppercase scheme therefore fails the
     * check, gets "https://" prepended anyway, and parse_url() reads the leading
     * "HTTP" as the hostname. So "HTTP://example.com" blacklists the host "HTTP"
     * and silently fails to blacklist example.com.
     *
     * Pinned here so the behaviour is visible and so a later fix has to update
     * this test on purpose rather than by accident. See the note in Step 0 of
     * the modernization plan.
     */
    public function testUppercaseSchemeIsMisparsedIntoTheSchemeAsHostname(): void
    {
        [$hosts] = SearchSettings::parseBlacklistCookie("HTTP://example.com");

        $this->assertSame(["HTTP"], $hosts);
    }

    /**
     * Characterization test. parse_url() is lenient about what it accepts as a
     * host, so free text survives as a blacklist entry rather than being
     * rejected. Harmless today (it simply never matches a real host) but it
     * means the parser is not a validator.
     */
    public function testFreeTextSurvivesAsAnEntry(): void
    {
        [$hosts] = SearchSettings::parseBlacklistCookie("not a host");

        $this->assertSame(["not a host"], $hosts);
    }
}
