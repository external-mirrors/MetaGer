<?php

namespace Tests\Unit\Search;

use App\Models\DeepResults\Button;
use App\Models\Result;
use App\Search\Sitelink;
use App\Search\Sitelinks;
use Tests\TestCase;

/**
 * Which of a result's sitelinks are shown, how they are grouped, and that each
 * can be opened through the proxy.
 *
 * The page side — four inline, the rest behind a <details> — is in
 * tests/Feature/Search/SitelinksTest. These are the rules that decide what goes
 * into it, at the size where a broken rule fails in one line.
 *
 * Extends Tests\TestCase rather than PHPUnit's: proxy links are signed with a
 * config value.
 */
class SitelinksTest extends TestCase
{
    private function hit(string $link, array $buttons): Result
    {
        $result = new Result(1, "Geschichte Berlins – Wikipedia", $link, $link, "Beschreibung", "brave", "https://brave.example/", 1);
        foreach ($buttons as $title => $url) {
            $result->deepResults["buttons"][] = new Button($title, $url);
        }
        return $result;
    }

    /** @return list<string> */
    private static function titles(array $buttons): array
    {
        return array_map(fn(Sitelink $link) => $link->title, $buttons);
    }

    public function testAResultWithoutSitelinksHasNone(): void
    {
        $sitelinks = Sitelinks::of($this->hit("https://example.org/", []));

        $this->assertTrue($sitelinks->isEmpty());
        $this->assertFalse($sitelinks->hasMore());
    }

    /**
     * Brave sends a Wikipedia article's table of contents as its sitelinks:
     * every one the article itself, with a #fragment. Those are sections of one
     * page, and are named so on the page.
     */
    public function testLinksIntoTheResultsOwnPageAreSections(): void
    {
        $page = "https://de.wikipedia.org/wiki/Geschichte_Berlins";
        $sitelinks = Sitelinks::of($this->hit($page, [
            "Namensherkunft" => "$page#Namensherkunft",
            "Vorgeschichte" => "$page#Vorgeschichte",
        ]));

        $this->assertTrue($sitelinks->areSections);
    }

    /**
     * The article URL and its section URLs are not always spelled alike: one
     * percent-encodes the umlaut, the other does not, or one ends in a slash.
     */
    public function testASectionIsRecognisedThroughSpellingDifferences(): void
    {
        $sitelinks = Sitelinks::of($this->hit("https://de.wikipedia.org/wiki/M%C3%BCnchen", [
            "Geschichte" => "https://de.wikipedia.org/wiki/München#Geschichte",
            "Geografie" => "http://de.wikipedia.org/wiki/M%C3%BCnchen/#Geografie",
        ]));

        $this->assertTrue($sitelinks->areSections);
    }

    public function testLinksToOtherPagesAreNotSections(): void
    {
        $sitelinks = Sitelinks::of($this->hit("https://www.berlin.de/", [
            "Bürgerservice" => "https://service.berlin.de/",
            "Tourismus" => "https://www.berlin.de/tourismus/",
        ]));

        $this->assertFalse($sitelinks->areSections);
    }

    /**
     * One link to another page makes the whole set ordinary sitelinks: calling
     * it "Abschnitte" would then be wrong for that one.
     */
    public function testAMixedSetIsNotSections(): void
    {
        $page = "https://de.wikipedia.org/wiki/Berlin";
        $sitelinks = Sitelinks::of($this->hit($page, [
            "Geschichte" => "$page#Geschichte",
            "Brandenburg" => "https://de.wikipedia.org/wiki/Brandenburg",
        ]));

        $this->assertFalse($sitelinks->areSections);
    }

