<?php

namespace Tests\Feature\Search;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Serper returns some results as `https://www.google.com/goto?url=<token>`, and
 * those were shown as they came — so clicking one sent the user through
 * Google, with their IP and Google cookies, before reaching the page. Reported
 * by a user who noticed the Google links in the result list.
 *
 * See App\Search\GoogleRedirectResolver and SerperBase::loadResponse.
 * tests/Fixtures/engines/serper-web-google-redirects.json has one entry of each
 * kind: a resolvable goto link (with sitelinks, one of them unresolvable), an
 * unresolvable one, the plain-text `/url?q=` spelling, and an ordinary link.
 */
class SerperGoogleRedirectTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Registered before actingAsSearchUser, whose catch-all `*` stub would
     * otherwise answer first: Http::fake appends, and the first match wins.
     */
    private function fakeGoogle(): void
    {
        Http::fake([
            "www.google.com/goto?url=CAESggEB-resolvable" => Http::response("", 302, [
                "Location" => "https://economistwritingeveryday.com/2025/11/17/is-art-anti-capitalist/",
            ]),
            "www.google.com/goto?url=CAESggEB-sitelink" => Http::response("", 302, [
                "Location" => "https://economistwritingeveryday.com/archive/",
            ]),
            // What a rate-limited lookup looks like: a redirect, but to Google's
            // own captcha rather than to the result.
            "www.google.com/goto?url=CAESggEB-unresolvable-sitelink" => Http::response("", 302, [
                "Location" => "https://www.google.com/sorry/index?continue=https://www.google.com/goto",
            ]),
            "www.google.com/goto?url=CAESggEB-unresolvable" => Http::response("<html></html>", 200),
        ]);
    }

    private function search(): array
    {
        $this->fakeGoogle();
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "serper_web" => $this->engineFixture("serper-web-google-redirects.json"),
        ]);

        $response = $this->get("/meta/meta.ger3?eingabe=kunst&focus=web&out=json");
        $response->assertOk();

        return $response->json("results");
    }

    public function testNoResultLinksToAGoogleRedirect(): void
    {
        $results = $this->search();

        foreach ($results as $result) {
            $this->assertDoesNotMatchRegularExpression(
                '~^https?://(www\.)?google\.[a-z.]+/(goto|url)\b~',
                $result["link"],
                "A result still sends the user through Google: " . $result["title"]
            );
        }
    }

    public function testAGotoLinkIsReplacedByWhereGoogleRedirectsTo(): void
    {
        $links = array_column($this->search(), "link");

        $this->assertContains("https://economistwritingeveryday.com/2025/11/17/is-art-anti-capitalist/", $links);
    }

    public function testAPlainTextRedirectIsUnwrappedWithoutAskingGoogle(): void
    {
        $links = array_column($this->search(), "link");

        $this->assertContains("https://serper-example.net/klartext", $links);
        Http::assertNotSent(fn(Request $request) => str_contains($request->url(), "/url?q="));
    }

    public function testAResultWhoseRedirectCannotBeResolvedIsDroppedNotShown(): void
    {
        $results = $this->search();
        $titles = array_column($results, "title");

        $this->assertNotContains("Von Google nicht aufgelöst", $titles);
        $this->assertContains(
            "Kaffee bei Serper",
            $titles,
            "Dropping one unresolvable result must not cost the rest of the page."
        );
    }

    public function testSitelinksAreResolvedAndUnresolvableOnesDropped(): void
    {
        $this->search();

        $engine = app(\App\MetaGer::class)->getResults();
        $result = collect($engine)->first(fn($r) => $r->titel === "Is art anti-capitalist?");
        $this->assertNotNull($result);

        $buttons = collect($result->deepResults["buttons"] ?? [])->map(fn($button) => [$button->title, $button->link])->all();
        $this->assertSame([["Archiv", "https://economistwritingeveryday.com/archive/"]], $buttons);
    }

    public function testAnAnswerWithoutRedirectsCostsNoRequestToGoogle(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        Http::assertNotSent(fn(Request $request) => str_contains($request->url(), "google."));
    }
}
