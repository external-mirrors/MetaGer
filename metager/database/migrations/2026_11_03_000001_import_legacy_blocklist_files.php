<?php

use App\Search\BlocklistFileImport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Imports the three legacy blacklist files on the deploy that introduces the
 * table, so there is no window in which searches read an empty blocklist.
 *
 * In production the files are subPath mounts from the `secrets` Secret at
 * config/, and every container that runs `migrate` mounts them — the fpm
 * container through its entrypoint, the scheduler and queue through
 * chart.migrateInitContainer. Where there are none (a checkout, CI, a test's
 * in-memory database) this does nothing.
 *
 * Idempotent like the import itself, so running it against a database that
 * already has the entries adds nothing. The mounts are removed in a later
 * release, once this has run in production.
 */
return new class extends Migration
{
    public function up(): void
    {
        $directory = config_path();

        $present = array_filter(
            array_keys(BlocklistFileImport::FILES),
            fn(string $name) => is_file($directory . DIRECTORY_SEPARATOR . $name)
        );
        if ($present === []) {
            return;
        }

        $counts = app(BlocklistFileImport::class)->import($directory);

        Log::info(sprintf(
            "blocklist: imported legacy files from %s: %d created, %d already present, %d skipped",
            $directory,
            $counts["created"],
            $counts["existing"],
            $counts["skipped"]
        ));
    }

    public function down(): void
    {
        // Imported entries are ordinary entries now; nothing to undo.
    }
};
