<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * The news and video rows come in a frame the page's script can put ‹ › on.
 *
 * Each row scrolls sideways, which a phone does with a swipe and a mouse barely
 * does at all. resources/js/resultpage/scroller.js adds buttons over the row's
 * edges, and needs two things from the server for that: a positioned frame
 * around the row that does not scroll with it, and the buttons' labels in the
 * page's language. Without the script the frame does nothing and the row
 * scrolls as before.
 */
class InlineRowScrollerTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function page(string $locale): DOMXPath
    {
        $this->actingAsSearchUser();
        // The video row comes after the sixth result, and brave-web.json and
        // serper-web.json do not reach that many between them.
        $mojeek = json_encode(["response" => [
            "status" => "OK",
            "head" => ["start" => 1, "return" => 8, "results" => 8],
            "results" => array_map(fn($i) => [
                "title" => "Kaffee {$i}",
                "url" => "https://mojeek-example{$i}.org/kaffee",
                "desc" => "Nur Mojeek.",
            ], range(1, 8)),
        ]]);
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
            "mojeek" => $mojeek,
        ]);

        $response = $this->get("/{$locale}/meta/meta.ger3?eingabe=kaffee&focus=web&web_engine_mojeek=on");
        $response->assertOk();

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());

        return new DOMXPath($document);
    }

    public function testEachRowSitsInAFrameDirectlyInTheResultList(): void
    {
        $page = $this->page("de-DE");

        foreach (["news", "videos"] as $id) {
            $row = $page->query("//div[@id='{$id}']")->item(0);
            $this->assertNotNull($row, "No #{$id} row on the page.");

            $frame = $row->parentNode;
            $this->assertSame("inline-scroller", $frame->getAttribute("class"), "#{$id} is not framed.");
            $this->assertSame("results", $frame->parentNode->getAttribute("id"));
            $this->assertSame("Zurückblättern", $frame->getAttribute("data-scroll-back"));
            $this->assertSame("Weiterblättern", $frame->getAttribute("data-scroll-forward"));
        }
    }

    public function testTheLabelsAreInThePagesLanguage(): void
    {
        $frame = $this->page("en-GB")->query("//div[@class='inline-scroller']")->item(0);

        $this->assertNotNull($frame);
        $this->assertSame("Scroll back", $frame->getAttribute("data-scroll-back"));
        $this->assertSame("Scroll forward", $frame->getAttribute("data-scroll-forward"));
    }
}
