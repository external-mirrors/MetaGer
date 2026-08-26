<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Not every Serper organic result carries a `snippet` -- production logged
 * repeated "A problem occurred parsing results from serper_web" / "Undefined
 * property: stdClass::$snippet" pairs, one per affected search.
 *
 * `Serper::loadResults()` read `$result->snippet` unconditionally, unlike
 * every other optional field on the same object (`date`, `thumbnail`,
 * `imageUrl`, `sitelinks`), which are all guarded with `property_exists()`.
 * Accessing the missing property raises a warning Laravel turns into a
 * catchable error, which is caught by the `try`/`catch` around the whole
 * `foreach` -- so the *entire* serper_web result page for that search was
 * discarded, not just the one result missing a snippet, including results
 * that were already parsed further down the same page.
 *
 * See tests/Fixtures/engines/serper-web-missing-snippet.json: the first
 * organic entry has no `snippet`, and a second, well-formed entry follows it.
 */
class SerperMissingSnippetTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    public function testAResultWithoutASnippetDoesNotDropTheWholePage(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "serper_web" => $this->engineFixture("serper-web-missing-snippet.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");
        $response->assertOk();

        $links = array_column($response->json("results"), "link");

        $this->assertContains(
            "https://serper-example.net/ohne-snippet",
            $links,
            "The result missing a snippet should still appear, with an empty description, not vanish."
        );
        $this->assertContains(
            "https://serper-example.net/kaffee",
            $links,
            "A well-formed result that was parsed after the broken one was lost along with it."
        );
    }
}
