<?php

namespace Tests\Unit\Search;

use App\Models\Configuration\SearchEngineRegistry;
use App\Models\parserSkripte\Tootnews;
use App\Models\Result;
use App\Models\SearchengineConfiguration;
use Tests\TestCase;

/**
 * TootNews's feed declares a default Atom namespace
 * (`xmlns="http://www.w3.org/2005/Atom"`, no prefix). The parser queried it
 * with an unprefixed xpath, `//feed[1]/entry` — and XPath 1.0 never matches an
 * unprefixed name against an element that has a default namespace. Every real
 * response therefore parsed to zero entries; the engine was silently dead in
 * production while returning HTTP 200. This is a regression test against the
 * actual response shape (captured live from https://toot.suma-lab.de/search),
 * not a synthetic one, because the synthetic fixture a plain unit test would
 * reach for is exactly the kind of fixture that would have missed this — it
 * would not have had a reason to declare the default namespace the real feed
 * does.
 */
class TootNewsParsingTest extends TestCase
{
    /** Byte-for-byte the response body for ?q=test, captured 2026-08-24. */
    private const string REAL_FEED = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
                        <feed xmlns="http://www.w3.org/2005/Atom"
                              xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"
                              xmlns:mg="http://metager.de/opensearch/"
                              xmlns:ad="http://a9.com/-/opensearch/extensions/advertisement/1.0/">
                          <title>Nolm Results</title>
                          <updated>2025-07-04T13:53:31+02:00</updated>
                          <opensearch:totalResults>18</opensearch:totalResults>
                          <opensearch:Query role="request" searchTerms="test"/>
                          <id>urn:uuid:1d634a8c-2764-424f-b082-6c96494b7240</id>

                          <entry>
                            <title>Thousands in Sweden protest over climate policy as election looms</title>
                            <link href="https://www.euronews.com/my-europe/2026/08/23/thousands-rally-in-stockholm-over-climate-crisis-weeks-ahead-of-general-election" hreflang="en"/>
                            <mg:anzeigeLink>https://www.euronews.com/my-europe/2026/08/23/thousands-rally-in-stockholm-over-climate-crisis-weeks-ahead-of-general-election</mg:anzeigeLink>
                            <content type="text"  xml:lang="en">
                                The protest comes as experts in Sweden have warned that the government's climate policy jeopardises the country's emissions-cutting goals to reduce CO₂ emissions from transport by 70 percent by 2030 and to achieve net-zero emissions by 2045. #EuropeNews
                            </content>
                          </entry>

                          <entry>
                            <title>Swarming jellyfish overrun French nuclear plant on same date two years in a row </title>
                            <link href="https://www.politico.eu/article/swarming-jellyfish-overrun-french-nuclear-plant-on-same-date-two-years-in-a-row/" hreflang="en"/>
                            <mg:anzeigeLink>https://www.politico.eu/article/swarming-jellyfish-overrun-french-nuclear-plant-on-same-date-two-years-in-a-row/</mg:anzeigeLink>
                            <content type="text"  xml:lang="en">
                                The jellyfish invasion is the latest in a series of climate change-driven incidents that have affected a record number of French nuclear reactors over the summer.
                            </content>
                          </entry>

                        </feed>
        XML;

    private function engine(): Tootnews
    {
        $registry = app(SearchEngineRegistry::class);
        $configuration = new SearchengineConfiguration($registry->sumas->tootnews);

        return new Tootnews("tootnews", $configuration);
    }

    /**
     * `tootnews` is one engine name serving two hosts, chosen in
     * `Tootnews::__construct()` off the current locale -- see
     * `tests/Feature/Search/TootNewsLocaleTest.php` for the host itself.
     * This is the sibling check that the brand attributed to a result
     * (`Result::$gefVon`/`$gefVonLink`, i.e. `infos->displayName`/
     * `infos->homepage`) switches to "TroetNews" alongside the host, rather
     * than a German result staying labelled with the English brand.
     */
    public function testAttributesResultsToTroetNewsForAGermanSearch(): void
    {
        \LaravelLocalization::setLocale('de-DE');
        $engine = $this->engine();

        $engine->loadResults(self::REAL_FEED);

        $this->assertSame("TroetNews", $engine->results[0]->gefVon[0]);
        $this->assertStringContainsString("troetnews.suma-ev.de", $engine->results[0]->gefVonLink[0]);
    }

