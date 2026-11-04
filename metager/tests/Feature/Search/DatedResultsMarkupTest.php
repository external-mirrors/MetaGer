<?php

namespace Tests\Feature\Search;

use Dom\Element;
use Dom\HTMLDocument;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * A result that carries a timestamp (`additionalInformation['date']`) must
 * leave the page's structure alone.
 *
 * layouts/result.blade.php opened `<span class="date">` in the description and
 * closed it with `</div>`. A browser reads that `</div>` as the end of
 * `.result-description`, so every dated result closed one element too many:
 * the first ended `.result-body`'s description early, the next ended
 * `.result-body`, then `.result`, then `#results`, `#results-container` and
 * `#resultpage-container`. Cards landed in the quicktip column and at the full
 * width of the body; "weitere Ergebnisse von …" lost its styling, which is
 * scoped to `.result > .result-more-from-host`; the in-list quicktips lost the
 * rule that hides them beside the column, so "Wussten Sie schon?" showed twice.
 *
 * Only Staan and TootNews set a timestamp. Brave and
 * Serper — the default engines and every fixture the rendering tests used —
 * set `date_string` at most, which is why the default news search looked fine
 * and why nothing caught it: Staan's tests read `out=json`.
 *
 * The page is parsed with the HTML5 parser, which recovers from bad markup the
 * way a browser does. DOMDocument's libxml parser recovers differently and
 * keeps the cards in `#results`; a test written with it would pass on the
 * broken page.
 */
class DatedResultsMarkupTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function render(string $query, array $engines): string
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses($engines);

        $response = $this->get("/de-DE/meta/meta.ger3?" . $query);
        $response->assertOk();

        return $response->getContent();
    }

    private function newsPage(): string
    {
        // The default news engines answer with nothing, so the page is
        // TootNews's alone, as on the reported page with Brave and Serper off.
        return $this->render("eingabe=kaffee&focus=nachrichten", [
            "tootnews" => $this->engineFixture("tootnews-dated.xml"),
            "brave_news" => "no-result",
            "serper_news" => "no-result",
        ]);
    }

    private function webPageWithStaan(): string
    {
        return $this->render("eingabe=kaffee&focus=web&staan=on", [
            "staan" => $this->engineFixture("staan-web.json"),
            "brave" => "no-result",
            "serper_web" => "no-result",
        ]);
    }

    /** @return list<Element> */
    private function cards(HTMLDocument $page): array
    {
        $cards = array_values(array_filter(
            iterator_to_array($page->querySelectorAll("div.result")),
            fn(Element $card) => !$card->classList->contains("ab-hint"),
        ));
        $this->assertNotEmpty($cards, "The page shows no results at all.");

        return $cards;
    }

    private function assertEveryCardStaysInTheList(string $html): void
    {
        $page = HTMLDocument::createFromString($html, LIBXML_NOERROR);

        foreach ($this->cards($page) as $index => $card) {
            $title = trim($card->querySelector(".result-title")?->textContent ?? "");
            $this->assertSame(
                "results",
                $card->parentElement?->id,
                "Result " . ($index + 1) . " (\"$title\") was pushed out of #results into "
                . $card->parentElement?->tagName . "#" . $card->parentElement?->id . ".",
            );
        }

        foreach ($page->querySelectorAll("div.ab-hint") as $index => $hint) {
            $this->assertSame("results", $hint->parentElement?->id, "The adblocker hint after result " . ($index + 1) . " was pushed out of #results.");
        }
    }

    public function testDatedNewsResultsStayInTheResultList(): void
    {
        $this->assertEveryCardStaysInTheList($this->newsPage());
    }

    public function testDatedWebResultsStayInTheResultList(): void
    {
        $this->assertEveryCardStaysInTheList($this->webPageWithStaan());
    }

    public function testTheDescriptionStaysInsideItsCard(): void
    {
        $page = HTMLDocument::createFromString($this->newsPage(), LIBXML_NOERROR);

        foreach ($this->cards($page) as $card) {
            $description = $card->querySelector(".result-description");
            $this->assertNotNull($description);
            $this->assertMatchesRegularExpression("/Beschreibung der Kaffeemeldung \d+\./", $description->textContent);
        }
    }

    public function testFoldedResultsStayUnderTheResultTheyBelongTo(): void
    {
        $page = HTMLDocument::createFromString($this->newsPage(), LIBXML_NOERROR);

        $folded = iterator_to_array($page->querySelectorAll("details.result-more-from-host"));
        $this->assertCount(1, $folded, "The fixture has three tagesschau.de results; the third is folded.");
        $this->assertTrue($folded[0]->parentElement->classList->contains("result"), "The folded results left their card.");
    }

    /**
     * A news card puts the date beside the host, under the title. The
     * description repeated it, so every card showed the same date twice, one
     * line apart.
     */
    public function testANewsCardShowsItsDateOnce(): void
    {
        $page = HTMLDocument::createFromString($this->newsPage(), LIBXML_NOERROR);

        foreach ($this->cards($page) as $card) {
            $this->assertCount(1, $card->querySelectorAll(".date, .date-string"));
            $this->assertNotNull($card->querySelector(".result-subheadline > .date"));
        }
    }

    /**
     * A web card has no date in its header, so the description leads with it,
     * set off by a dash. The dash sits outside `span.date`: relative-time.js
     * replaces that span's text with "vor 5 Tagen", and took the dash with it.
     */
    public function testAWebCardLeadsItsDescriptionWithTheDate(): void
    {
        $page = HTMLDocument::createFromString($this->webPageWithStaan(), LIBXML_NOERROR);

        $dated = array_values(array_filter(
            $this->cards($page),
            fn(Element $card) => $card->querySelector(".result-description > span.date") !== null,
        ));
        $this->assertCount(1, $dated, "staan-web.json dates one result.");

        $date = $dated[0]->querySelector(".result-description > span.date");
        $this->assertStringNotContainsString("—", $date->textContent);
        $this->assertMatchesRegularExpression("/^\s*—\s*Alles über Kaffee/u", $date->nextSibling->textContent);
    }

    /**
     * A browser recovers from a stray end tag without a trace in the DOM
     * whenever the element is the last in its parent, so the tree cannot tell.
     * The rendered markup can. `<button>…</label>` on the image page is one of
     * those: harmless only for as long as nothing follows the button.
     *
     * @return array<string, array{0: string, 1: array<string, string>}>
     */
    public static function pages(): array
    {
        return [
            "news, dated" => ["eingabe=kaffee&focus=nachrichten", ["tootnews" => "tootnews-dated.xml", "brave_news" => null, "serper_news" => null]],
            "web, Staan" => ["eingabe=kaffee&focus=web&staan=on", ["staan" => "staan-web.json", "brave" => null, "serper_web" => null]],
            "web, Brave and Serper" => ["eingabe=kaffee&focus=web", ["brave" => "brave-web.json", "serper_web" => "serper-web.json"]],
            "images" => ["eingabe=kaffee&focus=bilder", ["brave_images" => "brave-images.json", "serper_images" => null]],
        ];
    }

    /** @dataProvider pages */
    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function testEveryOpenedElementIsClosed(string $query, array $fixtures): void
    {
        $engines = array_map(fn(?string $fixture) => $fixture === null ? "no-result" : $this->engineFixture($fixture), $fixtures);
        $html = $this->render($query, $engines);

        // Only the markup: scripts and inline styles may spell out tags.
        $markup = preg_replace(["#<script\b.*?</script>#si", "#<style\b.*?</style>#si", "#<!--.*?-->#s"], "", $html);

        foreach (["div", "span", "button", "label", "details", "summary", "section", "ul", "li", "a", "h2"] as $tag) {
            $opened = preg_match_all("#<$tag(?=[\s>/])#i", $markup);
            $closed = preg_match_all("#</$tag\s*>#i", $markup);
            $this->assertSame($opened, $closed, "<$tag> opened $opened times and closed $closed times.");
        }
    }
}
