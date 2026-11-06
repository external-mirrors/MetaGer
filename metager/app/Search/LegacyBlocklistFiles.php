<?php

namespace App\Search;

/**
 * MetaGer's three old blacklist files, read as a snapshot's entries.
 *
 * Only for the time before the first `blocklist:sync`: the blocklist service
 * imported these same files (`blocklist:import-files` there), so once a sync
 * has stored its snapshot they are never read again. Parsed the way that
 * import parses them, so the list does not change shape at the switch:
 *
 *   blacklistDomains.txt         one host per line            → domain
 *   blacklistUrl.txt             host+path                    → url
 *                                host+path|query              → url_terms
 *   blacklistDescriptionUrl.txt  host+path                    → description
 *
 * Empty lines and `#` comments are skipped. Ids are negative, so they cannot
 * be mistaken for an entry of the service.
 */
class LegacyBlocklistFiles
{
    private const FILES = [
        "blacklistDomains.txt" => "domain",
        "blacklistUrl.txt" => "url",
        "blacklistDescriptionUrl.txt" => "description",
    ];

    public static function present(string $directory): bool
    {
        foreach (array_keys(self::FILES) as $name) {
            if (is_file($directory . DIRECTORY_SEPARATOR . $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{id: int, kind: string, value: string, terms: ?list<string>}>
     */
    public static function entries(string $directory): array
    {
        $entries = [];
        $id = 0;

        foreach (self::FILES as $name => $kind) {
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (!is_file($path)) {
                continue;
            }

            foreach (preg_split('/\r\n|\r|\n/', (string) file_get_contents($path)) as $line) {
                $line = trim($line);
                if ($line === "" || str_starts_with($line, "#")) {
                    continue;
                }

                $terms = null;
                $entryKind = $kind;
                if ($kind === "url" && str_contains($line, "|")) {
                    [$line, $query] = explode("|", $line, 2);
                    $entryKind = "url_terms";
                    $terms = BlocklistNormalizer::terms($query);
                    if ($terms === []) {
                        continue;
                    }
                }

                $value = $entryKind === "domain" ? BlocklistNormalizer::host($line) : BlocklistNormalizer::link($line);
                if ($value === "") {
                    continue;
                }

                $entries[] = ["id" => --$id, "kind" => $entryKind, "value" => $value, "terms" => $terms];
            }
        }

        return $entries;
    }
}
