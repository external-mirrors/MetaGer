<?php

namespace Tests\Feature;

use App\Models\BlocklistEntry;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * `artisan blocklist:import-files` — the one-off move of the three legacy
 * blacklist files into the table.
 */
class BlocklistImportFilesTest extends TestCase
{
    use UsesInMemorySqlite;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();

        $this->dir = sys_get_temp_dir() . "/blocklist-import-" . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . "/*") as $file) {
            unlink($file);
        }
        rmdir($this->dir);
        parent::tearDown();
    }

    private function write(string $name, string $contents): void
    {
        file_put_contents($this->dir . "/" . $name, $contents);
    }

    private function writeAll(): void
    {
        $this->write("blacklistDomains.txt", "beispiel.de\n  www.Spam.example  \n# a comment\n\nbeispiel.de\n");
        $this->write("blacklistUrl.txt", "example.org/kaffee\r\nexample.org/espresso|Kaffee Bohnen\r\n#example.org/tee\r\n");
        $this->write("blacklistDescriptionUrl.txt", "example.org/milch/\n");
    }

    /** @return list<array<string, mixed>> */
    private function entries(): array
    {
        return BlocklistEntry::query()->orderBy("id")->get()
            ->map(fn(BlocklistEntry $e) => [$e->kind, $e->value, $e->terms, $e->reason, $e->note])
            ->all();
    }

    public function testEachLineBecomesANormalisedEntry(): void
    {
        $this->writeAll();

        $this->artisan("blocklist:import-files", ["dir" => $this->dir])->assertSuccessful();

        $this->assertSame([
            ["domain", "beispiel.de", null, "other", "imported"],
            ["domain", "spam.example", null, "other", "imported"],
            ["url", "example.org/kaffee", null, "other", "imported"],
            ["url_terms", "example.org/espresso", "bohnen kaffee", "other", "imported"],
            ["description", "example.org/milch", null, "other", "imported"],
        ], $this->entries());
    }

    public function testRunningItTwiceChangesNothing(): void
    {
        $this->writeAll();

        $this->artisan("blocklist:import-files", ["dir" => $this->dir])->assertSuccessful();
        $first = $this->entries();

        $this->artisan("blocklist:import-files", ["dir" => $this->dir])
            ->expectsOutputToContain("0 created, 6 already present")
            ->assertSuccessful();

        $this->assertSame($first, $this->entries());
    }

    /**
     * A deleted entry is history, not a reason to skip: re-importing a file
     * after someone removed an entry through the API brings it back, which is
     * what an import of that file means.
     */
    public function testADeletedEntryDoesNotCountAsPresent(): void
    {
        $this->write("blacklistDomains.txt", "beispiel.de\n");
        $this->artisan("blocklist:import-files", ["dir" => $this->dir])->assertSuccessful();
        BlocklistEntry::first()->delete();

        $this->artisan("blocklist:import-files", ["dir" => $this->dir])->assertSuccessful();

        $this->assertSame(1, BlocklistEntry::count());
        $this->assertSame(2, BlocklistEntry::withTrashed()->count());
    }

    public function testMissingFilesAreSkipped(): void
    {
        $this->write("blacklistUrl.txt", "example.org/kaffee\n");

        $this->artisan("blocklist:import-files", ["dir" => $this->dir])
            ->expectsOutputToContain("blacklistDomains.txt: not found")
            ->assertSuccessful();

        $this->assertSame([["url", "example.org/kaffee", null, "other", "imported"]], $this->entries());
    }

    public function testAQueryWithoutWordsIsSkippedRatherThanWidenedToTheWholePage(): void
    {
        $this->write("blacklistUrl.txt", "example.org/kaffee|--\n");

        $this->artisan("blocklist:import-files", ["dir" => $this->dir])->assertSuccessful();

        $this->assertSame([], $this->entries());
    }

    public function testAMissingDirectoryFails(): void
    {
        $this->artisan("blocklist:import-files", ["dir" => $this->dir . "/nope"])->assertFailed();
    }
}
