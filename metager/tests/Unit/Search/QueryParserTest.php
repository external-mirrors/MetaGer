<?php

namespace Tests\Unit\Search;

use App\Search\QueryParser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * QueryParser in isolation.
 *
 * The behaviour is covered end-to-end in
 * tests/Feature/Search/SpecialSearchesTest (the operators as a user experiences
 * them) and tests/Feature/Search/QueryParsingTest (the request parameters
 * around them). This file is for the edge cases those cannot reach without
 * inventing a search for each: an operator used twice, an operator that looks
 * like another one, a query that is nothing but operators.
 *
 * Extends Tests\TestCase because the parser reads the user's saved blacklists
 * off the SearchSettings singleton and translates its warnings.
 */
class QueryParserTest extends TestCase
{
    private function parse(string $query, array $parameters = []): \App\Search\SearchQuery
    {
        return (new QueryParser())->parse($query, Request::create("/meta/meta.ger3", "GET", $parameters));
    }

    // ------------------------------------------------------------ operators

    public function testAQueryWithoutOperatorsComesBackUntouched(): void
    {
        $query = $this->parse("kaffee rösten");

        $this->assertSame("kaffee rösten", $query->q);
        $this->assertSame([], $query->phrases);
        $this->assertSame([], $query->stopWords);
        $this->assertSame([], $query->urlBlacklist);
    }

    /**
     * `-site:host` and `-site:*.domain` differ by two characters and are told
     * apart by their patterns alone, so they are parsed in a fixed order: the
     * host pattern refuses a leading `*` and runs first. Swapping them would
     * make every domain exclusion a host called `*.something`.
     */
    public function testAHostExclusionAndADomainExclusionAreToldApart(): void
    {
        $query = $this->parse("kaffee -site:beispiel.de -site:*.example.org");

        $this->assertSame(["beispiel.de"], array_values($query->hostBlacklist));
        $this->assertSame(["example.org"], array_values($query->domainBlacklist));
        $this->assertSame("kaffee", $query->q);
    }

    public function testAnOperatorMayBeUsedMoreThanOnce(): void
    {
        $query = $this->parse("kaffee -site:a.de -site:b.de -url:werbung -url:tracking");

        $this->assertSame(["a.de", "b.de"], array_values($query->hostBlacklist));
        $this->assertSame(["werbung", "tracking"], $query->urlBlacklist);
        $this->assertSame("kaffee", $query->q);
    }

    /**
     * Phrases are read out of the query but left in it — an engine that
     * understands quotes should still get them.
     */
    public function testAPhraseIsRecordedWithoutBeingRemovedFromTheQuery(): void
    {
        $query = $this->parse('kaffee "cold brew"');

        $this->assertSame(["cold brew"], $query->phrases);
        $this->assertSame('kaffee "cold brew"', $query->q);
    }

    /**
     * A hyphenated word inside quotes is part of the phrase, not a stopword.
     */
    public function testAHyphenInsideAPhraseIsNotAStopword(): void
    {
        $query = $this->parse('kaffee "single-origin -direct trade"');

        $this->assertSame([], $query->stopWords);
    }

    public function testAStopwordIsTakenOutOfTheQuery(): void
    {
        $query = $this->parse("kaffee -espresso");

        $this->assertSame(["espresso"], $query->stopWords);
        $this->assertSame("kaffee", $query->q);
    }

