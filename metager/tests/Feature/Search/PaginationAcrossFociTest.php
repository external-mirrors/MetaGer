<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * A `next=` uid names the engines of one search's next page, and those
 * engines belong to the fokus that search ran in.
 *
 * Searchengines::checkPagination used to swap them in whatever fokus the
 * request asked for. The uid of a web search sent with `focus=bilder` — a
 * hand-edited URL, or a client that keeps `next` while switching tabs — turned
 * Staan or Serper on inside an image search. Their results carry the
 * thumbnail as a string, ResultDeduplicator reads `->image->thumbnail` on an
 * image search, and the page or its load-more died with "Attempt to read
 * property "thumbnail" on string" (GlitchTip METAGER-1A5).
 */
class PaginationAcrossFociTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * A web search whose only next page is Staan's: its page is full, and
     * Serper's two results are not.
     */
    private function webSearchWithANextPage(): string
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
            "staan" => $this->engineFixture("staan-web.json"),
        ]);

        $page = $this->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web&staan=on&out=json");
        $page->assertOk();
        $this->assertNotNull($page->json("nextPage"), "The web search reported no next page, so this test has nothing to carry into another fokus.");

        $this->forgetRequestScopedServices();

        return $page->json("searchUid");
    }

    /** @return string[] */
    private function enabledEngines(): array
    {
        return array_values(array_map(
            fn($engine) => $engine->name,
            app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengines()
        ));
    }

    public function testAWebSearchsNextPageDoesNotEnableWebEnginesInAnImageSearch(): void
    {
        $uid = $this->webSearchWithANextPage();

        $this->fakeEngineResponses([
            "brave_images" => $this->engineFixture("brave-images.json"),
            "staan" => $this->engineFixture("staan-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=bilder&staan=on&out=json&next=" . $uid);

        $response->assertOk();
        $engines = $this->enabledEngines();
        $this->assertNotContains("staan", $engines, "A web engine from the cached next page was enabled in an image search.");
        $this->assertContains("brave_images", $engines, "An image search with a foreign next= uid should run as a fresh image search.");
    }

    /** The page two that next= exists for still works. */
    public function testTheNextPageOfTheSameFokusStillUsesTheCachedEngines(): void
    {
        $uid = $this->webSearchWithANextPage();

        $this->fakeEngineResponses([
            "staan" => $this->engineFixture("staan-web.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&staan=on&out=json&next=" . $uid);

        $response->assertOk();
        $this->assertSame(["staan"], $this->enabledEngines());
        $this->assertSame(10, (int) app(\App\Models\Configuration\Searchengines::class)->sumas["staan"]->configuration->getParameter->offset);
    }
}