    /** @return array<int, array{titel: string, link: string, anzeigeLink: string}> */
    private function summarize(Tootnews $engine): array
    {
        return array_values(array_map(fn(Result $result) => [
            "titel" => $result->titel,
            "link" => $result->link,
            "anzeigeLink" => $result->anzeigeLink,
        ], $engine->results));
    }

    public function testParsesEveryEntryFromTheRealFeedShape(): void
    {
        $engine = $this->engine();

        $engine->loadResults(self::REAL_FEED);

        $this->assertCount(2, $engine->results, "the default-namespace regression made this 0");
        $this->assertSame(
            "Thousands in Sweden protest over climate policy as election looms",
            $engine->results[0]->titel,
        );
        $this->assertStringContainsString(
            "climate policy jeopardises",
            $engine->results[0]->longDescr,
        );
    }

    /**
     * On every entry captured so far, <mg:anzeigeLink> is byte-identical to
     * <link href>, so this and testFallsBackToTheLinkWhenAnzeigeLinkIsMissing
     * cannot currently distinguish "reads mg:anzeigeLink" from "always copies
     * link" by their assertions alone. It's read via children() with the
     * correct namespace anyway, on the strength of two things the assertions
     * don't capture: it is what the very first version of this parser
     * (a7771e7f8) tried to do before a namespace-access mistake made it
     * always empty, and a field the API bothers to emit separately from
     * <link> is a field it may one day make differ from it.
     */
    public function testUsesTheFeedsOwnAnzeigeLinkRatherThanRepeatingLink(): void
    {
        $engine = $this->engine();

        $engine->loadResults(self::REAL_FEED);

        // Result::__construct strips the scheme/www for display, so compare
        // against that, not the raw <mg:anzeigeLink> text.
        $this->assertSame(
            "euronews.com/my-europe/2026/08/23/thousands-rally-in-stockholm-over-climate-crisis-weeks-ahead-of-general-election",
            $engine->results[0]->anzeigeLink,
        );
    }

    /**
     * asXML() re-serialises a node, which re-escapes any entity that was in
     * the source ("&amp;" parses to "&" and then asXML() turns it back into
     * "&amp;"), so strip_tags() alone leaves the escaped form sitting in the
     * text users see. html_entity_decode() alone has the opposite problem: a
     * node with genuine nested markup loses that markup's text outright, not
     * just its tags, if read with (string) instead. TootNews's own titles
     * happen not to carry escaped entities today, so this fixture is
     * synthetic on purpose -- the two failure modes need entities and nested
     * markup to actually show up to be caught.
     *
     * `<i>real</i>` (genuine markup, not an escaped entity) still loses its
     * tags in the final description -- that happens one layer up, in
     * Result::__construct()'s own strip_tags($descr, '<p>'), and is correct:
     * the point here is that its text survives, unlike a (string) cast.
     */
    public function testDecodesEntitiesWithoutLosingTextInsideRealMarkup(): void
    {
        $engine = $this->engine();

        $engine->loadResults(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/">
              <entry>
                <title>AT&amp;T &amp; Verizon&#39;s deal</title>
                <link href="https://example.org/att-verizon"/>
                <content type="text">Some &lt;b&gt;real&lt;/b&gt; and <i>real</i> markup.</content>
              </entry>
            </feed>
            XML);

        $this->assertCount(1, $engine->results);
        $this->assertSame("AT&T & Verizon's deal", $engine->results[0]->titel);
        $this->assertStringContainsString("Some real and real markup.", $engine->results[0]->longDescr);
    }

    /**
     * Captured live 2026-08-24 from ?q=AT%26T: toot.suma-lab.de reflects the
     * query into `searchTerms` without escaping "&", which is invalid XML
     * (a bare "&" must be "&amp;") and fails the whole document, not just
     * that attribute -- simplexml_load_string() returns false and every
     * entry the response might otherwise have carried is lost with it. That
     * is an upstream bug MetaGer cannot fix from the parser, and any query
     * containing "&" hits it (`AT&T`, `rock & roll`, `Bosnia & Herzegovina`,
     * ...). Documented here as a characterization test: this is a real
     * observed response, and the assertion is only that it degrades to zero
     * results rather than throwing.
     */
    public function testAnUnescapedAmpersandInTheUpstreamResponseDoesNotThrow(): void
    {
        $engine = $this->engine();

        $engine->loadResults(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
                            <feed xmlns="http://www.w3.org/2005/Atom"
                                  xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"
                                  xmlns:mg="http://metager.de/opensearch/"
                                  xmlns:ad="http://a9.com/-/opensearch/extensions/advertisement/1.0/">
                              <title>Nolm Results</title>
                              <updated>2025-07-04T13:53:31+02:00</updated>
                              <opensearch:totalResults>18</opensearch:totalResults>
                              <opensearch:Query role="request" searchTerms="AT&T"/>
                              <id>urn:uuid:1d634a8c-2764-424f-b082-6c96494b7240</id>

                            </feed>
            XML);

        $this->assertSame([], $engine->results);
    }

