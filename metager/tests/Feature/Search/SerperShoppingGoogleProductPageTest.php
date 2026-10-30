<?php

namespace Tests\Feature\Search;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Serper's `/shopping` answers with Google's own product page as every
 * result's link (`https://www.google.com/search?ibp=oshop&prds=…`), not with
 * the shop. That page is not a redirect, so GoogleRedirectResolver has nothing
 * to resolve: fetched from the server it answers with Google's cookie consent,
 * and in the browser a captcha follows. The answer carries no shop URL at all.
 *
 * Shown as is, a click sends the user to Google with their IP and cookies —
 * the leak SerperGoogleRedirectTest closed for web results. Such entries are
 * dropped. A shop link Serper may send again some day is shown as before.
 *
 * tests/Fixtures/engines/serper-shopping-google-product-pages.json has one
 * entry of each, the Google one copied from a real answer.
 */
class SerperShoppingGoogleProductPageTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function search(): array
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "serper_shopping" => $this->engineFixture("serper-shopping-google-product-pages.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffeemaschine&focus=produkte&out=json");
        $response->assertOk();

        return $response->json("results");
    }

    public function testAProductLinkingToGooglesProductPageIsNotShown(): void
    {
        $results = $this->search();

        $this->assertNotContains("Braun KF1100WH", array_column($results, "title"));
        foreach ($results as $result) {
            $this->assertDoesNotMatchRegularExpression(
                '~^https?://(www\.)?google\.[a-z.]+/~',
                $result["link"],
                "A product still sends the user to Google: " . $result["title"]
            );
        }
    }

    public function testAProductLinkingToTheShopIsShown(): void
    {
        $links = array_column($this->search(), "link");

        $this->assertContains("https://serper-example.net/filterkaffeemaschine", $links);
    }

    public function testGoogleIsNotAskedAboutTheProductPage(): void
    {
        $this->search();

        Http::assertNotSent(fn(Request $request) => str_contains($request->host(), "google."));
    }
}
