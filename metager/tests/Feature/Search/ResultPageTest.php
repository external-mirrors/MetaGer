<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for the HTML result page — the format every other one
 * exists to serve.
 *
 * The page is assembled by MetaGer::createView, which passes some twelve
 * variables into a blade that then calls back into the MetaGer object for
 * roughly seventy getters. That is the coupling step D7 has to unpick last, and
 * it is the part with no compiler to catch a mistake: a getter renamed in PHP
 * fails silently in a blade, leaving a blank spot on the page rather than an
 * error.
 *
 * So these tests assert that the things a result page must contain are on it,
 * for both foki that have their own template (web and bilder), and that the
 * search state a later request depends on is emitted. They deliberately do not
 * assert markup structure: the templates are allowed to change.
 */
class ResultPageTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function webSearch(string $query = "kaffee"): \Illuminate\Testing\TestResponse
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        return $this->get("/meta/meta.ger3?eingabe=" . urlencode($query) . "&focus=web");
    }

    public function testTheWebResultPageShowsTitleLinkAndDescription(): void
    {
        $response = $this->webSearch();

        $response->assertOk();
        // html_entity_decode in the Brave parser is why the title is decoded
        // before it reaches the page, and escaped again by blade on the way out.
        $response->assertSee("Kaffee &amp; Zubereitung", false);
        $response->assertSee("https://example.org/kaffee", false);
        $response->assertSee("Alles über Kaffee", false);
    }

    /**
     * The page names the engine behind each result. This is a stated principle
     * of the product — a metasearch that hides its sources is just a search
     * engine — and it is rendered from the same gefVon array the JSON output
     * exposes as `engines`.
     */
    public function testTheWebResultPageNamesTheEngineBehindAResult(): void
    {
        $response = $this->webSearch();

        $response->assertOk();
        $response->assertSee("Brave", false);
    }

    /**
     * The search UID is emitted into the page as a meta tag, because the
     * load-more path needs it on a follow-up request. Losing it does not break
     * the page — it breaks loading more results, one interaction later, which
     * is why it is worth a test rather than a glance.
     */
    public function testTheWebResultPageCarriesTheSearchKeyForLoadingMore(): void
    {
        $response = $this->webSearch();

        $response->assertOk();
        $response->assertSee('name="searchkey"', false);
    }

    public function testTheSearchTermIsPutBackIntoTheSearchBox(): void
    {
        $response = $this->webSearch("kaffee");

        $response->assertOk();
        $response->assertSee('value="kaffee"', false);
    }

    /**
     * The image fokus has its own template (resultpages.resultpage_images) and
     * its own deduplication rule — it compares thumbnails rather than links —
     * so it gets its own coverage rather than being assumed to follow the web
     * page.
     */
    public function testTheImageResultPageRendersThumbnails(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave_images" => $this->engineFixture("brave-images.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=bilder");

        $response->assertOk();
        $response->assertSee("Kaffeetasse auf einem Tisch", false);
    }

    /**
     * An empty query is not a result page: it redirects to the start page.
     * Pinned because the branch sits in MetaGerSearch@search above the timer
     * calls and is easy to lose when that method is broken up.
     */
    public function testAnEmptyQueryRedirectsToTheStartPage(): void
    {
        $this->actingAsSearchUser();

        $response = $this->get("/meta/meta.ger3?eingabe=&focus=web");

        $response->assertRedirect();
    }

    /**
     * A search with no results still renders a page with an explanation rather
     * than an error.
     */
    public function testASearchWithoutResultsStillRendersAPage(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $response->assertOk();
        $this->assertStringContainsString("<html", $response->getContent());
    }

    /**
     * The result page must not be cached by a shared cache: it carries the
     * caller's own results and is authorized per key.
     */
    public function testTheResultPageIsNotPubliclyCacheable(): void
    {
        $response = $this->webSearch();

        $response->assertOk();
        $this->assertStringContainsString(
            "private",
            strtolower($response->headers->get("Cache-Control") ?? ""),
            "A result page carries per-user results and must never be stored by a shared cache."
        );
    }
}
