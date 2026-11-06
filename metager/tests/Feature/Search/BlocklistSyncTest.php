<?php

namespace Tests\Feature\Search;

use App\Search\BlocklistStore;
use App\Search\BlocklistVersion;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * `artisan blocklist:sync`: fetching MetaGer's list from the blocklist service
 * (open-source/blocklist, docs/consumers.md there) into Valkey.
 *
 * The rule under test, the same as for the filter itself: a failed fetch keeps
 * the list MetaGer had. It never replaces it with an empty one.
 */
class BlocklistSyncTest extends TestCase
{
    private const URL = "http://blocklist.test";

    protected function setUp(): void
    {
        parent::setUp();
        config([
            "metager.metager.blocklist.url" => self::URL,
            "metager.metager.blocklist.token" => "metager-reader-token",
        ]);
        BlocklistStore::forget();
    }

    protected function tearDown(): void
    {
        BlocklistStore::forget();
        parent::tearDown();
    }

    /** @param list<array<string, mixed>> $entries */
    private function snapshot(int $version, array $entries = []): array
    {
        return ["format" => 1, "service" => "metager", "version" => $version, "changed_at" => "2026-11-06T10:00:00+00:00", "entries" => $entries];
    }

    private function entry(int $id = 7): array
    {
        return ["id" => $id, "kind" => "domain", "value" => "beispiel.de", "terms" => null];
    }

    public function testAListIsStoredAndTheVersionMoves(): void
    {
        Http::fake([self::URL . "/api/lists/metager" => Http::response($this->snapshot(3, [$this->entry()]), 200, ["ETag" => '"metager-3"'])]);
        $before = BlocklistVersion::current();

        $this->artisan("blocklist:sync")->expectsOutputToContain("version 3, 1 entries")->assertSuccessful();

        $this->assertSame([$this->entry()], BlocklistStore::snapshot()["entries"]);
        $this->assertSame('"metager-3"', BlocklistStore::etag());
        $this->assertNotSame($before, BlocklistVersion::current());
        Http::assertSent(fn(Request $r) => $r->hasHeader("Authorization", "Bearer metager-reader-token") && !$r->hasHeader("If-None-Match"));
    }

    public function testTheLastEtagIsSentAndNotModifiedChangesNothing(): void
    {
        Http::fake([self::URL . "/*" => Http::sequence()
            ->push($this->snapshot(3, [$this->entry()]), 200, ["ETag" => 'W/"metager-3"'])
            ->push("", 304, ["ETag" => 'W/"metager-3"'])]);
        $this->artisan("blocklist:sync")->assertSuccessful();
        $version = BlocklistVersion::current();

        $this->artisan("blocklist:sync")->expectsOutputToContain("unchanged")->assertSuccessful();

        $this->assertSame($version, BlocklistVersion::current());
        Http::assertSent(fn(Request $r) => $r->hasHeader("If-None-Match", 'W/"metager-3"'));
    }

    /** @return array<string, array{0: \Closure}> */
    public static function failures(): array
    {
        return [
            "server error" => [fn() => Http::response("down", 503)],
            "unauthorized" => [fn() => Http::response(["error" => "Unauthorized"], 401)],
            "not JSON" => [fn() => Http::response("<html>", 200)],
            "another service's list" => [fn() => Http::response(["format" => 1, "service" => "nolm", "version" => 9, "changed_at" => null, "entries" => []], 200)],
            "a format this code does not know" => [fn() => Http::response(["format" => 2, "service" => "metager", "version" => 9, "changed_at" => null, "entries" => []], 200)],
            "entries missing" => [fn() => Http::response(["format" => 1, "service" => "metager", "version" => 9, "changed_at" => null], 200)],
            "connection refused" => [fn() => throw new \Illuminate\Http\Client\ConnectionException("refused")],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider("failures")]
    public function testAFailedFetchKeepsTheListMetagerHad(\Closure $answer): void
    {
        Http::fake([self::URL . "/*" => Http::response($this->snapshot(3, [$this->entry()]), 200, ["ETag" => '"metager-3"'])]);
        $this->artisan("blocklist:sync")->assertSuccessful();
        $version = BlocklistVersion::current();

        $this->app->forgetInstance(\Illuminate\Http\Client\Factory::class);
        Http::clearResolvedInstances();
        Http::fake([self::URL . "/*" => $answer]);
        $this->artisan("blocklist:sync")->assertFailed();

        $this->assertSame([$this->entry()], BlocklistStore::snapshot()["entries"]);
        $this->assertSame('"metager-3"', BlocklistStore::etag());
        $this->assertSame($version, BlocklistVersion::current());
    }

    public function testAnEmptyListFromTheServiceIsALegitimateList(): void
    {
        // Only a failed fetch keeps the old list. A list the service says is
        // empty is the list: every entry was deleted on purpose.
        Http::fake([self::URL . "/*" => Http::sequence()
            ->push($this->snapshot(3, [$this->entry()]), 200, ["ETag" => '"metager-3"'])
            ->push($this->snapshot(4), 200, ["ETag" => '"metager-4"'])]);

        $this->artisan("blocklist:sync")->assertSuccessful();
        $this->artisan("blocklist:sync")->assertSuccessful();

        $this->assertSame([], BlocklistStore::snapshot()["entries"]);
    }

    public function testWithoutAUrlThereIsNothingToSync(): void
    {
        config(["metager.metager.blocklist.url" => null]);
        Http::fake();

        $this->artisan("blocklist:sync")->expectsOutputToContain("not configured")->assertSuccessful();

        Http::assertNothingSent();
        $this->assertNull(BlocklistStore::snapshot());
    }

    public function testItIsScheduledEveryMinute(): void
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->filter(fn($e) => str_contains($e->command ?? "", "blocklist:sync"));

        $this->assertCount(1, $events);
        $this->assertSame("* * * * *", $events->first()->expression);
    }
}