    public function testFallsBackToTheLinkWhenAnzeigeLinkIsMissing(): void
    {
        $engine = $this->engine();

        $engine->loadResults(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/">
              <entry>
                <title>Ohne Anzeigelink</title>
                <link href="https://example.org/ohne-anzeigelink"/>
                <content type="text">Beschreibung.</content>
              </entry>
            </feed>
            XML);

        $this->assertCount(1, $engine->results);
        $this->assertSame("example.org/ohne-anzeigelink", $engine->results[0]->anzeigeLink);
    }

    public function testAnEntryMissingATitleOrLinkIsSkippedWithoutBreakingTheRest(): void
    {
        $engine = $this->engine();

        $engine->loadResults(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/">
              <entry>
                <link href="https://example.org/ohne-titel"/>
                <content type="text">Kein Titel.</content>
              </entry>
              <entry>
                <title>Ohne Link</title>
                <content type="text">Kein Link.</content>
              </entry>
              <entry>
                <title>Vollstaendig</title>
                <link href="https://example.org/vollstaendig"/>
                <content type="text">Hat alles.</content>
              </entry>
            </feed>
            XML);

        $this->assertSame(["Vollstaendig"], array_column($this->summarize($engine), "titel"));
    }

    public function testGarbageInputIsIgnoredRatherThanThrowing(): void
    {
        $engine = $this->engine();

        $engine->loadResults("not xml at all");

        $this->assertSame([], $engine->results);
    }

    public function testAFeedWithNoEntriesLeavesResultsEmpty(): void
    {
        $engine = $this->engine();

        $engine->loadResults('<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>');

        $this->assertSame([], $engine->results);
    }

    /**
     * `<published>` is RFC3339, and `Result::getDate()` is typed
     * `Carbon|null` -- storing anything but a real `Carbon` instance under
     * `additionalInformation['date']` would TypeError there instead of here.
     * `Carbon::parse()` reads RFC3339 natively, so this pins that the parsed
     * instant matches the feed rather than just "some Carbon or other".
     */
    public function testParsesThePublishedDateIntoAMachineReadableDate(): void
    {
        $engine = $this->engine();

        $engine->loadResults(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/">
              <entry>
                <title>Mit Datum</title>
                <link href="https://example.org/mit-datum"/>
                <published>2026-08-23T10:15:00+02:00</published>
                <content type="text">Beschreibung.</content>
              </entry>
            </feed>
            XML);

        $this->assertCount(1, $engine->results);
        $date = $engine->results[0]->getDate();
        $this->assertNotNull($date);
        $this->assertTrue($date->equalTo(\Carbon\Carbon::parse('2026-08-23T10:15:00+02:00')));
    }

    public function testAnEntryWithoutAPublishedDateLeavesTheDateUnset(): void
    {
        $engine = $this->engine();

        $engine->loadResults(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/">
              <entry>
                <title>Ohne Datum</title>
                <link href="https://example.org/ohne-datum"/>
                <content type="text">Beschreibung.</content>
              </entry>
            </feed>
            XML);

        $this->assertCount(1, $engine->results);
        $this->assertNull($engine->results[0]->getDate());
    }

    /**
     * Same defensive posture as testAnUnescapedAmpersandInTheUpstreamResponseDoesNotThrow:
     * an upstream field MetaGer does not control can be malformed, and that
     * must degrade to "no date" for this one entry, not kill parsing.
     */
    public function testAnUnparsablePublishedDateLeavesTheDateUnsetRatherThanThrowing(): void
    {
        $engine = $this->engine();

        $engine->loadResults(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/">
              <entry>
                <title>Kaputtes Datum</title>
                <link href="https://example.org/kaputtes-datum"/>
                <published>not-a-date</published>
                <content type="text">Beschreibung.</content>
              </entry>
            </feed>
            XML);

        $this->assertCount(1, $engine->results);
        $this->assertNull($engine->results[0]->getDate());
    }
}
