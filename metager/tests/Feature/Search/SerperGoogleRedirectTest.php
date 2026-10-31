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
 * kind: a result Serper already resolved (with one plain and one goto
 * sitelink), a goto link it did not resolve, the plain-text `/url?q=`
 * spelling, and an ordinary link.
 *
 * Since Google stopped letting most of a search's links be opened (30 October
 * 2026), MetaGer no longer asks Google to resolve goto links itself — see the
 * resolver's docblock for the numbers. What is still a goto link is dropped.
 */
class SerperGoogleRedirectTest extends TestCase
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

    public function testGoogleIsNeverAskedToResolveAGotoLink(): void
    {
        $this->search();

        Http::assertNotSent(fn(Request $request) => str_contains($request->url(), "google.com/goto"));
    }

    public function testAPlainTextRedirectIsUnwrappedWithoutAskingGoogle(): void
    {
        $links = array_column($this->search(), "link");

        $this->assertContains("https://serper-example.net/klartext", $links);
        Http::assertNotSent(fn(Request $request) => str_contains($request->url(), "/url?q="));
    }

    public function testAResultLeftAsAGotoLinkIsDroppedNotShown(): void
    {
        $titles = array_column($this->search(), "title");

        $this->assertNotContains("Nur als Google-Weiterleitung", $titles);
        $this->assertContains(
            "Kaffee bei Serper",
            $titles,
            "Dropping one unresolved result must not cost the rest of the page."
        );
    }

    public function testAResultSerperAlreadyResolvedIsKeptWithoutItsGotoSitelinks(): void
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
