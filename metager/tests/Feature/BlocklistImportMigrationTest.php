<?php

namespace Tests\Feature;

use App\Models\BlocklistEntry;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * The data migration that imports the legacy blacklist files on the deploy
 * that introduces the table, so searches never read an empty blocklist.
 *
 * It reads config_path(), so the test points the application's config path at
 * a temporary directory rather than writing into the real config/.
 */
class BlocklistImportMigrationTest extends TestCase
{
    use UsesInMemorySqlite;

    private const MIGRATION = "2026_11_03_000001_import_legacy_blocklist_files.php";

    private string $dir;
    private string $configPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();

        $this->configPath = $this->app->configPath();
        $this->dir = sys_get_temp_dir() . "/blocklist-migration-" . uniqid();
        mkdir($this->dir);
        $this->app->useConfigPath($this->dir);
    }

    protected function tearDown(): void
    {
        $this->app->useConfigPath($this->configPath);
        foreach (glob($this->dir . "/*") as $file) {
            unlink($file);
        }
        rmdir($this->dir);
        parent::tearDown();
    }

    private function migrate(): void
    {
        (require database_path("migrations/" . self::MIGRATION))->up();
    }

    private function writeFiles(): void
    {
        file_put_contents($this->dir . "/blacklistDomains.txt", "beispiel.de\n");
        file_put_contents($this->dir . "/blacklistUrl.txt", "example.org/kaffee\nexample.org/espresso|kaffee\n");
        file_put_contents($this->dir . "/blacklistDescriptionUrl.txt", "example.org/milch\n");
    }

    public function testItRunsAfterTheTableMigration(): void
    {
        $files = array_map("basename", glob(database_path("migrations/2026_11_03_*.php")));

        $this->assertSame([
            "2026_11_03_000000_create_blocklist_entries_table.php",
            self::MIGRATION,
        ], $files);
    }

    public function testFilesPresentAreImportedOnce(): void
    {
        $this->writeFiles();

        $this->migrate();

        $this->assertSame(
            [["domain", "beispiel.de"], ["url", "example.org/kaffee"], ["url_terms", "example.org/espresso"], ["description", "example.org/milch"]],
            BlocklistEntry::query()->orderBy("id")->get()->map(fn($e) => [$e->kind, $e->value])->all()
        );
        $this->assertSame(["imported"], BlocklistEntry::query()->distinct()->pluck("note")->all());
    }

    public function testRunningItAgainAddsNoDuplicates(): void
    {
        $this->writeFiles();

        $this->migrate();
        $this->migrate();

        $this->assertSame(4, BlocklistEntry::withTrashed()->count());
    }

    public function testWithoutTheFilesItDoesNothing(): void
    {
        $this->migrate();

        $this->assertSame(0, BlocklistEntry::withTrashed()->count());
    }
}
