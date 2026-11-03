<?php

namespace Tests\Feature;

use App\Models\BlocklistEntry;
use App\Search\BlocklistNormalizer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\FakesSearchEngines;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * POST /api/blocklist/check against docs/blocklist-api.md.
 *
 * The searches are real — parsers, ranking, deduplication, host crowding — and
 * only the fetch is faked, the way every search feature test fakes it
 * (FakesSearchEngines). The fixtures: Brave's web answer has
 * example.org/kaffee, beispiel.de/sorten and example.org/espresso; Serper's
 * has example.org/kaffee too, and serper-example.net. An engine without a
 * fixture answers "no-result", which is what a failing upstream looks like.
 */
class BlocklistCheckTest extends TestCase
{
    use FakesSearchEngines;
    use UsesInMemorySqlite;

    private const TOKEN = "crm-admin-token-for-testing-91c3";

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        config(["metager.metager.crm.admin_token" => self::TOKEN]);
        Carbon::setTestNow("2026-11-03 14:05:00");

        // Nothing on this path may call out: no keyserver, no discharge.
        Http::preventStrayRequests();
        Http::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function fakeWeb(): \Tests\Support\FakeFetcher
    {
        return $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);
    }

    private function check(array $body, string $token = self::TOKEN): TestResponse
    {
        return $this->withHeader("Authorization", "Bearer " . $token)->postJson("/api/blocklist/check", $body);
    }

    private function block(string $kind, string $value, ?string $terms = null): BlocklistEntry
    {
        return BlocklistEntry::create([
            "kind" => $kind,
            "value" => BlocklistEntry::normalizeValue($kind, $value),
            "terms" => BlocklistEntry::normalizeTerms($kind, $terms),
            "reason" => "removal_request",
        ]);
    }

    public function testTheResponseShape(): void
    {
        $this->fakeWeb();

        $response = $this->check(["urls" => ["https://www.example.org/kaffee?ref=1"], "queries" => ["kaffee"], "foki" => ["web"]])
            ->assertOk();

        $this->assertSame(["data"], array_keys($response->json()));
        $this->assertSame(["checked_at", "scope", "results", "engines_failed"], array_keys($response->json("data")));
        $this->assertSame("2026-11-03T14:05:00+01:00", $response->json("data.checked_at"));
        $this->assertSame("first_page", $response->json("data.scope"));
        $this->assertCount(1, $response->json("data.results"));
        $this->assertSame(
            ["url", "query", "fokus", "found", "rank", "engines", "blocked_now", "blocking_entry_ids"],
            array_keys($response->json("data.results.0"))
        );
        foreach ($response->json("data.engines_failed") as $failed) {
            $this->assertSame(["fokus", "name"], array_keys($failed));
        }
    }

    public function testAUrlFoundByTwoEngines(): void
    {
        $this->fakeWeb();

        $row = $this->check(["urls" => ["https://example.org/kaffee"], "queries" => ["kaffee"], "foki" => ["web"]])
            ->assertOk()
            ->json("data.results.0");

        $this->assertSame("example.org/kaffee", $row["url"]);
        $this->assertSame("kaffee", $row["query"]);
        $this->assertSame("web", $row["fokus"]);
        $this->assertTrue($row["found"]);
        $this->assertIsInt($row["rank"]);
        $this->assertGreaterThanOrEqual(1, $row["rank"]);
        // The engine key from config/foki.json, not the display name: it is
        // what suma-crm maps onto the removal providers.
        $this->assertSame(["brave", "serper_web"], array_column($row["engines"], "name"));
        foreach ($row["engines"] as $engine) {
            $this->assertIsString($engine["display_name"]);
            $this->assertNotSame("", $engine["display_name"]);
        }
        $this->assertFalse($row["blocked_now"]);
        $this->assertSame([], $row["blocking_entry_ids"]);
    }

    public function testAUrlFoundByOneEngine(): void
    {
        $this->fakeWeb();

        $row = $this->check(["urls" => ["beispiel.de/sorten"], "queries" => ["kaffee"], "foki" => ["web"]])
            ->assertOk()
            ->json("data.results.0");

        $this->assertTrue($row["found"]);
        $this->assertSame(["brave"], array_column($row["engines"], "name"));
    }

    public function testAUrlNotFound(): void
    {
        $this->fakeWeb();

        $row = $this->check(["urls" => ["https://nowhere.example/page"], "queries" => ["kaffee"], "foki" => ["web"]])
            ->assertOk()
            ->json("data.results.0");

        $this->assertFalse($row["found"]);
        $this->assertNull($row["rank"]);
        $this->assertSame([], $row["engines"]);
        $this->assertFalse($row["blocked_now"]);
    }

    /**
     * The point of bypassing the filter: a blocked page is still reported as
     * returned upstream, along with the entry that hides it.
     */
    public function testAUrlFoundButBlocked(): void
    {
        $this->fakeWeb();
        $entry = $this->block("url", "example.org/kaffee");

        $row = $this->check(["urls" => ["https://example.org/kaffee"], "queries" => ["kaffee"], "foki" => ["web"]])
            ->assertOk()
            ->json("data.results.0");

        $this->assertTrue($row["found"], "The check ran with the blocklist applied and could not see the page.");
        $this->assertSame(["brave", "serper_web"], array_column($row["engines"], "name"));
        $this->assertTrue($row["blocked_now"]);
        $this->assertSame([$entry->id], $row["blocking_entry_ids"]);
    }

    /**
     * The bypass is the check's alone: a normal search in the same process
     * still filters.
     */
    public function testTheBypassDoesNotOutliveTheCheck(): void
    {
        $this->fakeWeb();
        $this->block("url", "example.org/kaffee");

        $this->check(["urls" => ["https://example.org/kaffee"], "queries" => ["kaffee"], "foki" => ["web"]])->assertOk();

        $this->assertTrue(app(\App\Search\Blacklists::class)->blocksResult("example.org", "example.org/kaffee", "kaffee"));
    }

    public function testAUrlTermsEntryMatchingAQueryVariant(): void
    {
        $this->fakeWeb();
        $entry = $this->block("url_terms", "example.org/espresso", "Kaffee");

        $rows = $this->check([
            "urls" => ["https://example.org/espresso"],
            "queries" => ["KAFFEE", "kaffee"],
            "foki" => ["web"],
        ])->assertOk()->json("data.results");

        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertTrue($row["found"], "Query " . $row["query"]);
            $this->assertTrue($row["blocked_now"], "Query " . $row["query"]);
            $this->assertSame([$entry->id], $row["blocking_entry_ids"]);
        }
        $this->assertSame(["KAFFEE", "kaffee"], array_column($rows, "query"));
    }

    public function testOneRowPerUrlQueryAndFokusAndTheDefaultFoki(): void
    {
        $this->fakeWeb();

        $rows = $this->check([
            "urls" => ["https://example.org/kaffee", "https://beispiel.de/sorten"],
            "queries" => ["kaffee"],
        ])->assertOk()->json("data.results");

        $this->assertSame(
            [
                ["example.org/kaffee", "web"], ["example.org/kaffee", "bilder"], ["example.org/kaffee", "nachrichten"],
                ["beispiel.de/sorten", "web"], ["beispiel.de/sorten", "bilder"], ["beispiel.de/sorten", "nachrichten"],
            ],
            array_map(fn(array $row) => [$row["url"], $row["fokus"]], $rows)
        );
    }

    /**
     * Engines that failed are named, so "not found" can be qualified. Serper
     * answers "no-result" here — the literal the fetcher writes for an
     * upstream error — and so it is not a source for anything, and is listed.
     */
    public function testFailedEnginesAreListed(): void
    {
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => "no-result",
        ]);

        $data = $this->check(["urls" => ["https://example.org/kaffee"], "queries" => ["kaffee"], "foki" => ["web"]])
            ->assertOk()
            ->json("data");

        $this->assertSame([["fokus" => "web", "name" => "serper_web"]], $data["engines_failed"]);
        $this->assertSame(["brave"], array_column($data["results"][0]["engines"], "name"));
    }

    /**
     * The paid engines have to run: the check answers on MetaGer's behalf,
     * without a key, and Serper is the one a removal request is about.
     */
    public function testPaidEnginesAreAskedWithoutAKey(): void
    {
        $fetcher = $this->fakeWeb();

        $this->check(["urls" => ["https://example.org/kaffee"], "queries" => ["kaffee"], "foki" => ["web"]])->assertOk();

        $this->assertContains("serper_web", $fetcher->queuedEngines());
        $this->assertContains("brave", $fetcher->queuedEngines());
    }

    public function testValidation(): void
    {
        $this->check([])->assertStatus(422)->assertJsonValidationErrors(["urls", "queries"]);
        $this->check(["urls" => array_fill(0, 21, "a.example"), "queries" => ["q"]])->assertStatus(422)->assertJsonValidationErrors("urls");
        $this->check(["urls" => ["a.example"], "queries" => array_fill(0, 6, "q")])->assertStatus(422)->assertJsonValidationErrors("queries");
        $this->check(["urls" => ["a.example"], "queries" => ["q"], "foki" => ["maps"]])->assertStatus(422)->assertJsonValidationErrors("foki.0");
        $this->check(["urls" => ["not a url"], "queries" => ["q"]])->assertStatus(422)->assertJsonValidationErrors("urls.0");
    }

    public function testTheTokenIsRequired(): void
    {
        $this->check(["urls" => ["a.example"], "queries" => ["q"]], "wrong")
            ->assertStatus(401)
            ->assertExactJson(["error" => "Unauthorized"]);
    }

    public function testItIsThrottledToTenAMinutePerToken(): void
    {
        $this->fakeWeb();
        $body = ["urls" => ["https://example.org/kaffee"], "queries" => ["kaffee"], "foki" => ["web"]];

        for ($i = 0; $i < 10; $i++) {
            $this->check($body)->assertOk();
        }

        $this->check($body)
            ->assertStatus(429)
            ->assertExactJson(["error" => "Too many requests"])
            ->assertHeader("Retry-After");

        Carbon::setTestNow(now()->addSeconds(61));
        $this->check($body)->assertOk();
    }
}
