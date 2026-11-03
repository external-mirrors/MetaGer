<?php

namespace Tests\Unit;

use App\Models\Result;
use Tests\TestCase;

/**
 * Characterization tests for Result::calcURLBoost(), the URL part of a
 * result's rank, written before the display URL was generalised.
 *
 * It read $anzeigeLink, the text drawn under the title, rather than $link,
 * so the same page scored differently depending on how its engine chose to
 * display it: Brave's "host › path › segment" kept a three-byte "›" per
 * segment, and a Brave result got about a fifth less URL boost than the same
 * URL from Serper. It reads the link now.
 *
 * One thing pinned here is wrong, and is pinned so the change that fixes it
 * is a deliberate one:
 *
 *  - It does not compare the query with the URL at all. $tmpLink starts empty
 *    and nothing writes to it, so strpos() is false for every character, and
 *    false >= 0 is true: every character of the query "matches". The boost is
 *    therefore (characters in the query) / (characters in the URL × 60) —
 *    shorter URLs rank higher, whatever they say.
 */
class ResultUrlBoostTest extends TestCase
{
    private const QUERY = "berlin wiki";

    private function boost(string $link, string $display): float
    {
        $description = str_repeat("Beschreibung ", 5); // the boost needs more than 40 characters
        $result = new Result(1, "Titel", $link, $display, $description, "Engine", "https://example.org", 1);

        return $result->calcURLBoost(self::QUERY);
    }

    /**
     * "berlinwiki" is 10 characters, "klexikonzumdewikiBerlin" 23: the URL
     * without scheme, dots, slashes and hyphens.
     */
    public function testTheBoostIsTheQueryLengthOverTheUrlLength(): void
    {
        $this->assertEqualsWithDelta(
            10 / (23 * 60),
            $this->boost("https://klexikon.zum.de/wiki/Berlin", "https://klexikon.zum.de/wiki/Berlin"),
            1e-12
        );
    }

    /** CHARACTERIZATION: no character of the URL has to match the query. */
    public function testCharacterizationAQueryUnrelatedToTheUrlScoresTheSame(): void
    {
        $description = str_repeat("Beschreibung ", 5);
        $result = new Result(1, "Titel", "https://klexikon.zum.de/wiki/Berlin", "https://klexikon.zum.de/wiki/Berlin", $description, "Engine", "https://example.org", 1);

        $this->assertSame($result->calcURLBoost("berlin wiki"), $result->calcURLBoost("xxxxxx qqqq"));
    }

    /** The query string never counts: only host and path are read. */
    public function testTheQueryStringIsIgnored(): void
    {
        $this->assertSame(
            $this->boost("https://klexikon.zum.de/wiki/Berlin", "https://klexikon.zum.de/wiki/Berlin"),
            $this->boost("https://klexikon.zum.de/wiki/Berlin?x=1", "https://klexikon.zum.de/wiki/Berlin?x=1"),
        );
    }

    public function testShortDescriptionsGetNoUrlBoost(): void
    {
        $result = new Result(1, "Titel", "https://klexikon.zum.de/wiki/Berlin", "https://klexikon.zum.de/wiki/Berlin", "kurz", "Engine", "https://example.org", 1);

        $this->assertSame(0, $result->calcURLBoost(self::QUERY));
    }

    /** The same page scores the same, however its engine displays it. */
    public function testTheBoostReadsTheLinkNotTheDisplayUrl(): void
    {
        $this->assertSame(
            $this->boost("https://klexikon.zum.de/wiki/Berlin", "https://klexikon.zum.de/wiki/Berlin"),
            $this->boost("https://klexikon.zum.de/wiki/Berlin", "klexikon.zum.de › wiki › Berlin"),
        );
    }

    /** "www." is not part of the length, as it was not when the display URL was read. */
    public function testTheWwwPrefixDoesNotCount(): void
    {
        $this->assertSame(
            $this->boost("https://klexikon.zum.de/wiki/Berlin", "x"),
            $this->boost("https://www.klexikon.zum.de/wiki/Berlin", "x"),
        );
    }
}
