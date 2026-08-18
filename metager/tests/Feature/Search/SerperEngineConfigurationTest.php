<?php

namespace Tests\Feature\Search;

use App\Models\Configuration\Searchengines;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * What every Serper endpoint has to agree on, asserted across all four.
 *
 * The four Serper parsers were copies of each other, and the copying is visible
 * in what they got wrong: three of the four built their *second page* as the
 * wrong class. `SerperNews` said `new Brave(...)` — page two of a news search
 * handed to Brave's web parser, which looks for a property the response does
 * not have — and `SerperImages`/`SerperShopping` said `new Serper(...)`, the
 * same defect one step less obvious. Only `serper_web` paginated as itself.
 *
 * Nothing about that is visible from a result page: the first page is correct
 * in every case, and the failure is an entry in the log during load-more. So it
 * is asserted here, on the engine object, which is where it is decidable.
 */
class SerperEngineConfigurationTest extends TestCase
{
    use FakesSearchEngines;

    /** The default page size Serper assumes when `num` is not set. */
    private const PAGE_SIZE = 10;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     *         fokus, engine, response property, parser class
     */
    public static function endpoints(): array
    {
        return [
            "web" => ["web", "serper_web", "organic", \app\Models\parserSkripte\Serper::class],
            "images" => ["bilder", "serper_images", "images", \app\Models\parserSkripte\SerperImages::class],
            "news" => ["nachrichten", "serper_news", "news", \app\Models\parserSkripte\SerperNews::class],
            "shopping" => ["produkte", "serper_shopping", "shopping", \app\Models\parserSkripte\SerperShopping::class],
        ];
    }

    /**
     * A full page of results, so `getNext()` asks for another one. Serper
     * reports no total, so "this page came back full" is the only signal there
     * is, and a short page means there is no next page to build.
     */
    private function fullPage(string $property): string
    {
        $entries = [];
        for ($i = 0; $i < self::PAGE_SIZE; $i++) {
            $entries[] = [
                "title" => "Kaffee $i",
                "link" => "https://example.org/kaffee/$i",
                "snippet" => "Ein Ergebnis.",
                "imageUrl" => "https://example.org/kaffee/$i.jpg",
                "thumbnailUrl" => "https://example.org/kaffee/$i-thumb.jpg",
                "thumbnailWidth" => 100,
                "thumbnailHeight" => 100,
                "imageWidth" => 800,
                "imageHeight" => 600,
                "price" => "1,00 €",
                "delivery" => "Kostenloser Versand",
            ];
        }

        return json_encode([$property => $entries]);
    }

    private function engineAfterSearch(string $fokus, string $engine, string $property): \App\Models\Searchengine
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([$engine => $this->fullPage($property)]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=$fokus");

        $engines = app(Searchengines::class)->sumas;
        $this->assertArrayHasKey($engine, (array) $engines, "The $fokus search never built $engine.");

        return ((array) $engines)[$engine];
    }

    /**
     * The second page of a Serper search is parsed by the same class as the
     * first. `new static` is what guarantees it; before the shared base each
     * class named a class by hand and three named the wrong one.
     */
    #[DataProvider("endpoints")]
    public function testEveryEndpointPaginatesAsItself(string $fokus, string $engine, string $property, string $parser): void
    {
        $suma = $this->engineAfterSearch($fokus, $engine, $property);

        $this->assertNotNull($suma->next, "$engine got a full page and asked for no second one.");
        $this->assertInstanceOf($parser, $suma->next, "The second page of $engine would be parsed by the wrong class.");
    }

    /**
     * The next page is the next page, not the same one again.
     */
    #[DataProvider("endpoints")]
    public function testTheNextPageAsksForThePageAfterThisOne(string $fokus, string $engine, string $property, string $parser): void
    {
        $suma = $this->engineAfterSearch($fokus, $engine, $property);

        $this->assertSame(2, $suma->next->configuration->getParameter->page);
    }

    /**
     * Serper is POST-with-a-JSON-body on every endpoint. That used to be set in
     * each parser's constructor, where `SearchEngineRegistry` could not see it;
     * it is config now, so this asserts the move did not quietly turn Serper
     * into a GET engine.
     */
    #[DataProvider("endpoints")]
    public function testEveryEndpointPostsItsQuery(string $fokus, string $engine, string $property, string $parser): void
    {
        $suma = $this->engineAfterSearch($fokus, $engine, $property);

        $this->assertSame("post_json", $suma->configuration->method);
    }

    /**
     * The display name reaches the static config, which is the only thing the
     * app's settings schema reads. Stated once in SerperBase::INFOS now, rather
     * than in four constructors the registry cannot see.
     */
    #[DataProvider("endpoints")]
    public function testEveryEndpointReportsItsDisplayName(string $fokus, string $engine, string $property, string $parser): void
    {
        $suma = $this->engineAfterSearch($fokus, $engine, $property);

        $this->assertSame("Serper", $suma->configuration->infos->displayName);
        $this->assertSame("Google", $suma->configuration->infos->indexName);
    }
}
