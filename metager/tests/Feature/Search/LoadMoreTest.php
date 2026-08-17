<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for the load-more path.
 *
 * MetaGer answers a search before every engine has replied — waitForMainResults
 * blocks for at most six seconds — and then keeps loading. The client polls
 * `/meta/loadMore` with the search UID, and the server rebuilds the whole
 * search from a cached object graph ("loader_<uid>": the Authorization, the
 * Searchengines, the SearchSettings and the Quicktips, serialized wholesale)
 * before collecting whatever has since arrived.
 *
 * That cached graph is the fragile part, and it is fragile in a way that is
 * invisible on the first page: a change to any of those four classes — or to
 * Laravel's rules about unserializing objects out of the cache, which is
 * exactly what the `cache.serializable_classes` item in step A3 was about —
 * breaks loading more results while the initial page still looks perfect.
 *
 * Hence these tests. They cover both shapes of the response: the HTML fragment
 * the website consumes, and the API schema a native client gets with out=json.
 */
class LoadMoreTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Run a search, then follow it with the load-more request the browser would
     * make: the same URL with the path swapped and the search key appended.
     * See resources/js/scriptResultPage.js.
     */
    private function searchThenLoadMore(string $out = ""): \Illuminate\Testing\TestResponse
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $query = "eingabe=kaffee&focus=web" . ($out !== "" ? "&out=" . $out : "");

        $page = $this->get("/meta/meta.ger3?" . $query);
        $page->assertOk();

        $searchkey = $this->searchKeyOf($page->getContent(), $out);

        return $this->get("/meta/loadMore?" . $query . "&loadMore=loader_" . $searchkey . "&script=yes");
    }

    private function searchKeyOf(string $content, string $out): string
    {
        if ($out === "json") {
            return json_decode($content, true)["searchUid"];
        }

        $this->assertMatchesRegularExpression('/name="searchkey" content="([^"]+)"/', $content, "The result page no longer carries a search key, so nothing can load more results.");
        preg_match('/name="searchkey" content="([^"]+)"/', $content, $match);

        return $match[1];
    }

    public function testLoadMoreReturnsTheFieldsTheWebsiteReadsFromIt(): void
    {
        $response = $this->searchThenLoadMore();

        $response->assertOk();
        $payload = $response->json();

        // scriptResultPage.js reads exactly these; a missing one is a silently
        // broken page rather than an error.
        $this->assertArrayHasKey("finished", $payload);
        $this->assertArrayHasKey("results", $payload);
        $this->assertArrayHasKey("nextSearchLink", $payload);
        $this->assertArrayHasKey("imagesearch", $payload);
        $this->assertArrayHasKey("engines", $payload);
    }

    /**
     * `results` is a rendered HTML fragment, not data. Pinned because it is the
     * reason the load-more response cannot simply be replaced by the JSON one.
     */
    public function testLoadMoreDeliversResultsAsRenderedHtml(): void
    {
        $payload = $this->searchThenLoadMore()->json();

        $this->assertIsString($payload["results"]);
        $this->assertStringContainsString("https://example.org/kaffee", $payload["results"]);
    }

    /**
     * `engines` reports which engines have answered, so a client can show what
     * it is still waiting for.
     */
    public function testLoadMoreReportsWhichEnginesHaveAnswered(): void
    {
        $payload = $this->searchThenLoadMore()->json();

        $this->assertIsArray($payload["engines"]);
        $this->assertArrayHasKey("brave", $payload["engines"]);
        foreach ($payload["engines"] as $name => $loaded) {
            $this->assertIsBool($loaded, "engines[$name] must be a boolean; a client branches on it.");
        }
    }

    /**
     * With out=json the load-more path answers in the same schema as the search
     * itself, plus `finished` and `engines`. The point of that is that a native
     * client never has to parse the HTML page — so the schema really has to
     * match, which is what this asserts.
     */
    public function testLoadMoreWithOutJsonAnswersInTheApiSchema(): void
    {
        $response = $this->searchThenLoadMore("json");

        $response->assertOk();
        $response->assertHeader("Content-Type", "application/json; charset=UTF-8");

        $payload = $response->json();

        foreach (["version", "query", "focus", "searchUid", "resultCount", "results", "warnings", "errors"] as $field) {
            $this->assertArrayHasKey($field, $payload, "The out=json load-more response dropped the `$field` field of the search schema.");
        }

        $this->assertArrayHasKey("finished", $payload);
        $this->assertArrayHasKey("engines", $payload);
        $this->assertSame(1, $payload["version"]);
    }

    /**
     * The JSON load-more response carries the complete ranked list rather than
     * only what is new. Loading more can reorder results, and a client handed a
     * difference would have nowhere to put it.
     */
    public function testLoadMoreWithOutJsonReturnsTheWholeRankedListNotADifference(): void
    {
        $payload = $this->searchThenLoadMore("json")->json();

        $this->assertNotEmpty($payload["results"]);
        $this->assertSame(count($payload["results"]), $payload["resultCount"]);
    }

    /**
     * An expired or unknown search key is answered with `finished: true`, not
     * with an error. The search state lives an hour; a browser left open longer
     * than that must stop polling rather than break.
     */
    public function testAnUnknownSearchKeyIsAnsweredAsFinished(): void
    {
        $this->actingAsSearchUser();

        $response = $this->get("/meta/loadMore?eingabe=kaffee&focus=web&loadMore=loader_does-not-exist&script=yes");

        $response->assertOk();
        $this->assertTrue($response->json("finished"));
    }

    /**
     * Without `script=yes` the route returns nothing at all.
     *
     * Characterizing rather than endorsing: loadMore() has an if with no else,
     * so the controller falls off the end and Laravel turns the null into an
     * empty 200. The comment there describes three request shapes and only two
     * are implemented — the no-JS variant it mentions is not on this route.
     */
    public function testLoadMoreWithoutTheScriptFlagReturnsAnEmptyResponse(): void
    {
        $this->actingAsSearchUser();

        $response = $this->get("/meta/loadMore?eingabe=kaffee&focus=web&loadMore=loader_whatever");

        $response->assertOk();
        $this->assertSame("", $response->getContent());
    }

    /**
     * The load-more response is per-user and must not be stored by a shared
     * cache, for the same reason the result page must not be.
     */
    public function testTheLoadMoreResponseIsNotPubliclyCacheable(): void
    {
        $response = $this->searchThenLoadMore("json");

        $this->assertStringContainsString(
            "private",
            strtolower($response->headers->get("Cache-Control") ?? ""),
            "Load-more carries the caller's own results and authorization."
        );
    }
}
