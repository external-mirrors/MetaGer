<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Every result can be opened anonymously, wherever and however it is shown.
 *
 * "Anonym öffnen" used to exist on the web result alone: the news and video
 * cards, the grouped and folded results and the image details had no way to
 * open their page through the proxy. The action row now matches the Android
 * app's (app-en, ResultRow.tsx): open anonymously first, the other way to open
 * it second, the engines that found it at the end.
 */
class ResultActionsTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function page(string $query, array $engines, array $headers = []): DOMXPath
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses($engines);

        $response = $this->withHeaders($headers)->get("/de-DE/meta/meta.ger3?" . $query);
        $response->assertOk();

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());

        return new DOMXPath($document);
    }

    private function webPage(array $headers = []): DOMXPath
    {
        return $this->page("eingabe=kaffee&focus=web", [
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ], $headers);
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

    public function testEveryResultOffersToOpenItAnonymouslyFirst(): void
    {
        $page = $this->webPage();

        foreach ($this->results($page) as $result) {
            $first = $page->query(".//div[" . self::hasClass("result-footer") . "]/*[1]", $result)->item(0);
            $this->assertNotNull($first);
            $this->assertSame("a", $first->nodeName);
            $this->assertStringContainsString("result-open-proxy", $first->getAttribute("class"));
        }
    }

    public function testTheEnginesThatFoundAResultEndItsActionRow(): void
    {
        $page = $this->webPage();

        foreach ($this->results($page) as $result) {
            $last = $page->query(".//div[" . self::hasClass("result-footer") . "]/*[last()]", $result)->item(0);
            $this->assertStringContainsString("result-hoster", $last->getAttribute("class"));
        }
    }

    /**
     * Both ways to open a result stay in the row at every width: through the
     * proxy, and the other way round from what the title does. A phone gets
     * the short labels, not fewer actions.
     */
    public function testTheActionRowOffersTheOtherWayToOpenTheResult(): void
    {
        $page = $this->webPage();

        foreach ($this->results($page) as $result) {
            $title = $page->query(".//h2[" . self::hasClass("result-title") . "]/a", $result)->item(0);
            $other = $page->query(".//div[" . self::hasClass("result-footer") . "]/a[" . self::hasClass("result-open-newtab") . " or " . self::hasClass("result-open") . "]", $result)->item(0);
            $this->assertNotNull($other);
            $this->assertSame($title->getAttribute("href"), $other->getAttribute("href"));
            $this->assertSame("In neuem Tab öffnen", $this->label($page, $other, "long"));
            $this->assertSame("Neuer Tab", $this->label($page, $other, "short"));
        }
    }

    private function label(DOMXPath $page, DOMElement $action, string $length): string
    {
        return trim($page->query("./span[" . self::hasClass("action-label-" . $length) . "]", $action)->item(0)?->textContent ?? "");
    }

    public function testGroupedResultsCanBeOpenedAnonymously(): void
    {
        $page = $this->webPage();

        $grouped = iterator_to_array($page->query("//div[" . self::hasClass("inherited-result") . "]"));
        $this->assertCount(2, $grouped, "brave-web.json groups two results under the espresso one.");
        foreach ($grouped as $member) {
            $link = $page->query(".//a[" . self::hasClass("result-open-proxy--icon") . "]", $member)->item(0);
            $this->assertNotNull($link);
            $this->assertSame("Anonym öffnen", $link->getAttribute("aria-label"));
        }
    }

    public function testFoldedResultsCanBeOpenedAnonymously(): void
    {
        $page = $this->page("eingabe=" . urlencode("gebrauchte waschmaschine") . "&focus=web", [
            "brave" => $this->engineFixture("brave-web-same-host.json"),
        ]);

        $folded = iterator_to_array($page->query("//details[" . self::hasClass("result-more-from-host") . "]/ul/li"));
        $this->assertCount(10, $folded);
        foreach ($folded as $member) {
            $this->assertSame(1, $page->query(".//a[" . self::hasClass("result-open-proxy--icon") . "]", $member)->length);
        }
    }

    public function testNewsAndVideoCardsCanBeOpenedAnonymously(): void
    {
        $page = $this->webPage();

        $cards = iterator_to_array($page->query("//div[" . self::hasClass("inline-result") . "]"));
        $this->assertNotEmpty($cards, "brave-web.json carries news and videos.");
        foreach ($cards as $card) {
            $link = $page->query(".//a[" . self::hasClass("result-open-proxy--short") . "]", $card)->item(0);
            $this->assertNotNull($link);
            $this->assertSame("Anonym", trim($link->textContent));
        }
    }

    public function testTheImageDetailsOfferToOpenThePageAnonymously(): void
    {
        $page = $this->page("eingabe=kaffee&focus=bilder", [
            "brave_images" => $this->engineFixture("brave-images.json"),
            "serper_images" => "no-result",
        ]);

        $actions = iterator_to_array($page->query("//div[" . self::hasClass("actions") . "]"));
        $this->assertNotEmpty($actions);
        foreach ($actions as $row) {
            $link = $page->query("./a[" . self::hasClass("result-open-proxy") . "]", $row)->item(0);
            $this->assertNotNull($link);
            $this->assertSame("Seite anonym öffnen", $this->label($page, $link, "long"));
        }
    }

    /**
     * A page that is itself being viewed through the proxy has nothing to open
     * anonymously: it already is.
     */
    public function testNothingOffersTheProxyOnAPageAlreadyViewedThroughIt(): void
    {
        $page = $this->webPage(["is-proxy" => "true"]);

        $this->assertSame(0, $page->query("//a[" . self::hasClass("result-open-proxy") . "]")->length);
    }
}
