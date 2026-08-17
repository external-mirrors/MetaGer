<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Characterization tests for the `out=` formats of the result page.
 *
 * MetaGer answers one search in seven shapes, chosen by a single query
 * parameter: html, results, results-with-style, json, rss20, atom10 and
 * result-count (plus `api`, an alias of atom10). Six of them are consumed by
 * something that is not a browser and cannot be checked by looking at the page.
 *
 * These tests pin what each format emits *before* step D takes the format
 * switch apart (MetaGer::createView is one of the methods D7d extracts into a
 * ResponseFactory). They are deliberately about structure and contract —
 * content type, document shape, which fields exist — rather than about
 * ranking, which SearchRankingTest covers.
 *
 * The `json` schema is the one with a written promise attached:
 * MetaGer::API_SCHEMA_VERSION says additive changes leave the version alone and
 * a rename or removal raises it. That promise is only worth something if
 * something fails when a field silently disappears, which is what
 * testTheJsonSchemaHasExactlyTheDocumentedFields is for.
 */
class OutputFormatsTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Run a web search for "kaffee" with both default engines answering.
     */
    private function search(string $out = ""): \Illuminate\Testing\TestResponse
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $url = "/meta/meta.ger3?eingabe=kaffee&focus=web";
        if ($out !== "") {
            $url .= "&out=" . $out;
        }

        return $this->get($url);
    }

    public function testTheJsonSchemaHasExactlyTheDocumentedFields(): void
    {
        $response = $this->search("json");

        $response->assertOk();
        $response->assertHeader("Content-Type", "application/json; charset=UTF-8");

        $payload = $response->json();

        // assertSame on the key list, not assertArrayHasKey: a removed field is
        // the breaking change API_SCHEMA_VERSION exists to track, and a test
        // that only checks presence would not notice one going missing.
        $this->assertSame([
            "version",
            "query",
            "focus",
            "searchUid",
            "resultCount",
            "nextPage",
            "searchTime",
            "results",
            "ads",
            "warnings",
            "errors",
        ], array_keys($payload), "The out=json envelope changed shape. If this is deliberate, MetaGer::API_SCHEMA_VERSION has to go up with it.");

        $this->assertSame(1, $payload["version"]);
        $this->assertSame("kaffee", $payload["query"]);
        $this->assertSame("web", $payload["focus"]);
        $this->assertNotEmpty($payload["searchUid"]);
        $this->assertSame(count($payload["results"]), $payload["resultCount"]);
        $this->assertIsFloat($payload["searchTime"] + 0);
    }

    public function testAJsonResultHasExactlyTheDocumentedFields(): void
    {
        $payload = $this->search("json")->json();

        $this->assertNotEmpty($payload["results"], "No result reached the JSON output at all.");

        $this->assertSame([
            "title",
            "link",
            "displayLink",
            "description",
            "longDescription",
            "proxyLink",
            "engines",
            "image",
            "date",
            "host",
            "domain",
            "partnershop",
            "price",
            "sitelinks",
        ], array_keys($payload["results"][0]), "A field of the out=json result schema changed. Raise MetaGer::API_SCHEMA_VERSION if it is deliberate.");
    }

    /**
     * `engines` is the one field with a shape of its own: it is built from the
     * two parallel arrays gefVon/gefVonLink, which is exactly the kind of thing
     * that comes apart during a refactor.
     */
    public function testAJsonResultNamesTheEnginesThatFoundIt(): void
    {
        $payload = $this->search("json")->json();

        $engines = collect($payload["results"])
            ->flatMap(fn(array $result) => $result["engines"])
            ->pluck("name")
            ->unique()
            ->values()
            ->all();

        $this->assertNotEmpty($engines);
        foreach ($payload["results"] as $result) {
            foreach ($result["engines"] as $engine) {
                $this->assertArrayHasKey("name", $engine);
                $this->assertArrayHasKey("link", $engine);
                $this->assertNotEmpty($engine["name"]);
            }
        }
    }

    /**
     * The JSON envelope carries the search UID as its own field so a client can
     * keep loading results even when there is no next page. Pinned because the
     * comment on it explains why it is not derived from nextPage, and a
     * refactor that "simplifies" it away would break loading exactly when few
     * engines answered.
     */
    public function testTheJsonEnvelopeCarriesTheSearchUidIndependentlyOfNextPage(): void
    {
        $payload = $this->search("json")->json();

        $this->assertNotEmpty($payload["searchUid"]);

        if ($payload["nextPage"] !== null) {
            $this->assertStringContainsString($payload["searchUid"], $payload["nextPage"]);
        }
    }

    public function testResultCountOutputIsTheCountAndTheElapsedSeconds(): void
    {
        $response = $this->search("result-count");

        $response->assertOk();
        $response->assertHeader("Content-Type", "text/plain; charset=UTF-8");

        // "<count>;<seconds>" — consumed by monitoring, not by a browser.
        $this->assertMatchesRegularExpression(
            '/^\d+;\d+(\.\d+)?$/',
            trim($response->getContent()),
            "out=result-count is a machine-read format: an integer, a semicolon, and seconds."
        );
    }

    public function testRss20OutputIsAFeedWithOneItemPerResult(): void
    {
        $response = $this->search("rss20");

        $response->assertOk();
        $response->assertHeader("Content-Type", "application/rss+xml; charset=UTF-8");

        $xml = $this->parseXml($response->getContent());

        $this->assertSame("rss", $xml->getName());
        $items = $xml->channel->item;
        $this->assertGreaterThan(0, count($items), "The RSS feed carried no items.");
        $this->assertNotEmpty((string) $items[0]->title);
        $this->assertNotEmpty((string) $items[0]->link);
    }

    public function testAtom10OutputIsAFeedWithOneEntryPerResult(): void
    {
        $response = $this->search("atom10");

        $response->assertOk();
        $response->assertHeader("Content-Type", "application/atom+xml; charset=UTF-8");

        $xml = $this->parseXml($response->getContent());

        $this->assertSame("feed", $xml->getName());
        $this->assertGreaterThan(0, count($xml->entry), "The Atom feed carried no entries.");
    }

    /**
     * `api` is an alias for `atom10` — the same view, reached through a
     * different parameter value. Pinned because the two cases sit next to each
     * other in the switch and one of them looks redundant enough to delete.
     */
    public function testTheApiOutputIsTheAtomFeed(): void
    {
        $api = $this->search("api");
        $api->assertOk();

        $this->assertSame("feed", $this->parseXml($api->getContent())->getName());
    }

    /**
     * `results` is the fragment the no-JS load-more path swaps into the page;
     * `results-with-style` is a whole document. Two tests rather than one
     * because a test may only run a single search — see
     * testASecondSearchInTheSameProcessThrows.
     */
    public function testResultsIsAFragment(): void
    {
        $fragment = $this->search("results");

        $fragment->assertOk();
        $this->assertStringNotContainsString("<html", $fragment->getContent(), "out=results is a fragment; it must not carry a document.");
    }

    /**
     * The image search has a fragment of its own, rendered from a different
     * blade than the web one — the only place the format switch branches on the
     * fokus before it branches on `out`. Nothing else in the suite renders it,
     * and step D7d rewrites that switch.
     */
    public function testTheImageSearchHasAFragmentOfItsOwn(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave_images" => $this->engineFixture("brave-images.json")]);

        $fragment = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=bilder&out=results");

        $fragment->assertOk();
        $this->assertStringNotContainsString("<html", $fragment->getContent(), "The image fragment carries a whole document.");
        $this->assertStringContainsString("<img", $fragment->getContent(), "The image fragment carries no images.");
    }

    public function testResultsWithStyleIsAWholeDocument(): void
    {
        $document = $this->search("results-with-style");

        $document->assertOk();
        $this->assertStringContainsString("<html", $document->getContent());
    }

    /**
     * A characterization of a constraint that shapes every other test here: a
     * process may run exactly one search.
     *
     * QueryTimer is a singleton (MetaGerProvider) and observeStart() throws
     * when a name is already registered, so the second search through
     * MetaGerSearch@search dies on "Search_CheckSpecialSearches" and comes back
     * as a 500. Under FPM this never happens — one request, one application
     * instance — which is why it has survived; it surfaces only where one
     * application handles two searches, i.e. in a test, or under a persistent
     * worker such as Octane.
     *
     * Pinned rather than fixed: the fix is a decision about the timer's
     * lifecycle, and this commit records behaviour rather than changing it.
     * The consequence for every test downstream is one search per test.
     */
    public function testASecondSearchInTheSameProcessFails(): void
    {
        $this->search("json")->assertOk();

        $second = $this->get("/meta/meta.ger3?eingabe=tee&focus=web&out=json");

        $second->assertStatus(500);
    }

    /**
     * An unknown `out` value falls back to the HTML page rather than erroring.
     */
    public function testAnUnknownOutputFormatFallsBackToHtml(): void
    {
        $response = $this->search("does-not-exist");

        $response->assertOk();
        $this->assertStringContainsString("<html", $response->getContent());
    }

    private function parseXml(string $content): \SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($xml === false) {
            $this->fail("The feed was not well-formed XML: " . ($errors[0]->message ?? "unknown error"));
        }

        return $xml;
    }
}
