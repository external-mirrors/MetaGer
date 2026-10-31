<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * The icons on Einstellungen, Filter and the date range follow the palette.
 *
 * They were black SVG files in <img>, which the dark theme cannot recolour
 * without a filter per icon, and two of them shared id="result-img-settings".
 * They are drawn inline in currentColor now, the way the rest of the result
 * page draws its icons, so they take the colour of the pill they sit in.
 */
class FilterToolbarTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    public function testTheToolbarIconsAreDrawnInTheTextColour(): void
    {
        $page = $this->resultPage();

        $this->assertSame(0, $page->query("//div[@id='options']//img")->length, "An icon in the filter area is still an <img>.");

        foreach (["//a[@id='settings-link']", "//label[@for='options-toggle']", "//label[@for='custom-date']"] as $control) {
            $icons = $page->query("{$control}/svg");
            $this->assertSame(1, $icons->length, "{$control} has no inline icon.");
            $this->assertSame("currentColor", $icons->item(0)->getAttribute("stroke"));
            $this->assertSame("true", $icons->item(0)->getAttribute("aria-hidden"));
        }
    }

    public function testTheSavedSettingCountIsItsOwnElement(): void
    {
        // A setting carried in the URL counts as saved, as one from the
        // settings page does.
        $page = $this->resultPage("&web_setting_m=fr_FR");

        $count = $page->query("//a[@id='settings-link']/span[@class='settings-count']");
        $this->assertSame(1, $count->length, "The count of saved settings is not marked up on its own.");
        $this->assertMatchesRegularExpression('/^\d+$/', trim($count->item(0)->textContent));
    }

    private function resultPage(string $query = ""): DOMXPath
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $response = $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web{$query}");
        $response->assertOk();

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());

        return new DOMXPath($document);
    }
}