    /**
     * The headings every Wikipedia article ends with lead to lists of sources,
     * which is never what a search was for. They take a quarter of the line in
     * a typical article, so they are dropped — in the languages Wikipedia is
     * read in most, and with a qualifier such as "(chronologisch)" too.
     */
    public function testReferenceSectionsAreDropped(): void
    {
        $page = "https://de.wikipedia.org/wiki/Geschichte_Berlins";
        $sitelinks = Sitelinks::of($this->hit($page, [
            "Namensherkunft" => "$page#Namensherkunft",
            "Siehe auch" => "$page#Siehe_auch",
            "Literatur (chronologisch)" => "$page#Literatur_(chronologisch)",
            "Dokumentationen" => "$page#Dokumentationen",
            "Weblinks" => "$page#Weblinks",
            "Einzelnachweise" => "$page#Einzelnachweise",
            "External links" => "$page#External_links",
            "  references " => "$page#References",
        ]));

        $this->assertSame(["Namensherkunft", "Dokumentationen"], self::titles($sitelinks->links));
    }

    /**
     * Only a section of the result's own page is a reference section. A site
     * whose sitelinks include a page called "Literatur" means that page.
     */
    public function testAPageNamedLikeAReferenceSectionStays(): void
    {
        $sitelinks = Sitelinks::of($this->hit("https://www.buchhandlung.example/", [
            "Literatur" => "https://www.buchhandlung.example/literatur",
            "Sachbuch" => "https://www.buchhandlung.example/sachbuch",
        ]));

        $this->assertSame(["Literatur", "Sachbuch"], self::titles($sitelinks->links));
    }

    /**
     * ResultDeduplicator merges the buttons of every engine that found the
     * result, so the same sitelink can arrive twice.
     */
    public function testALinkArrivingTwiceIsShownOnce(): void
    {
        $result = $this->hit("https://www.berlin.de/", [
            "Tourismus" => "https://www.berlin.de/tourismus/",
        ]);
        $result->deepResults["buttons"][] = new Button("Tourismus in Berlin", "https://www.berlin.de/tourismus/");

        $this->assertSame(["Tourismus"], self::titles(Sitelinks::of($result)->links));
    }

    public function testTheFirstFourAreShownInlineAndTheRestOnlyWhenAsked(): void
    {
        $buttons = [];
        foreach (range(1, 6) as $n) {
            $buttons["Seite $n"] = "https://example.org/$n";
        }
        $sitelinks = Sitelinks::of($this->hit("https://example.org/", $buttons));

        $this->assertSame(["Seite 1", "Seite 2", "Seite 3", "Seite 4"], self::titles($sitelinks->inline()));
        $this->assertTrue($sitelinks->hasMore());
        $this->assertCount(6, $sitelinks->links);
    }

    public function testFourOrFewerNeedNoMore(): void
    {
        $buttons = [];
        foreach (range(1, 4) as $n) {
            $buttons["Seite $n"] = "https://example.org/$n";
        }

        $this->assertFalse(Sitelinks::of($this->hit("https://example.org/", $buttons))->hasMore());
    }

    /**
     * Someone who opens every result anonymously would otherwise leave the
     * proxy the moment they click a sitelink. The fragment goes into the
     * signed URL with everything else, so the proxy receives the section too.
     */
    public function testEverySitelinkCarriesAProxyLinkOfItsOwn(): void
    {
        $page = "https://de.wikipedia.org/wiki/Geschichte_Berlins";
        $result = $this->hit($page, ["Weimarer Republik" => "$page#Die_Weimarer_Republik_(1919–1933)"]);

        $link = Sitelinks::of($result)->links[0];

        $this->assertSame($result->generateProxyLink("$page#Die_Weimarer_Republik_(1919–1933)"), $link->proxyLink);
        $this->assertStringContainsString(rawurlencode("#Die_Weimarer_Republik_(1919–1933)"), $link->proxyLink);
    }

    /**
     * The result's own buttons are cached with it; the signed URLs only the
     * page needs stay off them (LoaderCacheTest's size budget).
     */
    public function testTheResultsOwnButtonsAreLeftAlone(): void
    {
        $result = $this->hit("https://www.berlin.de/", ["Tourismus" => "https://www.berlin.de/tourismus/"]);

        Sitelinks::of($result);

        $this->assertSame(["title", "link"], array_keys(get_object_vars($result->deepResults["buttons"][0])));
    }
}
