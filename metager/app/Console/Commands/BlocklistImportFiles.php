<?php

namespace App\Console\Commands;

use App\Search\BlocklistFileImport;
use Illuminate\Console\Command;

/**
 * Import the legacy blacklist files by hand — see App\Search\BlocklistFileImport.
 *
 * The first deploy does this on its own: the data migration
 * 2026_11_03_000001_import_legacy_blocklist_files imports the mounted files
 * from config/. This is for doing it again, or from somewhere else:
 *
 *   php artisan blocklist:import-files /metager/metager_app/config
 */
class BlocklistImportFiles extends Command
{
    protected $signature = "blocklist:import-files {dir : directory holding the blacklist*.txt files}";

    protected $description = "Import the legacy blacklist text files into the blocklist table";

    public function handle(BlocklistFileImport $import): int
    {
        $directory = (string) $this->argument("dir");

        if (!is_dir($directory)) {
            $this->error("No such directory: $directory");
            return self::FAILURE;
        }

        $counts = $import->import($directory, fn(string $level, string $message) => $level === "warn"
            ? $this->warn($message)
            : $this->info($message));

        $this->info("{$counts["created"]} created, {$counts["existing"]} already present, {$counts["skipped"]} skipped");

        return self::SUCCESS;
    }
}