    /**
     * A bare hyphen, or one in front of punctuation, is not a stopword — the
     * pattern requires a letter or digit after it. That is what keeps a query
     * like `vitamin b-12` or a dash between words from silently filtering
     * results away.
     */
    #[DataProvider("hyphensThatAreNotOperators")]
    public function testAHyphenThatIsNotAnOperatorIsLeftAlone(string $query): void
    {
        $this->assertSame([], $this->parse($query)->stopWords, "`$query` was read as a stopword.");
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function hyphensThatAreNotOperators(): array
    {
        return [
            "a bare hyphen" => ["kaffee - tee"],
            "a hyphen before punctuation" => ["kaffee -!"],
        ];
    }

    /**
     * A query of nothing but operators leaves nothing to search for, which is a
     * warning rather than an error.
     */
    public function testAQueryOfNothingButOperatorsWarns(): void
    {
        $query = $this->parse("-site:beispiel.de");

        $this->assertSame("", $query->q);
        $this->assertNotEmpty($query->warnings);
    }

    public function testAQueryAboutSelfHarmProducesAPreventionWarning(): void
    {
        $this->assertNotEmpty($this->parse("depressionen hilfe")->htmlWarnings);
        $this->assertEmpty($this->parse("kaffee")->htmlWarnings);
    }

    /**
     * The trigger check runs on the query with the operators already removed,
     * so excluding a trigger word does not raise the warning.
     */
    public function testExcludingATriggerWordDoesNotRaiseThePreventionWarning(): void
    {
        $this->assertEmpty($this->parse("kaffee -depression")->htmlWarnings);
    }

    // ---------------------------------------------------------- date ranges

    private function sanitize(array $parameters): Request
    {
        return (new QueryParser())->sanitizeFilters(Request::create("/meta/meta.ger3", "GET", $parameters));
    }

    public function testAUsableRangeIsLeftAsItIs(): void
    {
        $from = Carbon::now()->subDays(60)->format("Y-m-d");
        $to = Carbon::now()->subDays(30)->format("Y-m-d");

        $request = $this->sanitize(["fc" => "on", "ff" => $from, "ft" => $to]);

        $this->assertSame($from, $request->input("ff"));
        $this->assertSame($to, $request->input("ft"));
    }

    /**
     * @return array<string, array{0: array<string, string>}>
     */
    public static function rangesThatAreDropped(): array
    {
        return [
            "half a range" => [["fc" => "on", "ff" => "2026-01-01"]],
            "not a date" => [["fc" => "on", "ff" => "gestern", "ft" => "heute"]],
            "the wrong format" => [["fc" => "on", "ff" => "01.01.2026", "ft" => "01.02.2026"]],
            "dates without the switch" => [["ff" => "2026-01-01", "ft" => "2026-02-01"]],
        ];
    }

    /**
     * @param array<string, string> $parameters
     */
    #[DataProvider("rangesThatAreDropped")]
    public function testARangeThatCannotBeUsedIsRemovedEntirely(array $parameters): void
    {
        $request = $this->sanitize($parameters);

        $this->assertNull($request->input("ff"));
        $this->assertNull($request->input("ft"));
        $this->assertNull($request->input("fc"));
    }

    public function testARangeRunningBackwardsIsSwapped(): void
    {
        $earlier = Carbon::now()->subDays(60)->format("Y-m-d");
        $later = Carbon::now()->subDays(30)->format("Y-m-d");

        $request = $this->sanitize(["fc" => "on", "ff" => $later, "ft" => $earlier]);

        $this->assertSame($earlier, $request->input("ff"));
        $this->assertSame($later, $request->input("ft"));
    }

    public function testDatesOutsideTheAllowedWindowArePulledIntoIt(): void
    {
        $request = $this->sanitize([
            "fc" => "on",
            "ff" => Carbon::now()->subYears(5)->format("Y-m-d"),
            "ft" => Carbon::now()->addYears(5)->format("Y-m-d"),
        ]);

        $this->assertSame(Carbon::now()->subYear()->format("Y-m-d"), $request->input("ff"));
        $this->assertSame(Carbon::now()->format("Y-m-d"), $request->input("ft"));
    }

    /**
     * A custom range and a quick freshness filter in one request would send an
     * engine two contradicting instructions, so the quick one goes.
     */
    public function testTheQuickFreshnessFilterIsDroppedWhenACustomRangeIsGiven(): void
    {
        $request = $this->sanitize([
            "f" => "d",
            "fc" => "on",
            "ff" => Carbon::now()->subDays(60)->format("Y-m-d"),
            "ft" => Carbon::now()->subDays(30)->format("Y-m-d"),
        ]);

        $this->assertNull($request->input("f"));
        $this->assertSame("on", $request->input("fc"));
    }

    /**
     * The quick filter on its own is not touched.
     */
    public function testTheQuickFreshnessFilterSurvivesOnItsOwn(): void
    {
        $this->assertSame("d", $this->sanitize(["f" => "d"])->input("f"));
    }
}
