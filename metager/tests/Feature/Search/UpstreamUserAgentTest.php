<?php

namespace Tests\Feature\Search;

use App\Support\Browser;
use App\Support\UpstreamUserAgent;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * What MetaGer tells a search engine about the person searching.
 *
 * It used to tell it everything: the client's own User-Agent header went into
 * the fetch mission verbatim and out to the engine, alongside the search term.
 * Browser, version, operating system, build — for the engines that log it, that
 * is most of a fingerprint attached to a query, which is the one thing a
 * metasearch engine exists to prevent.
 *
 * Now two strings leave the building, and the only bit of the client that
 * decides between them is desktop-or-not. These tests drive the real search
 * path and read the mission the fetcher would have executed, so they fail if
 * anything re-introduces the pass-through — including at a layer below
 * Searchengine, where the mission is assembled.
 */
class UpstreamUserAgentTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * A distinctive client User-Agent, chosen so that a pass-through is
     * unmistakable in a failure message.
     */
    private const CLIENT_DESKTOP = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36";

    private const CLIENT_PHONE = "Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36";

    /**
     * @return array<int, string> the User-Agent of every mission the search queued
     */
    private function userAgentsSentFor(string $clientUserAgent): array
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->withHeader("User-Agent", $clientUserAgent)
            ->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")
            ->assertOk();

        $missions = array_filter($fetcher->missions(), fn(array $m) => $m["name"] !== "Quicktips");
        $this->assertNotEmpty($missions, "The search queued nothing, so this test proves nothing.");

        return array_values(array_unique(array_map(fn(array $m) => $m["useragent"], $missions)));
    }

    /**
     * The regression this commit is about, asserted on the value rather than
     * against the constant: comparing to UpstreamUserAgent::DESKTOP alone would
     * still pass if that constant were quietly redefined as the client header.
     *
     * @param array<int, string> $sent
     */
    private function assertRevealsNothingAbout(string $client, array $sent): void
    {
        foreach ($sent as $userAgent) {
            $this->assertNotSame($client, $userAgent, "The visitor's User-Agent was forwarded to a search engine.");
            $this->assertStringNotContainsString("Chrome", $userAgent);
            $this->assertStringNotContainsString("Windows", $userAgent);
            $this->assertStringNotContainsString("Pixel", $userAgent);
        }
    }

    /**
     * One search per test method, not two — a second search in the same PHP
     * process returns 500, because QueryTimer is a singleton that throws on a
     * repeated event name. Pinned in OutputFormatsTest; it is why the desktop
     * and mobile cases cannot share a method.
     */
    public function testADesktopSearchSendsTheGenericDesktopUserAgent(): void
    {
        $sent = $this->userAgentsSentFor(self::CLIENT_DESKTOP);

        $this->assertSame([UpstreamUserAgent::DESKTOP], $sent);
        $this->assertRevealsNothingAbout(self::CLIENT_DESKTOP, $sent);
    }

    public function testAPhoneSearchSendsTheGenericMobileUserAgent(): void
    {
        $sent = $this->userAgentsSentFor(self::CLIENT_PHONE);

        $this->assertSame([UpstreamUserAgent::MOBILE], $sent);
        $this->assertRevealsNothingAbout(self::CLIENT_PHONE, $sent);
    }

    /**
     * Every engine of the fokus gets the same string. Worth pinning: the value
     * is memoised on a container singleton, and a per-engine value would be
     * both slower and a way to tell engines apart.
     */
    public function testEveryEngineOfASearchGetsTheSameUserAgent(): void
    {
        $this->assertCount(1, $this->userAgentsSentFor(self::CLIENT_DESKTOP));
    }

    /**
     * A tablet counts as mobile. DeviceDetector draws the line there and so do
     * we; the point is which layout an HTML engine serves.
     */
    public function testATabletCountsAsMobile(): void
    {
        $tablet = Browser::fromUserAgent(
            "Mozilla/5.0 (Linux; Android 13; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36"
        );

        $this->assertTrue($tablet->isTablet(), "This User-Agent is no longer detected as a tablet; pick another.");
        $this->assertSame(UpstreamUserAgent::MOBILE, UpstreamUserAgent::for($tablet));
    }

    /**
     * A crawler, a curl script, an empty header — anything not recognisably
     * mobile gets the desktop string rather than an empty one.
     */
    public function testAnUnrecognisedClientGetsTheDesktopUserAgent(): void
    {
        foreach (["", "curl/8.5.0", "MetaGer-Monitoring"] as $odd) {
            $this->assertSame(
                UpstreamUserAgent::DESKTOP,
                UpstreamUserAgent::for(Browser::fromUserAgent($odd)),
                "An unrecognised client (`$odd`) did not get the desktop User-Agent."
            );
        }
    }

    /**
     * A User-Agent claiming a Firefox nobody runs any more is itself a
     * fingerprint, and the string is a constant that no build step updates. If
     * this fails, bump UpstreamUserAgent::VERSION rather than the bound.
     *
     * The bound is deliberately generous — this is a reminder on a slow timer,
     * not a policy about which Firefox is current. Firefox ships roughly
     * thirteen releases a year; version 142 is where this commit set it, in
     * August 2026.
     */
    public function testTheAdvertisedFirefoxVersionHasNotGoneStale(): void
    {
        preg_match("/Firefox\/(\d+)/", UpstreamUserAgent::DESKTOP, $matches);
        $version = (int) ($matches[1] ?? 0);

        $yearsSince = (strtotime("now") - strtotime("2026-08-01")) / (365.25 * 24 * 3600);
        $expected = 142 + (int) floor($yearsSince * 13);

        $this->assertGreaterThanOrEqual(
            $expected - 26,
            $version,
            "UpstreamUserAgent advertises Firefox $version, which is around two years behind current. "
            . "That is distinctive enough to undo the point of sending a generic User-Agent at all — bump VERSION."
        );
    }

    /**
     * Both strings carry the same version, so bumping one bumps both.
     */
    public function testDesktopAndMobileAdvertiseTheSameVersion(): void
    {
        preg_match("/rv:([\d.]+)/", UpstreamUserAgent::DESKTOP, $desktop);
        preg_match("/rv:([\d.]+)/", UpstreamUserAgent::MOBILE, $mobile);

        $this->assertSame($desktop[1], $mobile[1]);
    }
}
