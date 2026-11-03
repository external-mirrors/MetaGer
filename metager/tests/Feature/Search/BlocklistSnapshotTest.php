<?php

namespace Tests\Feature\Search;

use App\Models\BlocklistEntry;
use App\Search\Blacklists;
use App\Search\BlocklistVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * How Blacklists keeps its snapshot, and what it falls back to.
 *
 * The rule under test: when Redis or the database cannot be asked, the filter
 * keeps the last list it had. It never falls back to an empty one, because an
 * empty blocklist is every removal silently undone.
 *
 * "A new request" is simulated by a fresh instance with the in-memory static
 * forgotten, which is what PHP-FPM gives each request; what carries the
 * snapshot across is then the stored file alone.
 */
class BlocklistSnapshotTest extends TestCase
{
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    protected function tearDown(): void
    {
        Redis::clearResolvedInstances();
        parent::tearDown();
    }

    private function nextRequest(): Blacklists
    {
        $reflection = new \ReflectionClass(Blacklists::class);
        $reflection->setStaticPropertyValue("snapshot", null);
        $reflection->setStaticPropertyValue("snapshotVersion", null);

        return new Blacklists();
    }

    private function blocksBeispiel(Blacklists $blacklists): bool
    {
        return $blacklists->blocksResult("beispiel.de", "beispiel.de/sorten", "kaffee");
    }

    private function breakRedis(): void
    {
        $this->app->instance("redis", new class {
            public function connection(?string $name = null): never
            {
                throw new \Predis\Connection\ConnectionException(new \Predis\Connection\StreamConnection(new \Predis\Connection\Parameters()), "Redis is down");
            }

            public function __call(string $method, array $arguments): never
            {
                $this->connection();
            }
        });
        Redis::clearResolvedInstances();
    }

    private function blockBeispiel(): BlocklistEntry
    {
        return BlocklistEntry::create(["kind" => "domain", "value" => "beispiel.de", "reason" => "other"]);
    }

    public function testAnEntryBlocksFromTheNextRequestOn(): void
    {
        $this->assertFalse($this->blocksBeispiel($this->nextRequest()));

        $this->blockBeispiel();

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));
    }

    public function testARequestKeepsTheListItStartedWith(): void
    {
        $blacklists = $this->nextRequest();
        $this->assertFalse($this->blocksBeispiel($blacklists));

        $this->blockBeispiel();

        $this->assertFalse($this->blocksBeispiel($blacklists), "An edit applied halfway through a request.");
    }

    public function testAnUnchangedVersionDoesNotReadTheTable(): void
    {
        $this->blockBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        DB::enableQueryLog();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        $this->assertSame([], DB::getQueryLog(), "The stored snapshot was not used; every search would query the database.");
    }

    public function testWithRedisDownTheLastSnapshotStays(): void
    {
        $this->blockBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        $this->breakRedis();
        // Even with the table emptied behind its back: the snapshot is what counts.
        DB::table("blocklist_entries")->delete();

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()), "Redis down emptied the blocklist.");
    }

    public function testWithTheDatabaseDownTheLastSnapshotStays(): void
    {
        $this->blockBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        BlocklistVersion::bump();
        DB::statement("DROP TABLE blocklist_entries");

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()), "A failed reload emptied the blocklist.");
    }

    public function testWithNothingEverLoadedAndNoDatabaseNothingIsBlockedAndNothingThrows(): void
    {
        DB::statement("DROP TABLE blocklist_entries");

        $this->assertFalse($this->blocksBeispiel($this->nextRequest()));
    }

    /**
     * Valkey losing its data must not resurrect an old snapshot: a counter
     * restarting from zero would name a file stored long ago as current.
     */
    public function testALostVersionCounterDoesNotReviveAnOldSnapshot(): void
    {
        $entry = $this->blockBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        Redis::connection()->del(BlocklistVersion::KEY);
        // Removed without a bump, as if the bump had been lost with the data.
        DB::table("blocklist_entries")->where("id", $entry->id)->delete();

        $this->assertFalse($this->blocksBeispiel($this->nextRequest()));
    }

    public function testTheBypassAppliesToOneInstanceOnly(): void
    {
        $this->blockBeispiel();
        $bypassed = $this->nextRequest();

        $inside = $bypassed->withoutBlocking(fn() => [
            $this->blocksBeispiel($bypassed),
            $this->blocksBeispiel(new Blacklists()),
            $bypassed->blockingEntryIds("beispiel.de", "beispiel.de/sorten", "kaffee") !== [],
        ]);

        $this->assertSame([false, true, true], $inside);
        $this->assertTrue($this->blocksBeispiel($bypassed), "The bypass outlived its callback.");
    }
}
