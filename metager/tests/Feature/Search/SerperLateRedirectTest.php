<?php

namespace Tests\Feature\Search;

use App\Models\parserSkripte\Base\SerperBase;
use Tests\Concerns\FakesSearchEngines;
use Tests\Support\FakeFetcher;
use Tests\TestCase;

/**
 * Results Serper returns as Google redirects are dropped from the page and
 * brought back by load-more, once Serper's `/resolve` has turned them into real
 * addresses.
 *
 * See App\Search\LateRedirectResolution. The fixture,
 * tests/Fixtures/engines/serper-web-google-redirects.json, has two goto links
 * only `/resolve` can open: an organic result ("Nur als Google-Weiterleitung")
 * and a sitelink ("Nur bei Google") of a result that is already resolved.
 */
class SerperLateRedirectTest extends TestCase
{
    use FakesSearchEngines;

    private const UNRESOLVED = "https://www.google.com/goto?url=CAESggEB-unresolved";
    private const SITELINK = "https://www.google.com/goto?url=CAESggEB-sitelink";

    private const TARGETS = [
        self::UNRESOLVED => "https://serper-example.net/weitergeleitet",
        self::SITELINK => "https://economistwritingeveryday.com/about/",
    ];

    private FakeFetcher $fetcher;
    private string $query = "eingabe=kunst&focus=web&out=json";
    private string $searchUid;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @param \Closure(string): ?string $resolve goto link => the body /resolve answers, or null for not yet
     */
    private function search(\Closure $resolve): array
    {
        $this->actingAsSearchUser();
        $this->fetcher = $this->fakeEngineResponses([
            "serper_web" => $this->engineFixture("serper-web-google-redirects.json"),
            SerperBase::RESOLVE_MISSION_NAME => fn(array $mission) => $resolve($this->linkOf($mission)),
        ]);

        $response = $this->get("/meta/meta.ger3?" . $this->query);
        $response->assertOk();
        $this->searchUid = $response->json("searchUid");

        return $response->json();
    }

    private function loadMore(): array
    {
        $this->forgetRequestScopedServices();
        $response = $this->get("/meta/loadMore?" . $this->query . "&loadMore=loader_" . $this->searchUid . "&script=yes");
        $response->assertOk();

        return $response->json();
    }

    private static function resolvedTo(string $target): string
    {
        return json_encode(["organic" => [["link" => $target]]]);
    }

    private function linkOf(array $mission): string
    {
        return json_decode($mission["curlopts"][CURLOPT_POSTFIELDS], true)["organic"][0]["link"];
    }

    /** @return list<array<string, mixed>> */
    private function resolveMissions(): array
    {
        return array_values(array_filter(
            $this->fetcher->missions(),
            fn(array $mission) => $mission["name"] === SerperBase::RESOLVE_MISSION_NAME
        ));
    }

    private static function linksOf(array $payload): array
    {
        return array_column($payload["results"], "link", "title");
    }

    public function testTheSearchItselfAsksSerperForNothing(): void
    {
        $this->search(fn() => null);

        $this->assertSame([], $this->resolveMissions(), "Only a client that comes back for the results should cost a /resolve request.");
    }

    public function testTheFirstLoadMoreAsksForEachGotoLinkOnItsOwn(): void
    {
        $this->search(fn() => null);
        $this->loadMore();

        $missions = $this->resolveMissions();
        $this->assertSame([self::SITELINK, self::UNRESOLVED], array_map($this->linkOf(...), $missions));

        $search = collect($this->fetcher->missions())->firstWhere("name", "serper_web");
        foreach ($missions as $mission) {
            $this->assertSame("https://google.serper.dev/resolve", $mission["url"]);
            $this->assertSame(
                $search["headers"],
                $mission["headers"],
                "The resolve mission must carry the search's headers, which is where the API key is."
            );
            // The fetcher's default ten seconds would cut off the ~25 s wait.
            $this->assertSame(SerperBase::RESOLVE_WINDOW_SECONDS, $mission["curlopts"][CURLOPT_TIMEOUT]);
            $this->assertSame(0, $mission["curlopts"][CURLOPT_LOW_SPEED_LIMIT]);
        }
    }

