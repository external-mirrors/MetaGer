<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * The "open results anonymously" setting: every way into a result goes
 * through the proxy, and the action row offers the direct way instead.
 *
 * Without it, someone who opens everything anonymously has to find the shield
 * on every result, and anything that is not a shield — the title, the address,
 * the picture, a sitelink, a grouped result — leaves the proxy the moment it is
 * clicked. Decided on the server, so it holds without JavaScript; with it,
 * resultpage/proxy.js sends these links to SafeBrowse as it does the shields.
 */
class OpenAnonymouslyTest extends TestCase
{
    use FakesSearchEngines;

    private const PROXY = "https://proxy.metager.de/";

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function page(string $query, array $engines, array $headers = [], bool $anonymously = true): DOMXPath
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses($engines);

        $request = $this->withHeaders($headers);
        if ($anonymously) {
            $request = $request->withUnencryptedCookie("open_anonymously", "on");
        }
        $response = $request->get("/de-DE/meta/meta.ger3?" . $query);
        $response->assertOk();

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());

        return new DOMXPath($document);
    }

    private function webPage(string $fixture = "brave-web.json", array $headers = [], bool $anonymously = true): DOMXPath
    {
        return $this->page("eingabe=kaffee&focus=web", ["brave" => $this->engineFixture($fixture)], $headers, $anonymously);
    }

    private static function hasClass(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
    }

    /** @return list<DOMElement> */
    private function results(DOMXPath $page): array
    {
        $results = iterator_to_array($page->query("//div[@id='results']/div[" . self::hasClass("result") . " and not(" . self::hasClass("ab-hint") . ")]"));
        $this->assertNotEmpty($results, "The page shows no results at all.");

        return $results;
    }

    /**
     * A link that opens $direct through the proxy: the old proxy as its href,
     * which is what works without JavaScript, and SafeBrowse for proxy.js.
     */
    private function assertOpensAnonymously(DOMElement $link, string $direct): void
    {
        $href = $link->getAttribute("href");
        $this->assertStringStartsWith(self::PROXY, $href, "$direct is opened directly.");
        $this->assertStringContainsString("url=" . rawurlencode($direct), $href);
        $this->assertStringContainsString("#url=" . rawurlencode($direct), $link->getAttribute("data-proxy-link"));
    }

    private function directLinkOf(DOMXPath $page, DOMElement $result): string
    {
        $direct = $page->query(".//div[" . self::hasClass("result-footer") . "]/a[" . self::hasClass("result-open-direct") . "]", $result)->item(0);
        $this->assertNotNull($direct, "The action row offers no way to open the result directly.");

        return $direct->getAttribute("href");
    }

    public function testTheTitleAddressAndPictureOpenThroughTheProxy(): void
    {
        $page = $this->webPage();

        foreach ($this->results($page) as $result) {
            $direct = $this->directLinkOf($page, $result);
            $this->assertStringStartsWith("https://", $direct);
            $this->assertOpensAnonymously($page->query(".//h2[" . self::hasClass("result-title") . "]/a", $result)->item(0), $direct);
            $this->assertOpensAnonymously($page->query(".//a[" . self::hasClass("result-link") . "]", $result)->item(0), $direct);
            foreach ($page->query(".//div[" . self::hasClass("result-image") . " or " . self::hasClass("result-logo") . "]/a", $result) as $picture) {
                $this->assertOpensAnonymously($picture, $direct);
            }
        }
    }

    public function testSitelinksOpenThroughTheProxy(): void
    {
        $page = $this->webPage("brave-web-sitelinks.json");

        $sitelinks = iterator_to_array($page->query("//div[" . self::hasClass("result-sitelinks") . "]//li/a"));
        $this->assertNotEmpty($sitelinks);
        foreach ($sitelinks as $link) {
            $this->assertOpensAnonymously($link, $link->getAttribute("title"));
        }
    }

    public function testGroupedResultsOpenThroughTheProxy(): void
    {
        $page = $this->webPage();

        $grouped = iterator_to_array($page->query("//a[" . self::hasClass("inherited-title") . "]"));
        $this->assertCount(2, $grouped);
        foreach ($grouped as $link) {
            $this->assertOpensAnonymously($link, $link->getAttribute("title"));
        }
    }

    public function testFoldedResultsOpenThroughTheProxy(): void
    {
        $page = $this->page("eingabe=" . urlencode("gebrauchte waschmaschine") . "&focus=web", [
            "brave" => $this->engineFixture("brave-web-same-host.json"),
        ]);

        $folded = iterator_to_array($page->query("//details[" . self::hasClass("result-more-from-host") . "]/ul/li/a[1]"));
        $this->assertCount(10, $folded);
        foreach ($folded as $link) {
            $this->assertOpensAnonymously($link, $link->getAttribute("title"));
        }
    }

    public function testNewsAndVideoCardsOpenThroughTheProxy(): void
    {
        $page = $this->webPage();

        $cards = iterator_to_array($page->query("//div[" . self::hasClass("inline-result") . "]"));
        $this->assertNotEmpty($cards);
        foreach ($cards as $card) {
            $direct = $page->query(".//a[" . self::hasClass("result-open-direct") . "]", $card)->item(0);
            $this->assertNotNull($direct, "A card offers no way to open its page directly.");
            $this->assertSame("Direkt", trim($direct->textContent));
            foreach ($page->query("./a", $card) as $link) {
                $this->assertOpensAnonymously($link, $direct->getAttribute("href"));
            }
        }
    }

    public function testImageResultsOpenTheirPageThroughTheProxy(): void
    {
        $page = $this->page("eingabe=kaffee&focus=bilder", [
            "brave_images" => $this->engineFixture("brave-images.json"),
            "serper_images" => "no-result",
        ]);

        $links = iterator_to_array($page->query("//a[" . self::hasClass("link") . "]"));
        $this->assertNotEmpty($links);
        foreach ($links as $link) {
            $row = $page->query("following-sibling::div[" . self::hasClass("actions") . "]", $link)->item(0);
            $direct = $page->query(".//a[" . self::hasClass("result-open-direct") . "]", $row)->item(0);
            $this->assertNotNull($direct, "The image details offer no way to open the page directly.");
            $this->assertOpensAnonymously($link, $direct->getAttribute("href"));
        }
    }

    /**
     * The shield turns into its opposite: the one thing in the row that opens
     * the result directly. The other-tab action goes — through SafeBrowse every
     * result opens in a tab of its own anyway — and the shields beside grouped,
     * folded and listed links go, because those links are anonymous already.
     */
    public function testTheActionRowOffersTheDirectWayInstead(): void
    {
        $page = $this->webPage("brave-web-sitelinks.json");

        foreach ($this->results($page) as $result) {
            $first = $page->query(".//div[" . self::hasClass("result-footer") . "]/*[1]", $result)->item(0);
            $this->assertStringContainsString("result-open-direct", $first->getAttribute("class"));
            $this->assertSame("Direkt öffnen", trim($page->query("./span[" . self::hasClass("action-label-long") . "]", $first)->item(0)->textContent));
            $this->assertFalse($first->hasAttribute("data-proxy-link"), "proxy.js would send the direct way through SafeBrowse.");
            $this->assertSame(0, $page->query(".//div[" . self::hasClass("result-footer") . "]/a[" . self::hasClass("result-open") . " or " . self::hasClass("result-open-newtab") . "]", $result)->length);
        }
        $this->assertSame(0, $page->query("//a[" . self::hasClass("result-open-proxy") . "]")->length);
    }

    /**
     * Results that arrive after the page was sent are rendered by the
     * load-more request. It reads its settings from its own request, like the
     * page did (SearchSettingsProvider boots even the settings it restores from
     * the loader entry), and the browser sends it the same way: with the cookie,
     * or — for a visitor whose cookies do not stick — with the setting carried
     * in the page's query string, which scriptResultPage.js builds its URL from.
     *
     * @return array<string, array{0: bool}>
     */
    public static function waysTheSettingTravels(): array
    {
        return ["as a cookie" => [true], "in the query string" => [false]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider("waysTheSettingTravels")]
    public function testResultsLoadedLaterOpenThroughTheProxyToo(bool $cookie): void
    {
        $this->actingAsSearchUser();
        $serper = $this->engineFixture("serper-web.json");
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => fn() => null,
        ]);
        $query = "eingabe=kaffee&focus=web" . ($cookie ? "" : "&open_anonymously=on");
        if ($cookie) {
            $this->withUnencryptedCookie("open_anonymously", "on");
        }

        $page = $this->get("/de-DE/meta/meta.ger3?" . $query);
        $page->assertOk();
        preg_match('/name="searchkey" content="([^"]+)"/', $page->getContent(), $key);
        $fetcher->deliverLate("serper_web", $serper);
        $more = $this->get("/meta/loadMore?" . $query . "&loadMore=loader_" . $key[1] . "&script=yes");

        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="utf-8"?><div id="results">' . $more->json("results") . "</div>");
        $title = (new DOMXPath($document))->query("//div[" . self::hasClass("result") . "][.//a[" . self::hasClass("result-open-direct") . "][@href='https://serper-example.net/kaffee']]//h2/a")->item(0);
        $this->assertNotNull($title, "The late Serper result does not open anonymously.");
        $this->assertOpensAnonymously($title, "https://serper-example.net/kaffee");
    }

    /** A page viewed through the proxy is anonymous already. */
    public function testAPageViewedThroughTheProxyLinksDirectly(): void
    {
        $page = $this->webPage("brave-web.json", ["is-proxy" => "true"]);

        foreach ($this->results($page) as $result) {
            $title = $page->query(".//h2[" . self::hasClass("result-title") . "]/a", $result)->item(0);
            $this->assertStringStartsNotWith(self::PROXY, $title->getAttribute("href"));
            $this->assertSame(0, $page->query(".//a[" . self::hasClass("result-open-direct") . "]", $result)->length);
        }
    }

    public function testWithoutTheSettingResultsOpenDirectly(): void
    {
        $page = $this->webPage("brave-web.json", [], false);

        foreach ($this->results($page) as $result) {
            $title = $page->query(".//h2[" . self::hasClass("result-title") . "]/a", $result)->item(0);
            $this->assertStringStartsNotWith(self::PROXY, $title->getAttribute("href"));
            $this->assertFalse($title->hasAttribute("data-proxy-link"));
        }
        $this->assertSame(0, $page->query("//a[" . self::hasClass("result-open-direct") . "]")->length);
    }

    public function testTheSettingsPageOffersIt(): void
    {
        $response = $this->get("/de-DE/meta/settings?focus=web&url=" . urlencode("https://metager.de"));

        $response->assertOk();
        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());
        $select = (new DOMXPath($document))->query("//select[@name='open_anonymously']")->item(0);
        $this->assertNotNull($select, "The settings page has no way to turn it on.");
        $this->assertStringContainsString("Ergebnisse anonym öffnen", $response->getContent());
    }

    public function testSavingItSetsACookieThatTheResultPageReads(): void
    {
        $response = $this->post("/de-DE/meta/settings/es", ["focus" => "web", "open_anonymously" => "on"]);

        $response->assertRedirect();
        $response->assertCookie("open_anonymously", "on", false);
    }
}
