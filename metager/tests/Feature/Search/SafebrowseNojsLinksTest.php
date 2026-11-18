<?php

namespace Tests\Feature\Search;

use DOMDocument;
use DOMXPath;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * "Anonym öffnen" for a browser without JavaScript: the link's href is SafeBrowse's page view
 * without JavaScript (`/proxy/nojs?url=…&sid=…`), and every result opened from one results page
 * carries the same `sid`, so SafeBrowse can keep them in one session when cookies are blocked.
 *
 * The `sid` survives searching on: the search bar and the page's own links carry it, and the next
 * page takes it over — but only when it was reached from one of our own pages, so a result page
 * URL passed on to someone else does not hand them the sender's session. How the sid is chosen
 * is in tests/Unit/Http/SafebrowseNojsTest; this is the wiring into the page.
 */
class SafebrowseNojsLinksTest extends TestCase
{
    use FakesSearchEngines;

    private const SID = "aB3dE5gH7jK9mN1pQ3sT5vW7";

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function page(string $query = "", array $headers = []): DOMXPath
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $response = $this->withHeaders($headers)->get("/de-DE/meta/meta.ger3?eingabe=kaffee&focus=web" . $query);
        $response->assertOk();

        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());

        return new DOMXPath($document);
    }

    /** @return list<array<string, string>> the query of every shield's href */
    private function shields(DOMXPath $page): array
    {
        $shields = [];
        foreach ($page->query("//a[contains(@class, 'result-open-proxy')]") as $shield) {
            $href = $shield->getAttribute("href");
            $this->assertMatchesRegularExpression("~^(https?://[^/]+)?/de-DE/proxy/nojs\\?~", $href);
            parse_str((string) parse_url($href, PHP_URL_QUERY), $query);
            $shields[] = $query;
        }
        $this->assertNotEmpty($shields, "The page offers no way to open a result anonymously.");

        return $shields;
    }

    private function pageSid(DOMXPath $page): string
    {
        $sids = array_unique(array_column($this->shields($page), "sid"));
        $this->assertCount(1, $sids, "The results of one page would open in different sessions.");

        return $sids[0];
    }

    public function testTheShieldOpensTheTargetInSafebrowseWithoutJavascript(): void
    {
        $page = $this->page();

        foreach ($this->shields($page) as $query) {
            $this->assertMatchesRegularExpression("~^https?://~", $query["url"]);
            $this->assertMatchesRegularExpression("/^[A-Za-z0-9_-]{16,64}$/", $query["sid"]);
        }
        // The JavaScript path is the same as before: SafeBrowse in the hash.
        $shield = $page->query("//a[contains(@class, 'result-open-proxy')]")->item(0);
        $this->assertStringContainsString("/proxy#url=", $shield->getAttribute("data-proxy-link"));
    }

    public function testTheSearchBarCarriesTheSid(): void
    {
        $page = $this->page();

        $input = $page->query("//input[@name='sid'][@form='searchForm']")->item(0);
        $this->assertNotNull($input, "A new search from the search bar would start a new session.");
        $this->assertSame($this->pageSid($page), $input->getAttribute("value"));
    }

    public function testThePagesOwnLinksCarryTheSid(): void
    {
        $page = $this->page();
        $sid = $this->pageSid($page);

        $tabs = iterator_to_array($page->query("//a[contains(@href, 'focus=nachrichten')]"));
        $this->assertNotEmpty($tabs);
        foreach ($tabs as $tab) {
            parse_str((string) parse_url($tab->getAttribute("href"), PHP_URL_QUERY), $query);
            $this->assertSame($sid, $query["sid"] ?? null);
        }
    }

    public function testASearchFromOurOwnPageKeepsTheSid(): void
    {
        $page = $this->page("&sid=" . self::SID, ["Referer" => url("/meta/meta.ger3?eingabe=tee")]);

        $this->assertSame(self::SID, $this->pageSid($page));
        $this->assertSame(self::SID, $page->query("//input[@name='sid']")->item(0)->getAttribute("value"));
    }

    /** A result page URL passed on (pasted, bookmarked, linked from elsewhere) gets its own session. */
    public function testACarriedSidIsOnlyTakenOverFromOurOwnPages(): void
    {
        $this->assertNotSame(self::SID, $this->pageSid($this->page("&sid=" . self::SID)));
    }

    /** Apps that cannot authenticate against SafeBrowse keep the old proxy and get no sid. */
    public function testAnOldAppKeepsTheOldProxy(): void
    {
        $page = $this->page("", ["mg-app" => "5.4.1"]);

        $shields = iterator_to_array($page->query("//a[contains(@class, 'result-open-proxy')]"));
        $this->assertNotEmpty($shields);
        foreach ($shields as $shield) {
            $this->assertStringStartsWith("https://proxy.metager.de/", $shield->getAttribute("href"));
        }
        $this->assertSame(0, $page->query("//input[@name='sid']")->length);
    }
}
