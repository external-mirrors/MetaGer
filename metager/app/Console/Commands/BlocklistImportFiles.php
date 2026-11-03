<?php

namespace App\Console\Commands;

use App\Models\BlocklistEntry;
use Illuminate\Console\Command;

/**
 * One-off: move the three legacy blacklist files into `blocklist_entries`.
 *
 *   blacklistDomains.txt         one host per line            → domain
 *   blacklistUrl.txt             host+path                    → url
 *                                host+path|query              → url_terms
 *   blacklistDescriptionUrl.txt  host+path                    → description
 *
 * Every entry gets reason `other` and note `imported`; empty lines and `#`
 * comments are skipped. Values and terms are normalised the way the API
 * normalises them (BlocklistEntry::normalizeValue / normalizeTerms), so a
 * line that only differed from another by `www.`, case or a trailing slash
 * becomes one entry.
 *
 * Idempotent: an entry that already exists (and is not deleted) is left
 * alone, so running it twice, or after suma-crm has added entries, is safe.
 *
 * Run once per environment, with the files still mounted, before the chart
 * stops mounting them:
 *
 *   php artisan blocklist:import-files /metager/metager_app/config
 */
class BlocklistImportFiles extends Command
{
    protected $signature = "blocklist:import-files {dir : directory holding the blacklist*.txt files}";

    protected $description = "Import the legacy blacklist text files into the blocklist table";

    private const FILES = [
        "blacklistDomains.txt" => BlocklistEntry::KIND_DOMAIN,
        "blacklistUrl.txt" => BlocklistEntry::KIND_URL,
        "blacklistDescriptionUrl.txt" => BlocklistEntry::KIND_DESCRIPTION,
    ];

    public function handle(): int
    {
        $directory = rtrim((string) $this->argument("dir"), DIRECTORY_SEPARATOR);

        if (!is_dir($directory)) {
            $this->error("No such directory: $directory");
            return self::FAILURE;
        }

        $created = 0;
        $existing = 0;
        $skipped = 0;

        foreach (self::FILES as $name => $kind) {
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (!is_file($path)) {
                $this->warn("$name: not found, skipped");
                continue;
            }

            $fileCreated = 0;
            foreach (preg_split('/\r\n|\r|\n/', (string) file_get_contents($path)) as $number => $line) {
                $line = trim($line);
                if ($line === "" || str_starts_with($line, "#")) {
                    continue;
                }

                [$entryKind, $value, $terms] = $this->parse($kind, $line);

                if ($value === "" || ($entryKind === BlocklistEntry::KIND_URL_TERMS && $terms === "")) {
                    $this->warn(sprintf("%s:%d: nothing usable in \"%s\", skipped", $name, $number + 1, $line));
                    $skipped++;
                    continue;
                }

                if (BlocklistEntry::activeDuplicate($entryKind, $value, $terms) !== null) {
                    $existing++;
                    continue;
                }

                BlocklistEntry::create([
                    "kind" => $entryKind,
                    "value" => $value,
                    "terms" => $terms,
                    "reason" => "other",
                    "note" => "imported",
                    "created_by" => "blocklist:import-files",
                ]);
                $created++;
                $fileCreated++;
            }

            $this->info("$name: $fileCreated imported");
        }

        $this->info("$created created, $existing already present, $skipped skipped");

        return self::SUCCESS;
    }

    /**
     * @return array{0: string, 1: string, 2: ?string} kind, value, terms
     */
    private function parse(string $kind, string $line): array
    {
        if ($kind === BlocklistEntry::KIND_URL && str_contains($line, "|")) {
            [$page, $query] = explode("|", $line, 2);

            return [
                BlocklistEntry::KIND_URL_TERMS,
                BlocklistEntry::normalizeValue(BlocklistEntry::KIND_URL_TERMS, $page),
                BlocklistEntry::normalizeTerms(BlocklistEntry::KIND_URL_TERMS, $query),
            ];
        }

        return [$kind, BlocklistEntry::normalizeValue($kind, $line), null];
    }
}
