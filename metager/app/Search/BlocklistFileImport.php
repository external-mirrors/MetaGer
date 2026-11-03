<?php

namespace App\Search;

use App\Models\BlocklistEntry;

/**
 * Moves the three legacy blacklist files into `blocklist_entries`.
 *
 *   blacklistDomains.txt         one host per line            → domain
 *   blacklistUrl.txt             host+path                    → url
 *                                host+path|query              → url_terms
 *   blacklistDescriptionUrl.txt  host+path                    → description
 *
 * Every entry gets reason `other` and note `imported`; empty lines and `#`
 * comments are skipped. Values and terms are normalised the way the API
 * normalises them, so lines that differ only by `www.`, case or a trailing
 * slash become one entry.
 *
 * Idempotent: an entry that already exists (and is not deleted) is left
 * alone. Used by `artisan blocklist:import-files` and by the data migration
 * that imports the mounted files on the first deploy.
 */
class BlocklistFileImport
{
    public const FILES = [
        "blacklistDomains.txt" => BlocklistEntry::KIND_DOMAIN,
        "blacklistUrl.txt" => BlocklistEntry::KIND_URL,
        "blacklistDescriptionUrl.txt" => BlocklistEntry::KIND_DESCRIPTION,
    ];

    /**
     * @param ?callable(string $level, string $message): void $report
     * @return array{created: int, existing: int, skipped: int, files: int}
     */
    public function import(string $directory, ?callable $report = null): array
    {
        $report ??= fn() => null;
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        $counts = ["created" => 0, "existing" => 0, "skipped" => 0, "files" => 0];

        foreach (self::FILES as $name => $kind) {
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (!is_file($path)) {
                $report("warn", "$name: not found, skipped");
                continue;
            }
            $counts["files"]++;

            $fileCreated = 0;
            foreach (preg_split('/\r\n|\r|\n/', (string) file_get_contents($path)) as $number => $line) {
                $line = trim($line);
                if ($line === "" || str_starts_with($line, "#")) {
                    continue;
                }

                [$entryKind, $value, $terms] = $this->parse($kind, $line);

                if ($value === "" || ($entryKind === BlocklistEntry::KIND_URL_TERMS && $terms === "")) {
                    $report("warn", sprintf("%s:%d: nothing usable in \"%s\", skipped", $name, $number + 1, $line));
                    $counts["skipped"]++;
                    continue;
                }

                if (BlocklistEntry::activeDuplicate($entryKind, $value, $terms) !== null) {
                    $counts["existing"]++;
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
                $counts["created"]++;
                $fileCreated++;
            }

            $report("info", "$name: $fileCreated imported");
        }

        return $counts;
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
