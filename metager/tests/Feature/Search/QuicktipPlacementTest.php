<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Where the quicktips go, at every width.
 *
 * They used to be a column beside the results and nothing else, and that
 * column is hidden below 1350px — so on a tablet or a phone there were no
 * quicktips at all. Now the column stays for wide screens and the same
 * quicktips are also rendered into the result list, after the second result;
 * CSS shows one of the two (result-page.less). Both copies are asserted here,
 * because without JavaScript there is no other way for them to get there.
 *
 * And not every quicktip answers the query: the saying and the MetaGer tip
 * (Quicktip::EXTRA_TYPES) stand next to any search. Those come after the
 * answers, drawn quieter — at the end of the column, and before the pager in
 * the list. Not folded away: they are short, and there is room for them.
 */
class QuicktipPlacementTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function feed(array $types): string
    {
        $entries = array_map(
            fn(string $type) => "<entry><mg:type>{$type}</mg:type><title>Quicktip {$type}</title>"
                . '<link href="https://example.org/' . $type . '"/><content>Inhalt ' . $type . '</content></entry>',
            $types
        );

        return '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:mg="http://metager.de/opensearch/quicktips/">'
            . implode("", $entries) . '</feed>';
    }

    private function engines(?string $quicktips): array
    {
        return [
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
            "Quicktips" => $quicktips ?? fn() => null,
        ];
    }

    private function page(array $types, string $extraQuery = ""): DOMXPath
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses($this->engines($this->feed($types)));

        $response = $this->get("/de-DE/meta/meta.ger3?eingabe=" . uniqid("kaffee") . "&focus=web" . $extraQuery);
        $response->assertOk();

        return $this->xpath($response->getContent());
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="utf-8"?>' . $html);

        return new DOMXPath($document);
    }

    private static function hasClass(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
    }

    /** @return list<string> */
    private function types(DOMXPath $page, string $path): array
    {
        return array_map(
            fn($node) => $node->getAttribute("type"),
            iterator_to_array($page->query($path))
        );
    }

    public function testTheColumnShowsTheAnswersThenTheExtras(): void
    {
        $page = $this->page(["tip", "wikipedia", "spruch"]);

        $this->assertSame(
            ["wikipedia"],
            $this->types($page, "//div[@id='quicktips']/div[" . self::hasClass("quicktip") . "]")
        );
        $this->assertSame(
            ["spruch", "tip"],
            $this->types($page, "//div[@id='quicktips']/div[" . self::hasClass("quicktip-extras") . "]/div[" . self::hasClass("quicktip") . "]")
        );
        $this->assertSame(0, $page->query("//div[@id='quicktips']//details")->length, "There is room for the extras; they are not folded away.");
    }

    public function testTheListCarriesTheAnswersAfterTheSecondResult(): void
    {
        $page = $this->page(["tip", "wikipedia", "spruch"]);

        $children = iterator_to_array($page->query("//div[@id='results']/*"));
        $section = $page->query("//div[@id='results']/section[" . self::hasClass("quicktips-inline") . "]")->item(0);
        $this->assertNotNull($section, "Below 1350px the list is the only place quicktips can show.");

        $resultsBefore = 0;
        foreach ($children as $child) {
            if ($child->isSameNode($section)) {
                break;
            }
            if (preg_match('/\bresult\b/', $child->getAttribute("class")) && !str_contains($child->getAttribute("class"), "ab-hint")) {
                $resultsBefore++;
            }
        }
        $this->assertSame(2, $resultsBefore);
        $this->assertSame(["wikipedia"], $this->types($page, "//section[" . self::hasClass("quicktips-inline") . "]/div[" . self::hasClass("quicktip") . "]"));
    }

    public function testTheListCarriesTheExtrasBeforeThePager(): void
    {
        $page = $this->page(["tip", "wikipedia", "spruch"]);

        $extras = $page->query("//div[@id='results']/div[" . self::hasClass("quicktips-inline-extras") . "]")->item(0);
        $this->assertNotNull($extras);
        $this->assertSame(
            ["spruch", "tip"],
            $this->types($page, "//div[" . self::hasClass("quicktips-inline-extras") . "]/div[" . self::hasClass("quicktip-extras") . "]/div[" . self::hasClass("quicktip") . "]")
        );

        // Only the pager may follow, and it renders nothing when there is no
        // further page — as here.
        $this->assertSame(0, $page->query(
            "following-sibling::div[" . self::hasClass("result") . "]",
            $extras
        )->length, "The extras are the end of the list, after every result.");
    }

    public function testOnlyExtrasPutNothingBetweenTheResults(): void
    {
        $page = $this->page(["spruch"]);

        $this->assertSame(0, $page->query("//section[" . self::hasClass("quicktips-inline") . "]")->length);
        $this->assertSame(1, $page->query("//div[" . self::hasClass("quicktips-inline-extras") . "]")->length);
    }

    public function testTurningQuicktipsOffRemovesBothCopies(): void
    {
        $page = $this->page(["wikipedia", "spruch"], "&quicktips=off");

        $this->assertSame(0, $page->query("//div[" . self::hasClass("quicktip") . "]")->length);
        $this->assertSame(0, $page->query("//*[" . self::hasClass("quicktips-inline") . "]")->length);
    }

    /**
     * Quicktips that arrive after the page come with the next load-more poll,
     * and that poll sends the list as well: the in-list copy is part of it, and
     * resultpage/patch.js would otherwise never see it — or, on a later poll,
     * take it out again as gone.
     */
    public function testQuicktipsArrivingLateComeWithTheList(): void
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses($this->engines(null));
        $query = "eingabe=" . uniqid("kaffee") . "&focus=web";

        $page = $this->get("/meta/meta.ger3?" . $query);
        $page->assertOk();
        $this->assertStringNotContainsString("quicktips-inline", $page->getContent());
        preg_match('/name="searchkey" content="([^"]+)"/', $page->getContent(), $match);

        $fetcher->deliverLate("Quicktips", $this->feed(["wikipedia"]));
        $payload = $this->get("/meta/loadMore?" . $query . "&loadMore=loader_" . $match[1] . "&script=yes")->json();

        $this->assertArrayHasKey("quicktips", $payload);
        $this->assertArrayHasKey("results", $payload, "The list did not come along, so the in-list copy never reaches the page.");
        $this->assertSame(
            ["wikipedia"],
            $this->types($this->xpath($payload["results"]), "//section[" . self::hasClass("quicktips-inline") . "]/div[" . self::hasClass("quicktip") . "]")
        );
    }
}
