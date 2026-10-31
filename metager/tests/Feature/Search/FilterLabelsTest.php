<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Each dropdown in the filter box is named by the label above it.
 *
 * The labels said for="safesearch", for="freshness" and so on, and no element
 * had those ids, so a screen reader announced three unnamed dropdowns. Clicking
 * "Sprache" did not open the language list either.
 */
class FilterLabelsTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    public function testEveryFilterLabelPointsAtItsDropdown(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $response = $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web");
        $response->assertOk();

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());
        $page = new DOMXPath($document);

        $selects = $page->query("//div[@id='options-items']//select");
        $this->assertGreaterThan(0, $selects->length, "No filter dropdowns on the page.");

        foreach ($selects as $select) {
            $name = $select->getAttribute("name");
            $id = $select->getAttribute("id");
            $this->assertNotSame("", $id, "The {$name} dropdown has no id for its label.");
            $this->assertSame(1, $page->query("//*[@id='{$id}']")->length, "#{$id} is not unique on the page.");

            $label = $page->query("//label[@for='{$id}']")->item(0);
            $this->assertNotNull($label, "No label names the {$name} dropdown.");
            $this->assertNotSame("", trim($label->textContent));
        }
    }
}
