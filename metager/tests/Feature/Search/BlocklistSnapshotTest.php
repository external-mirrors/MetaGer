<?php

namespace Tests\Feature\Search;

use App\Search\Blacklists;
use App\Search\BlocklistStore;
use App\Search\BlocklistVersion;
use Illuminate\Support\Facades\Redis;
use Tests\Concerns\FakesBlocklist;
use Tests\TestCase;

/**
 * How Blacklists keeps its compiled list, and what it falls back to.
 *
 * The rule under test: when Valkey cannot be asked or has lost the synced
 * snapshot, the filter keeps the last list it had. It never falls back to an
 * empty one, because an empty blocklist is every removal silently undone.
 * Before the very first sync it reads the legacy blacklist files.
 *
 * "A new request" is simulated by a fresh instance with the in-memory static
 * forgotten, which is what PHP-FPM gives each request; what carries the
 * compiled list across is then the stored file alone.
 */
class BlocklistSnapshotTest extends TestCase
{
    use FakesBlocklist;

    private string $legacy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->legacy = sys_get_temp_dir() . "/blocklist-legacy-" . uniqid();
        mkdir($this->legacy);
        config(["metager.metager.blocklist.legacy_directory" => $this->legacy]);
    }

    protected function tearDown(): void
    {
        // Back to the real Valkey before FakesBlocklist cleans up in it.
        $this->app->forgetInstance("redis");
        Redis::clearResolvedInstances();
        array_map("unlink", glob($this->legacy . "/*") ?: []);
        rmdir($this->legacy);
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

    private function blockBeispiel(): int
    {
        return $this->blocklistEntry("domain", "beispiel.de");
    }

    private function legacyFileBlocksBeispiel(): void
    {
        file_put_contents($this->legacy . "/blacklistDomains.txt", "beispiel.de\n");
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

    public function testAnUnchangedVersionDoesNotReadTheSnapshotAgain(): void
    {
        $this->blockBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        // Gone without a bump: only a re-read would notice.
        Redis::connection()->del(BlocklistStore::SNAPSHOT);

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()), "The stored compiled list was not used; every search would read and compile the snapshot.");
    }

    public function testWithRedisDownTheLastListStays(): void
    {
        $this->blockBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        $this->breakRedis();

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()), "Redis down emptied the blocklist.");
    }

    public function testWithTheSyncedSnapshotLostTheLastListStays(): void
    {
        $this->blockBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        Redis::connection()->del(BlocklistStore::SNAPSHOT);
        BlocklistVersion::bump();

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()), "Losing the snapshot emptied the blocklist.");
    }

    public function testBeforeTheFirstSyncTheLegacyFilesApply(): void
    {
        $this->legacyFileBlocksBeispiel();

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));
    }

    public function testTheFirstSyncReplacesTheLegacyFiles(): void
    {
        $this->legacyFileBlocksBeispiel();
        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));

        $this->blocklistEntry("domain", "anderes.example");

        $this->assertFalse($this->blocksBeispiel($this->nextRequest()), "The legacy files still applied after a sync.");
    }

    public function testALostSnapshotFallsBackToTheLastListNotToTheLegacyFiles(): void
    {
        $this->legacyFileBlocksBeispiel();
        $id = $this->blockBeispiel();
        $this->removeBlocklistEntry($id);
        $this->assertFalse($this->blocksBeispiel($this->nextRequest()));

        Redis::connection()->del(BlocklistStore::SNAPSHOT);
        BlocklistVersion::bump();

        $this->assertFalse($this->blocksBeispiel($this->nextRequest()), "A removal was undone by the old files.");
    }

    public function testWithNothingAnywhereNothingIsBlockedAndNothingThrows(): void
    {
        $this->assertFalse($this->blocksBeispiel($this->nextRequest()));
    }

    public function testWithRedisDownAndNothingStoredTheLegacyFilesApply(): void
    {
        $this->legacyFileBlocksBeispiel();
        $this->breakRedis();

        $this->assertTrue($this->blocksBeispiel($this->nextRequest()));
    }
}
