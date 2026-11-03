<?php

namespace Tests\Feature;

use App\Models\BlocklistEntry;
use App\Search\BlocklistVersion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * /api/blocklist against docs/blocklist-api.md.
 *
 * suma-crm is built against that document with Http::fake, so these tests are
 * the other half of the contract: every shape here is spelled out key by key,
 * and a field added, renamed or retyped fails here before it fails there.
 */
class BlocklistApiTest extends TestCase
{
    use UsesInMemorySqlite;

    /**
     * Spelled out, not read through the middleware: config/metager/metager.php
     * doubles the segment, and a test that went through the same wrong path as
     * the code would pass along with the bug. See EventBroadcastAuthorizationTest.
     */
    private const CONFIG_PATH = "metager.metager.crm.admin_token";

    private const TOKEN = "crm-admin-token-for-testing-91c3";

    /** The entry shape, in contract order. */
    private const ENTRY_KEYS = [
        "id", "kind", "value", "terms", "reason", "note", "external_ref",
        "created_by", "review_at", "created_at", "updated_at", "deleted_at",
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        config([self::CONFIG_PATH => self::TOKEN]);
        Carbon::setTestNow("2026-11-03 14:05:00");
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function api(string $method, string $uri, array $data = [], ?string $token = self::TOKEN): TestResponse
    {
        $headers = $token === null ? [] : ["Authorization" => "Bearer " . $token];

        return $this->json($method, $uri, $data, $headers);
    }

    private function create(array $overrides = []): TestResponse
    {
        return $this->api("POST", "/api/blocklist", $overrides + [
            "kind" => "url_terms",
            "value" => "https://www.Example.org/people/max-mustermann/?x=1",
            "terms" => ["Max", "MUSTERMANN"],
            "reason" => "removal_request",
            "note" => "Art. 17 request, balancing in favour of requester",
            "external_ref" => "crm:removal:17",
            "created_by" => "jane@suma-ev.de",
            "review_at" => "2027-11-03",
        ]);
    }

    private function assertEntryShape(array $entry): void
    {
        $this->assertSame(self::ENTRY_KEYS, array_keys($entry), "The entry's keys drifted from docs/blocklist-api.md.");
    }

    private function version(): ?string
    {
        return Redis::connection()->get(BlocklistVersion::KEY);
    }

    // --- authorization ------------------------------------------------------

    public function testTheTokenIsRequired(): void
    {
        $this->api("GET", "/api/blocklist", token: null)->assertStatus(401)->assertExactJson(["error" => "Unauthorized"]);
        $this->api("GET", "/api/blocklist", token: "wrong")->assertStatus(401)->assertExactJson(["error" => "Unauthorized"]);
    }

    public function testAnUnsetTokenRefusesEverything(): void
    {
        config([self::CONFIG_PATH => null]);
        $this->api("GET", "/api/blocklist", token: "")->assertStatus(401);

        config([self::CONFIG_PATH => ""]);
        $this->withHeader("Authorization", "Bearer ")->getJson("/api/blocklist")->assertStatus(401);
    }

    /**
     * suma-crm calls from a server: no cookies, no Origin header. The token is
     * the credential, so a write must work without an Origin and regardless
     * of one — the same-origin checks that stand in for CSRF on the
     * cookie-carrying forms (Concerns\HandlesKeyCheckout::sameOrigin and its
     * siblings) are not in this path, and must not be.
     */
    public function testAServerWithoutAnOriginCanWrite(): void
    {
        $this->assertNull(request()->header("Origin"));

        $this->create()->assertStatus(201);
    }

    public function testAForeignOriginDoesNotMatterButTheTokenDoes(): void
    {
        $this->withHeader("Origin", "https://evil.example")->create()->assertStatus(201);

        $this->withHeader("Origin", config("app.url"))
            ->postJson("/api/blocklist", ["kind" => "domain", "value" => "example.net", "reason" => "spam"])
            ->assertStatus(401);
    }

    /**
     * Not a navigation, never relocated: an API call that forgot its Accept
     * header still gets JSON, and the locale prefix is stripped like anywhere
     * else.
     */
    public function testTheApiAnswersJsonWithoutAnAcceptHeaderAndUnderALocalePrefix(): void
    {
        $this->withHeader("Authorization", "Bearer " . self::TOKEN)
            ->get("/de-DE/api/blocklist")
            ->assertOk()
            ->assertJsonPath("meta.total", 0);

        $this->withHeader("Authorization", "Bearer " . self::TOKEN)
            ->post("/api/blocklist", ["kind" => "nope"])
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors" => ["kind"]]);
    }

    // --- create -------------------------------------------------------------

    public function testCreateStoresTheNormalisedEntry(): void
    {
        $response = $this->create()->assertStatus(201);

        $this->assertEntryShape($response->json("data"));
        $this->assertSame([
            "id" => 1,
            "kind" => "url_terms",
            "value" => "example.org/people/max-mustermann",
            "terms" => ["max", "mustermann"],
            "reason" => "removal_request",
            "note" => "Art. 17 request, balancing in favour of requester",
            "external_ref" => "crm:removal:17",
            "created_by" => "jane@suma-ev.de",
            "review_at" => "2027-11-03",
            "created_at" => "2026-11-03T14:05:00+01:00",
            "updated_at" => "2026-11-03T14:05:00+01:00",
            "deleted_at" => null,
        ], $response->json("data"));
    }

    public function testADomainIsStoredAsItsHostWithNullTerms(): void
    {
        $this->create(["kind" => "domain", "value" => "https://WWW.Example.org:8443/a?b", "terms" => null])
            ->assertStatus(201)
            ->assertJsonPath("data.value", "example.org")
            ->assertJsonPath("data.terms", null);
    }

    public function testUrlTermsNeedTerms(): void
    {
        $this->create(["terms" => null])->assertStatus(422)->assertJsonValidationErrors("terms");
        $this->create(["terms" => []])->assertStatus(422)->assertJsonValidationErrors("terms");
        $this->create(["terms" => [" - "]])->assertStatus(422)->assertJsonValidationErrors("terms");
    }

    public function testOtherKindsRefuseTerms(): void
    {
        $this->create(["kind" => "url", "terms" => ["max"]])->assertStatus(422)->assertJsonValidationErrors("terms");
    }

    public function testValidationAnswersInLaravelsShape(): void
    {
        $this->api("POST", "/api/blocklist", ["kind" => "everything", "reason" => "because"])
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors" => ["kind", "value", "reason"]]);

        $this->create(["value" => "not a url at all/"])->assertStatus(422)->assertJsonValidationErrors("value");
        $this->create(["review_at" => "03.11.2027"])->assertStatus(422)->assertJsonValidationErrors("review_at");
        $this->create(["note" => str_repeat("x", 2001)])->assertStatus(422)->assertJsonValidationErrors("note");
    }

    /**
     * Unique among non-deleted entries, compared after normalisation — the
     * same terms in another order and spelling are the same entry.
     */
    public function testADuplicateIsRefusedUntilTheFirstIsDeleted(): void
    {
        $this->create()->assertStatus(201);

        $this->create(["value" => "example.org/people/max-mustermann", "terms" => ["mustermann max"]])
            ->assertStatus(422)
            ->assertJsonValidationErrors("value");

        $this->api("DELETE", "/api/blocklist/1")->assertOk();
        $this->create()->assertStatus(201)->assertJsonPath("data.id", 2);
    }

    // --- read ---------------------------------------------------------------

    public function testShow(): void
    {
        $this->create();

        $response = $this->api("GET", "/api/blocklist/1")->assertOk();
        $this->assertSame(["data"], array_keys($response->json()));
        $this->assertEntryShape($response->json("data"));
    }

    public function testShowIncludesDeletedEntries(): void
    {
        $this->create();
        $this->api("DELETE", "/api/blocklist/1");

        $this->api("GET", "/api/blocklist/1")->assertOk()->assertJsonPath("data.deleted_at", "2026-11-03T14:05:00+01:00");
    }

    public function testUnknownIdsAre404(): void
    {
        foreach (["GET", "PATCH", "DELETE"] as $method) {
            $this->api($method, "/api/blocklist/99")->assertStatus(404)->assertExactJson(["error" => "Not found"]);
            $this->api($method, "/api/blocklist/abc")->assertStatus(404)->assertExactJson(["error" => "Not found"]);
        }
    }

    public function testIndexShape(): void
    {
        $this->create();

        $response = $this->api("GET", "/api/blocklist")->assertOk();

        $this->assertSame(["data", "meta"], array_keys($response->json()));
        $this->assertEntryShape($response->json("data.0"));
        $this->assertSame(
            ["current_page" => 1, "last_page" => 1, "per_page" => 50, "total" => 1, "review_due" => 0],
            $response->json("meta")
        );
    }

    public function testIndexIsNewestFirstAndPaginates(): void
    {
        foreach (["a.example", "b.example", "c.example"] as $host) {
            $this->create(["kind" => "domain", "value" => $host, "terms" => null])->assertStatus(201);
        }

        $first = $this->api("GET", "/api/blocklist?per_page=2")->assertOk();
        $this->assertSame([3, 2], array_column($first->json("data"), "id"));
        $this->assertSame(["current_page" => 1, "last_page" => 2, "per_page" => 2, "total" => 3, "review_due" => 0], $first->json("meta"));

        $second = $this->api("GET", "/api/blocklist?per_page=2&page=2")->assertOk();
        $this->assertSame([1], array_column($second->json("data"), "id"));

        $this->api("GET", "/api/blocklist?per_page=201")->assertStatus(422)->assertJsonValidationErrors("per_page");
    }

    public function testIndexFilters(): void
    {
        $this->create(); // 1: url_terms, removal_request, crm:removal:17
        $this->create(["kind" => "domain", "value" => "spam.example", "terms" => null, "reason" => "spam", "note" => "Casino", "external_ref" => null]); // 2
        $this->create(["kind" => "url", "value" => "other.example/x", "terms" => null, "reason" => "legal", "note" => null, "external_ref" => "crm:removal:18", "review_at" => "2026-11-01"]); // 3

        $ids = fn(string $query) => array_column($this->api("GET", "/api/blocklist?" . $query)->assertOk()->json("data"), "id");

        $this->assertSame([2], $ids("kind=domain"));
        $this->assertSame([3], $ids("reason=legal"));
        $this->assertSame([2], $ids("q=CASINO"));
        $this->assertSame([1], $ids("q=mustermann"));
        $this->assertSame([3, 1], $ids("q=crm:removal"));
        $this->assertSame([3], $ids("external_ref=crm:removal:18"));
        $this->assertSame([3], $ids("review_due=1"));

        $this->api("DELETE", "/api/blocklist/2");
        $this->assertSame([3, 1], $ids(""));
        $this->assertSame([3, 2, 1], $ids("trashed=1"));

        $this->api("GET", "/api/blocklist?kind=everything")->assertStatus(422)->assertJsonValidationErrors("kind");
    }

    /**
     * suma-crm's nav badge reads this with per_page=1, so it must not depend
     * on the filters — and it counts only live entries.
     */
    public function testReviewDueIgnoresTheFiltersAndDeletedEntries(): void
    {
        $this->create(["review_at" => "2026-11-03"]); // due today
        $this->create(["kind" => "domain", "value" => "a.example", "terms" => null, "review_at" => "2026-10-01"]);
        $this->create(["kind" => "domain", "value" => "b.example", "terms" => null, "review_at" => "2026-11-04"]); // tomorrow
        $this->create(["kind" => "domain", "value" => "c.example", "terms" => null, "review_at" => "2026-01-01"]);
        $this->api("DELETE", "/api/blocklist/4");

        $this->api("GET", "/api/blocklist?per_page=1&kind=url")->assertOk()->assertJsonPath("meta.review_due", 2);
    }

    // --- update / delete ----------------------------------------------------

    public function testPatchChangesTheBookkeepingFields(): void
    {
        $this->create();
        Carbon::setTestNow("2026-11-04 09:00:00");

        $response = $this->api("PATCH", "/api/blocklist/1", [
            "reason" => "legal",
            "note" => null,
            "review_at" => "2028-01-01",
            "external_ref" => "crm:removal:99",
        ])->assertOk();

        $this->assertEntryShape($response->json("data"));
        $this->assertSame("legal", $response->json("data.reason"));
        $this->assertNull($response->json("data.note"));
        $this->assertSame("2028-01-01", $response->json("data.review_at"));
        $this->assertSame("crm:removal:99", $response->json("data.external_ref"));
        $this->assertSame("2026-11-04T09:00:00+01:00", $response->json("data.updated_at"));
        $this->assertSame("2026-11-03T14:05:00+01:00", $response->json("data.created_at"));
    }

    public function testPatchRefusesWhatAnEntryBlocks(): void
    {
        $this->create();

        $this->api("PATCH", "/api/blocklist/1", ["value" => "example.org/else", "kind" => "url", "terms" => ["x"]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(["value", "kind", "terms"]);

        $this->api("PATCH", "/api/blocklist/1", ["reason" => "because"])->assertStatus(422)->assertJsonValidationErrors("reason");
    }

    public function testDeleteSoftDeletesAndRecordsWho(): void
    {
        $this->create();

        $response = $this->api("DELETE", "/api/blocklist/1", ["deleted_by" => "jane@suma-ev.de"])->assertOk();

        $this->assertEntryShape($response->json("data"));
        $this->assertSame("2026-11-03T14:05:00+01:00", $response->json("data.deleted_at"));
        $this->assertSame("jane@suma-ev.de", BlocklistEntry::withTrashed()->find(1)->deleted_by);

        $this->api("DELETE", "/api/blocklist/1")->assertStatus(404)->assertExactJson(["error" => "Not found"]);
        $this->api("PATCH", "/api/blocklist/1", ["note" => "x"])->assertStatus(404);
    }

    public function testEveryWriteBumpsTheVersion(): void
    {
        $before = (int) $this->version();
        $this->create();
        $afterCreate = (int) $this->version();
        $this->assertGreaterThan($before, $afterCreate, "create");

        $this->api("PATCH", "/api/blocklist/1", ["note" => "changed"]);
        $afterPatch = (int) $this->version();
        $this->assertGreaterThan($afterCreate, $afterPatch, "patch");

        $this->api("DELETE", "/api/blocklist/1");
        $this->assertGreaterThan($afterPatch, (int) $this->version(), "delete");
    }

    // --- test ---------------------------------------------------------------

    public function testTheTestEndpointReportsEveryMatchingEntry(): void
    {
        $this->create(); // 1: url_terms max mustermann
        $this->create(["kind" => "description", "value" => "example.org/people/max-mustermann", "terms" => null]); // 2
        $this->create(["kind" => "url_terms", "terms" => ["erika"]]); // 3, does not apply

        $response = $this->api("POST", "/api/blocklist/test", [
            "url" => "https://www.example.org/people/max-mustermann?x=1",
            "query" => "Max Mustermann Anwalt",
        ])->assertOk();

        $this->assertSame(["data"], array_keys($response->json()));
        $this->assertSame(["url", "query", "blocked", "description_blanked", "matches"], array_keys($response->json("data")));
        $this->assertSame("example.org/people/max-mustermann", $response->json("data.url"));
        $this->assertSame("Max Mustermann Anwalt", $response->json("data.query"));
        $this->assertTrue($response->json("data.blocked"));
        $this->assertTrue($response->json("data.description_blanked"));
        $this->assertSame([1, 2], array_column($response->json("data.matches"), "id"));
        $this->assertEntryShape($response->json("data.matches.0"));
    }

    public function testTheTestEndpointForAnotherQuery(): void
    {
        $this->create();

        $this->api("POST", "/api/blocklist/test", [
            "url" => "https://example.org/people/max-mustermann",
            "query" => "Max",
        ])
            ->assertOk()
            ->assertJsonPath("data.blocked", false)
            ->assertJsonPath("data.description_blanked", false)
            ->assertJsonPath("data.matches", []);
    }

    public function testTheTestEndpointValidates(): void
    {
        $this->api("POST", "/api/blocklist/test", [])->assertStatus(422)->assertJsonValidationErrors(["url", "query"]);
    }
}
