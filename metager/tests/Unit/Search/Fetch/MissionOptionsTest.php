<?php

namespace Tests\Unit\Search\Fetch;

use App\Search\Fetch\MissionOptions;
use Tests\TestCase;

/**
 * Characterization tests for how a fetch mission becomes curl options.
 *
 * This is every outgoing request MetaGer makes to a search engine, and until
 * now none of it was covered: the option building sat inside the worker loop of
 * requests:fetcher, where reaching it meant a Redis queue and a network. It is
 * about to change — nothing currently asks upstream for a compressed response —
 * so this pins what it does today first.
 *
 * The assertions are on option constants rather than on a fetched response.
 * What matters is the request curl is told to make; whether an engine answers
 * it is not this test's business.
 */
class MissionOptionsTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function mission(array $overrides = []): array
    {
        return $overrides + [
            "resulthash" => "abc123",
            "url" => "https://api.example.org/search?q=kaffee",
            "useragent" => "Mozilla/5.0 (X11; Linux x86_64) MetaGer",
            "username" => null,
            "password" => null,
            "headers" => [],
            "cacheDuration" => 60,
            "name" => "brave",
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        // No proxy unless a test asks for one. The deployed fetcher has one
        // configured, so leaving this to the environment would make the result
        // depend on whoever ran the suite.
        config(["metager.metager.fetcher.proxy" => ["host" => "", "port" => "", "user" => "", "password" => ""]]);
    }

    public function testTheUrlAndUserAgentComeFromTheMission(): void
    {
        $options = MissionOptions::for($this->mission());

        $this->assertSame("https://api.example.org/search?q=kaffee", $options[CURLOPT_URL]);
        $this->assertSame("Mozilla/5.0 (X11; Linux x86_64) MetaGer", $options[CURLOPT_USERAGENT]);
        $this->assertSame(1, $options[CURLOPT_RETURNTRANSFER]);
        $this->assertTrue($options[CURLOPT_FOLLOWLOCATION]);
    }

    /**
     * The worker gets one string back off a finished handle and has to know
     * from it where to store the answer, for how long, and which engine it
     * belongs to. Changing the separator or the order breaks readMultiCurl,
     * which is a different file.
     */
    public function testThePrivateTagCarriesHashDurationAndEngineName(): void
    {
        $options = MissionOptions::for($this->mission());

        $this->assertSame("abc123;60;brave", $options[CURLOPT_PRIVATE]);
    }

    /**
     * These bound how long a search engine may take. waitForMainResults gives up
     * at 6 seconds, so an engine answering after CURLOPT_TIMEOUT would not have
     * made the page anyway — the values are pinned because changing them changes
     * how many engines make it onto a slow page.
     */
    public function testTheTimeoutsAreWhatTheSearchPathAssumes(): void
    {
        $options = MissionOptions::for($this->mission());

        $this->assertSame(8, $options[CURLOPT_CONNECTTIMEOUT]);
        $this->assertSame(10, $options[CURLOPT_TIMEOUT]);
        $this->assertSame(50000, $options[CURLOPT_LOW_SPEED_LIMIT]);
        $this->assertSame(10, $options[CURLOPT_LOW_SPEED_TIME]);
    }

    /**
     * Connection reuse across searches is the reason a second search to the same
     * engine skips DNS, TCP and TLS entirely.
     */
    public function testKeepaliveIsRequested(): void
    {
        $options = MissionOptions::for($this->mission());

        $this->assertSame(1, $options[CURLOPT_TCP_KEEPALIVE]);
        $this->assertSame(600, $options[CURLOPT_TCP_KEEPIDLE]);
        $this->assertSame(15, $options[CURLOPT_TCP_KEEPINTVL]);
    }

    /**
     * Every engine response used to arrive uncompressed, because nothing ever
     * asked. An empty string is not "no encoding" — it tells curl to advertise
     * every encoding it was built with (this image has gzip, brotli and zstd)
     * and to decode the answer transparently, so the parsers keep seeing the
     * same bytes.
     */
    public function testUpstreamIsAskedForACompressedResponse(): void
    {
        $options = MissionOptions::for($this->mission());

        $this->assertArrayHasKey(CURLOPT_ACCEPT_ENCODING, $options, "Engine responses are being fetched uncompressed again.");
        $this->assertSame("", $options[CURLOPT_ACCEPT_ENCODING]);
    }

    /**
     * The escape hatch, in case an engine turns out to answer badly to one of
     * the encodings curl offers. Narrowing it to `gzip` — or to "" via a header
     * of its own — stays a per-engine decision.
     */
    public function testAnEngineCanNarrowTheEncodingsItIsOffered(): void
    {
        $options = MissionOptions::for($this->mission([
            "curlopts" => [CURLOPT_ACCEPT_ENCODING => "gzip"],
        ]));

        $this->assertSame("gzip", $options[CURLOPT_ACCEPT_ENCODING]);
    }

    public function testHeadersBecomeAFlatListOfHeaderLines(): void
    {
        $options = MissionOptions::for($this->mission([
            "headers" => ["X-Subscription-Token" => "secret", "Accept" => "application/json"],
        ]));

        $this->assertSame(
            ["X-Subscription-Token: secret", "Accept: application/json"],
            $options[CURLOPT_HTTPHEADER]
        );
    }

    public function testNoHeaderOptionIsSetWhenTheMissionHasNone(): void
    {
        $this->assertArrayNotHasKey(CURLOPT_HTTPHEADER, MissionOptions::for($this->mission()));
    }

    public function testHttpAuthCredentialsAreJoinedWithAColon(): void
    {
        $options = MissionOptions::for($this->mission(["username" => "user", "password" => "pw"]));

        $this->assertSame("user:pw", $options[CURLOPT_USERPWD]);
    }

    public function testAMissionWithOnlyAUsernameSetsNoCredentials(): void
    {
        $this->assertArrayNotHasKey(CURLOPT_USERPWD, MissionOptions::for($this->mission(["username" => "user"])));
    }

    /**
     * How a POST engine works: the mission carries raw curl options that win
     * over the defaults. Assigned one at a time rather than merged — curl option
     * constants are integers and array_merge renumbers integer keys.
     */
    public function testMissionCurlOptionsOverrideTheDefaults(): void
    {
        $options = MissionOptions::for($this->mission([
            "curlopts" => [CURLOPT_POST => true, CURLOPT_POSTFIELDS => '{"q":"kaffee"}', CURLOPT_TIMEOUT => 3],
        ]));

        $this->assertTrue($options[CURLOPT_POST]);
        $this->assertSame('{"q":"kaffee"}', $options[CURLOPT_POSTFIELDS]);
        $this->assertSame(3, $options[CURLOPT_TIMEOUT], "A mission override no longer beats the default timeout.");
        $this->assertSame("https://api.example.org/search?q=kaffee", $options[CURLOPT_URL]);
    }

    /**
     * A mission arrives as JSON, so its curlopts keys are strings by the time
     * they are decoded. As array keys PHP turns numeric strings into integers,
     * but the cast is explicit rather than relying on that.
     */
    public function testCurlOptionKeysSurviveTheJsonRoundTrip(): void
    {
        $mission = $this->mission(["curlopts" => [CURLOPT_POST => true]]);
        $decoded = json_decode(json_encode($mission), true);

        $this->assertTrue(MissionOptions::for($decoded)[CURLOPT_POST]);
    }

    public function testEveryMissionGoesThroughTheProxyWhenOneIsConfigured(): void
    {
        config([
            "metager.metager.fetcher.proxy.host" => "proxy.example.org",
            "metager.metager.fetcher.proxy.port" => "8080",
        ]);

        $options = MissionOptions::for($this->mission());

        $this->assertSame("proxy.example.org", $options[CURLOPT_PROXY]);
        $this->assertSame("8080", $options[CURLOPT_PROXYPORT]);
        $this->assertSame(CURLPROXY_HTTP, $options[CURLOPT_PROXYTYPE]);
        $this->assertArrayNotHasKey(CURLOPT_PROXYUSERPWD, $options, "Credentials appeared without any being configured.");
    }

    public function testProxyCredentialsAreSentWhenBothAreConfigured(): void
    {
        config([
            "metager.metager.fetcher.proxy.host" => "proxy.example.org",
            "metager.metager.fetcher.proxy.port" => "8080",
            "metager.metager.fetcher.proxy.user" => "user",
            "metager.metager.fetcher.proxy.password" => "pw",
        ]);

        $this->assertSame("user:pw", MissionOptions::for($this->mission())[CURLOPT_PROXYUSERPWD]);
    }

    /**
     * An engine can opt out per mission. That is the only way a request leaves
     * the fetcher directly while a proxy is configured, so it is worth a test
     * of its own.
     */
    public function testAMissionCanOptOutOfTheProxy(): void
    {
        config([
            "metager.metager.fetcher.proxy.host" => "proxy.example.org",
            "metager.metager.fetcher.proxy.port" => "8080",
        ]);

        $this->assertArrayNotHasKey(CURLOPT_PROXY, MissionOptions::for($this->mission(["proxy" => false])));
    }

    public function testNoProxyIsUsedWhenOnlyAHostIsConfigured(): void
    {
        config(["metager.metager.fetcher.proxy.host" => "proxy.example.org"]);

        $this->assertArrayNotHasKey(CURLOPT_PROXY, MissionOptions::for($this->mission()));
    }
}
