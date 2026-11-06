<?php

namespace Tests\Feature;

use App\Search\BlocklistStore;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * POST /api/blocklist-changed: the blocklist service's change notice
 * (open-source/blocklist, docs/consumers.md, "Change notice"). It makes
 * MetaGer sync at once instead of at the next minute, and nothing else.
 */
class BlocklistChangedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            "metager.metager.blocklist.url" => "http://blocklist.test",
            "metager.metager.blocklist.token" => "metager-reader-token",
            "metager.metager.blocklist.webhook_token" => "hook-token-for-testing",
        ]);
        BlocklistStore::forget();
        Http::fake(["http://blocklist.test/*" => Http::response(
            ["format" => 1, "service" => "metager", "version" => 5, "changed_at" => null, "entries" => [["id" => 1, "kind" => "domain", "value" => "beispiel.de", "terms" => null]]],
            200,
            ["ETag" => '"metager-5"']
        )]);
    }

    protected function tearDown(): void
    {
        BlocklistStore::forget();
        parent::tearDown();
    }

    private function notify(?string $token = "hook-token-for-testing")
    {
        return $this->postJson("/api/blocklist-changed", ["service" => "metager", "version" => 5], $token === null ? [] : ["Authorization" => "Bearer $token"]);
    }

    public function testANoticeSyncsAtOnce(): void
    {
        $this->notify()->assertStatus(202);

        Http::assertSentCount(1);
        $this->assertSame('"metager-5"', BlocklistStore::etag());
    }

    public function testWithoutTheTokenNothingHappens(): void
    {
        $this->notify(null)->assertStatus(401);
        $this->notify("guessed")->assertStatus(401);

        Http::assertNothingSent();
    }

    public function testAnUnsetTokenRefusesEveryNotice(): void
    {
        config(["metager.metager.blocklist.webhook_token" => null]);

        $this->withHeader("Authorization", "Bearer ")->postJson("/api/blocklist-changed")->assertStatus(401);
        Http::assertNothingSent();
    }

    public function testTheBodyIsNotTrusted(): void
    {
        // Whatever version the notice names, MetaGer fetches and checks the
        // list itself; the notice only says "now".
        $this->postJson("/api/blocklist-changed", ["service" => "nolm", "version" => 999], ["Authorization" => "Bearer hook-token-for-testing"])->assertStatus(202);

        $this->assertSame(5, BlocklistStore::snapshot()["version"]);
    }

    public function testAFailedSyncStillAnswersTheNotice(): void
    {
        $this->app->forgetInstance(\Illuminate\Http\Client\Factory::class);
        Http::clearResolvedInstances();
        Http::fake(["http://blocklist.test/*" => Http::response("down", 503)]);

        $this->notify()->assertStatus(202);
        $this->assertNull(BlocklistStore::snapshot());
    }
}