    public function testLinksAreQueuedForResolutionOnlyOnce(): void
    {
        $this->search(fn() => null);
        $this->loadMore();
        $this->loadMore();

        $this->assertCount(2, $this->resolveMissions());
    }

    public function testAResolvedLinkBringsItsResultBack(): void
    {
        $first = $this->search(fn(string $link) => self::resolvedTo(self::TARGETS[$link]));
        $this->assertArrayNotHasKey("Nur als Google-Weiterleitung", self::linksOf($first));

        $payload = $this->loadMore();

        $this->assertSame("https://serper-example.net/weitergeleitet", self::linksOf($payload)["Nur als Google-Weiterleitung"] ?? null);
        $this->assertTrue($payload["finished"], "Nothing is left to resolve, so there is nothing left to wait for.");
        $this->assertFalse($payload["resolving"]);
    }

    public function testAResolvedSitelinkComesBackOnItsResult(): void
    {
        $this->search(fn(string $link) => self::resolvedTo(self::TARGETS[$link]));
        $payload = $this->loadMore();

        $result = collect($payload["results"])->firstWhere("title", "Is art anti-capitalist?");
        $this->assertSame([
            ["title" => "Archiv", "link" => "https://economistwritingeveryday.com/archive/"],
            ["title" => "Nur bei Google", "link" => "https://economistwritingeveryday.com/about/"],
        ], $result["sitelinks"]);
    }

    public function testLoadMoreIsNotFinishedWhileLinksAreStillBeingResolved(): void
    {
        $this->search(fn() => null);

        $payload = $this->loadMore();

        $this->assertFalse($payload["finished"]);
        $this->assertTrue($payload["resolving"]);
    }

    public function testAnAnswerThatArrivesOnALaterLoadMoreIsPickedUpThen(): void
    {
        $this->search(fn() => null);
        $this->loadMore();

        foreach ($this->resolveMissions() as $mission) {
            if ($this->linkOf($mission) === self::UNRESOLVED) {
                $this->fetcher->deliver($mission, self::resolvedTo(self::TARGETS[self::UNRESOLVED]));
            }
        }
        $payload = $this->loadMore();

        $this->assertSame("https://serper-example.net/weitergeleitet", self::linksOf($payload)["Nur als Google-Weiterleitung"] ?? null);
        $this->assertFalse($payload["finished"], "The sitelink is still out.");
    }

    public function testTheWindowClosesOnLinksThatNeverResolve(): void
    {
        $this->search(fn() => null);
        $this->loadMore();

        $this->travel(SerperBase::RESOLVE_WINDOW_SECONDS + 1)->seconds();
        $payload = $this->loadMore();

        $this->assertTrue($payload["finished"]);
        $this->assertFalse($payload["resolving"]);
        $this->assertArrayNotHasKey("Nur als Google-Weiterleitung", self::linksOf($payload));
    }

    /**
     * `/resolve` leaves a link it could not open as the Google link. That is
     * an answer, not a reason to keep waiting — and never something to show.
     */
    public function testALinkSerperCouldNotResolveIsGivenUpOn(): void
    {
        $this->search(fn(string $link) => $link === self::UNRESOLVED
            ? self::resolvedTo(self::UNRESOLVED)
            : "no-result");

        $payload = $this->loadMore();

        $this->assertTrue($payload["finished"]);
        $this->assertArrayNotHasKey("Nur als Google-Weiterleitung", self::linksOf($payload));
        foreach ($payload["results"] as $result) {
            $this->assertStringNotContainsString("google.com/goto", $result["link"]);
        }
    }
}
