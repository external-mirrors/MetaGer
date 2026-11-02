<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * A result's sitelinks on the page.
 *
 * Brave sends a Wikipedia article's whole table of contents as sitelinks, and
 * they used to be printed in full: for "Geschichte Berlins" seventeen links,
 * six ragged lines of the accent colour, nothing to tell where one ends and the
 * next begins, and a result twice the height of its neighbours. Now four are
 * shown, the rest wait in a closed <details> (no JavaScript needed) as a list,
 * and each one in that list can be opened anonymously.
 *
 * Which links make it into the list is App\Search\Sitelinks, tested on its own
 * in tests/Unit/Search/SitelinksTest.
 */
class SitelinksTest extends TestCase
{
    use FakesSearchEngines;

    private const ARTICLE = "https://de.wikipedia.org/wiki/Geschichte_Berlins";

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function page(string $fixture = "brave-web-sitelinks.json", string $engine = "brave"): DOMXPath
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([$engine => $this->engineFixture($fixture)]);

        $response = $this->get("/de-DE/meta/meta.ger3?eingabe=" . urlencode("geschichte berlin") . "&focus=web");
        $response->assertOk();

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());

        return new DOMXPath($document);
    }

    private static function hasClass(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
    }

    private function sitelinksOf(DOMXPath $page, string $resultLink): DOMElement
    {
        $result = $page->query("//div[" . self::hasClass("result") . "][.//h2/a[@href='$resultLink']]")->item(0);
        $this->assertNotNull($result, "No result for $resultLink on the page.");
        $sitelinks = $page->query(".//div[" . self::hasClass("result-sitelinks") . "]", $result)->item(0);
        $this->assertNotNull($sitelinks, "The result for $resultLink shows no sitelinks.");

        return $sitelinks;
    }

    /** @return list<string> */
    private function hrefs(DOMXPath $page, string $query, DOMElement $context): array
    {
        return array_map(fn(DOMElement $a) => $a->getAttribute("href"), iterator_to_array($page->query($query, $context)));
    }

    public function testALongTableOfContentsShowsFourSectionsAndFoldsTheRest(): void
    {
        $page = $this->page();
        $sitelinks = $this->sitelinksOf($page, self::ARTICLE);

        $this->assertSame([
            self::ARTICLE . "#Namensherkunft",
            self::ARTICLE . "#Vorgeschichte_(16.000_v._Chr._bis_1.200_n._Chr.)",
            self::ARTICLE . "#Handelsstadt_im_Mittelalter_(1200–1448)",
            self::ARTICLE . "#Kurfürstliche_Residenzstadt",
        ], $this->hrefs($page, "./ul[" . self::hasClass("result-deep-buttons") . "]/li/a", $sitelinks));

        $all = $page->query("./details[" . self::hasClass("result-sitelinks-all") . "]", $sitelinks)->item(0);
        $this->assertNotNull($all);
        $this->assertFalse($all->hasAttribute("open"), "The full list is there to be asked for, not shown.");
        $this->assertSame("Alle 13 Abschnitte", trim($page->query("./summary", $all)->item(0)->textContent));
        $this->assertCount(13, $page->query("./ul/li", $all));
    }

    /**
     * Seventeen sections arrive, thirteen are listed: Siehe auch, Literatur
     * (chronologisch), Weblinks and Einzelnachweise lead to lists of sources.
     */
    public function testTheSourceListsAnArticleEndsWithAreNotListed(): void
    {
        $page = $this->page();
        $sitelinks = $this->sitelinksOf($page, self::ARTICLE);

        $listed = $this->hrefs($page, ".//a[not(" . self::hasClass("result-open-proxy") . ")]", $sitelinks);
        foreach (["Siehe_auch", "Literatur_(chronologisch)", "Weblinks", "Einzelnachweise"] as $section) {
            $this->assertNotContains(self::ARTICLE . "#" . $section, $listed);
        }
        $this->assertContains(self::ARTICLE . "#Dokumentationen", $listed);
    }

    /**
     * berlin.de's sitelinks are pages of their own, so they are "Links" — and
     * one of them is a page called Literatur, which is kept.
     */
    public function testSitelinksToOtherPagesAreCalledLinks(): void
    {
        $page = $this->page();
        $sitelinks = $this->sitelinksOf($page, "https://www.berlin.de/");

        $all = $page->query("./details[" . self::hasClass("result-sitelinks-all") . "]", $sitelinks)->item(0);
        $this->assertSame("Alle 6 Links", trim($page->query("./summary", $all)->item(0)->textContent));
        $this->assertContains("https://www.berlin.de/literatur/", $this->hrefs($page, "./ul/li/a[1]", $all));
    }

    /**
     * Each link in the full list has its own shield, signed for that link —
     * section fragment included — and wired for SafeBrowse the way a result's
     * own "Anonym öffnen" is.
     */
    public function testEveryLinkInTheFullListCanBeOpenedAnonymously(): void
    {
        $page = $this->page();
        $all = $page->query("./details", $this->sitelinksOf($page, self::ARTICLE))->item(0);

        $rows = iterator_to_array($page->query("./ul/li", $all));
        $this->assertCount(13, $rows);
        foreach ($rows as $row) {
            $link = $page->query("./a[1]", $row)->item(0)->getAttribute("href");
            $shield = $page->query("./a[" . self::hasClass("result-open-proxy--icon") . "]", $row)->item(0);
            $this->assertNotNull($shield, "No way to open $link anonymously.");
            $this->assertStringContainsString("url=" . rawurlencode($link), $shield->getAttribute("href"));
            $this->assertStringContainsString("#url=" . rawurlencode($link), $shield->getAttribute("data-proxy-link"));
        }
    }

    /**
     * The inline links stay plain: a shield beside each of four links in one
     * line would be eight things to tell apart instead of four.
     */
    public function testTheInlineLinksCarryNoShield(): void
    {
        $page = $this->page();
        $inline = $page->query("./ul[" . self::hasClass("result-deep-buttons") . "]", $this->sitelinksOf($page, self::ARTICLE))->item(0);

        $this->assertSame(0, $page->query(".//a[" . self::hasClass("result-open-proxy") . "]", $inline)->length);
    }

    public function testAShortSetHasNothingToUnfold(): void
    {
        $page = $this->page("serper-web.json", "serper_web");
        $sitelinks = $this->sitelinksOf($page, "https://serper-example.net/kaffee");

        $this->assertCount(2, $page->query("./ul/li", $sitelinks));
        $this->assertSame(0, $page->query("./details", $sitelinks)->length);
    }

    public function testAResultWithoutSitelinksHasNoSitelinkBlock(): void
    {
        $page = $this->page();
        $result = $page->query("//div[" . self::hasClass("result") . "][.//h2/a[@href='https://beispiel.de/mauer']]")->item(0);

        $this->assertNotNull($result);
        $this->assertSame(0, $page->query(".//div[" . self::hasClass("result-sitelinks") . "]", $result)->length);
    }
}
