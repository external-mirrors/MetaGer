<?php

namespace App\Search;

use Illuminate\Support\Facades\Log;

/**
 * The operator blocklist: which results MetaGer will not show, and whose
 * descriptions it will not print.
 *
 * The list is MetaGer's list in the blocklist service (open-source/blocklist),
 * which suma-crm edits. `artisan blocklist:sync` (BlocklistSync) fetches it
 * every minute, and at once when the service calls /api/blocklist-changed, and
 * stores the snapshot in Valkey (BlocklistStore). The service's
 * docs/matching.md is the matching rule; its worked examples run here too
 * (Tests\Unit\Search\BlocklistWorkedExamplesTest). Four kinds:
 *
 *   domain       a host — every result on it is dropped
 *   url          a host+path — that one page is dropped
 *   url_terms    a host+path plus a set of terms — that page is dropped when
 *                every term is among the query's words, in any order, among
 *                others ("max mustermann" also covers "Mustermann Max anwalt")
 *   description  a host+path — the result stays, its description is blanked
 *
 * ## How a lookup stays cheap
 *
 * The snapshot is compiled into hash maps — host → ids, link → ids,
 * link → term sets — so a lookup is one isset() per kind whatever the length
 * of the list, and terms are only looked at for a link that already hit. The
 * compiled list is stamped with the Valkey counter `blocklist:version`
 * (BlocklistVersion), which every sync that stores a new snapshot increments,
 * and kept per worker: in memory for long-running processes, and as an
 * OPcache-held PHP file per version for FPM, which keeps nothing in memory
 * between requests (see directory()). Each request reads the counter once;
 * only a changed counter costs reading and compiling the snapshot, once per
 * pod.
 *
 * ## Failure
 *
 * If Valkey cannot be asked, or has lost the snapshot, the last compiled list
 * stays in use. The filter falls back to the old state, never to an empty list
 * — a blocklist that silently empties is a removal request silently undone.
 *
 * ## Before the first sync
 *
 * Until a sync has stored a snapshot, the list is read from the three legacy
 * files in config/ (LegacyBlocklistFiles), which production still mounts. The
 * blocklist service imported the same files, so the switch changes nothing
 * that is blocked. Once anything was compiled from a snapshot, the files are
 * never read again on that pod.
 */
class Blacklists
{
    /**
     * The compiled snapshot, for the lifetime of the process — which under
     * FPM is one request; see directory() for what carries it further.
     *
     * Static, so tests must reset it: Tests\TestCase::setUp calls flush().
     *
     * @var ?array{domains: array<string, list<int>>, urls: array<string, list<int>>, descriptions: array<string, list<int>>, terms: array<string, list<array{0: int, 1: list<string>}>>}
     */
    private static ?array $snapshot = null;

    /** The `blocklist:version` the snapshot was compiled at; null if unknown. */
    private static ?string $snapshotVersion = null;

    /**
     * The snapshot as this instance resolved it. The instance is a container
     * singleton, so the version check happens once per request — and an edit
     * landing halfway through a request does not apply to half of its results.
     *
     * @var ?array{domains: array<string, list<int>>, urls: array<string, list<int>>, descriptions: array<string, list<int>>, terms: array<string, list<array{0: int, 1: list<string>}>>}
     */
    private ?array $resolved = null;

    /** @var array<string, array<string, true>> the query's normalised tokens, keyed by the raw query */
    private array $queryTokens = [];

    /**
     * Forget every snapshot, in memory and on disk. For tests: the version
     * counter lives in a Redis shared by the whole suite while each test has
     * a database of its own, so a snapshot stored by one test would be
     * "current" for the next one that writes nothing.
     */
    public static function flush(): void
    {
        self::$snapshot = null;
        self::$snapshotVersion = null;

        foreach (glob(self::directory() . DIRECTORY_SEPARATOR . "snapshot-*.php") ?: [] as $file) {
            @unlink($file);
        }
    }

    /**
     * Is this result blocked?
     *
     * The host has to be readable for any of it to apply: a link the url parser
     * could not take a host out of is never blocked, not even by an exact url
     * entry.
     *
     * @param string $strippedHost host with scheme, `www.`, port and query removed
     * @param string $strippedLink the same, plus the path
     * @param string $query the search term
     */
    public function blocksResult(string $strippedHost, string $strippedLink, string $query): bool
    {
        return $this->blockingEntryIds($strippedHost, $strippedLink, $query) !== [];
    }

    /**
     * Should this result be shown without its description?
     */
    public function blocksDescription(string $strippedLink): bool
    {
        return $this->descriptionEntryIds($strippedLink) !== [];
    }

    /**
     * Ids of every entry that drops this result for this query, for a log
     * line or a test that has to say which entry applied.
     *
     * @return list<int>
     */
    public function blockingEntryIds(string $strippedHost, string $strippedLink, string $query): array
    {
        if ($strippedHost === "") {
            return [];
        }

        $snapshot = $this->snapshot();
        $host = BlocklistNormalizer::strippedHost($strippedHost);
        $link = BlocklistNormalizer::strippedLink($strippedLink);

        $ids = array_merge($snapshot["domains"][$host] ?? [], $snapshot["urls"][$link] ?? []);

        if (isset($snapshot["terms"][$link])) {
            $tokens = $this->queryTokens[$query] ??= array_fill_keys(BlocklistNormalizer::terms($query), true);
            foreach ($snapshot["terms"][$link] as [$id, $terms]) {
                foreach ($terms as $term) {
                    if (!isset($tokens[$term])) {
                        continue 2;
                    }
                }
                $ids[] = $id;
            }
        }

        sort($ids);

        return $ids;
    }

    /**
     * Ids of every entry that blanks this result's description.
     *
     * @return list<int>
     */
    public function descriptionEntryIds(string $strippedLink): array
    {
        return $this->snapshot()["descriptions"][BlocklistNormalizer::strippedLink($strippedLink)] ?? [];
    }

    private function snapshot(): array
    {
        return $this->resolved ??= self::current();
    }

    /**
     * The snapshot for the current version, from the cheapest place that has it.
     */
    private static function current(): array
    {
        $version = BlocklistVersion::current();

        if ($version === null) {
            // Redis is down. Whatever was last current is the best answer.
            $snapshot = self::$snapshot ?? self::newestStored();
            if ($snapshot !== null) {
                return $snapshot;
            }
            Log::warning("blocklist: version unreadable and no list stored, reading the legacy files");
            return self::compileOrEmpty();
        }

        if (self::$snapshot !== null && self::$snapshotVersion === $version) {
            return self::$snapshot;
        }

        $snapshot = self::stored($version);
        if ($snapshot === null) {
            try {
                $snapshot = self::compile();
            } catch (\Throwable $e) {
                $fallback = self::$snapshot ?? self::newestStored();
                if ($fallback !== null) {
                    Log::warning("blocklist: reload for version $version failed, keeping the previous snapshot: " . $e->getMessage());
                    return $fallback;
                }
                Log::error("blocklist: no snapshot could be loaded, results are unfiltered: " . $e->getMessage());
                return self::empty();
            }
            self::store($version, $snapshot);
        }

        self::$snapshot = $snapshot;
        self::$snapshotVersion = $version;

        return $snapshot;
    }

    /**
     * Valkey is down and nothing was ever compiled here: the legacy files are
     * all there is, if they are mounted.
     */
    private static function compileOrEmpty(): array
    {
        try {
            return self::compileLegacy();
        } catch (\Throwable $e) {
            Log::error("blocklist: no list could be loaded, results are unfiltered: " . $e->getMessage());
            return self::empty();
        }
    }

    /**
     * Where compiled snapshots are kept between requests.
     *
     * PHP-FPM keeps nothing between requests, so the static above only lasts
     * one request there (it does last in queue workers and in tests). The
     * snapshot is therefore also written as a PHP file returning the array,
     * one per version: OPcache keeps a required file in shared memory, so
     * every request after the first in a pod gets it without a database
     * query and without even unserializing it. A version's file never
     * changes, so OPcache's timestamp checks cannot serve a stale one.
     */
    private static function directory(): string
    {
        return config("metager.metager.blocklist.compiled_directory") ?? storage_path("framework/cache/blocklist");
    }

    private static function path(string $version): string
    {
        return self::directory() . DIRECTORY_SEPARATOR . "snapshot-" . preg_replace('/[^0-9A-Za-z_-]/', "", $version) . ".php";
    }

    private static function stored(string $version): ?array
    {
        $path = self::path($version);
        if (!is_file($path)) {
            return null;
        }

        $snapshot = @include $path;

        return is_array($snapshot) ? $snapshot : null;
    }

    /**
     * The most recently written snapshot, whatever its version.
     */
    private static function newestStored(): ?array
    {
        $files = glob(self::directory() . DIRECTORY_SEPARATOR . "snapshot-*.php") ?: [];
        usort($files, fn(string $a, string $b) => (int) @filemtime($b) <=> (int) @filemtime($a));

        foreach ($files as $file) {
            $snapshot = @include $file;
            if (is_array($snapshot)) {
                return $snapshot;
            }
        }

        return null;
    }

    /**
     * Write the snapshot for $version, atomically, and drop older ones.
     *
     * A failure here costs speed, not correctness: the next request compiles
     * again.
     */
    private static function store(string $version, array $snapshot): void
    {
        $directory = self::directory();
        $path = self::path($version);

        try {
            if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new \RuntimeException("cannot create $directory");
            }

            $temporary = $path . "." . bin2hex(random_bytes(4)) . ".tmp";
            if (@file_put_contents($temporary, "<?php return " . var_export($snapshot, true) . ";\n") === false) {
                throw new \RuntimeException("cannot write $temporary");
            }
            if (!@rename($temporary, $path)) {
                @unlink($temporary);
                throw new \RuntimeException("cannot rename $temporary");
            }
        } catch (\Throwable $e) {
            Log::warning("blocklist: snapshot not stored: " . $e->getMessage());
            return;
        }

        // Keep a few, not one: a worker that read the version a moment before
        // the latest write may still be about to require the previous file.
        $files = glob($directory . DIRECTORY_SEPARATOR . "snapshot-*.php") ?: [];
        usort($files, fn(string $a, string $b) => (int) @filemtime($b) <=> (int) @filemtime($a));
        foreach (array_slice($files, 3) as $old) {
            @unlink($old);
        }
    }

    /**
     * The list from the snapshot `blocklist:sync` stored in Valkey.
     *
     * Without one, it depends on what came before. If this worker or this pod
     * has compiled a list already, the snapshot was lost (Valkey restarted),
     * and throwing makes current() keep that list until the next sync. If
     * nothing was ever compiled here, there has been no sync yet, and the
     * legacy files are the list.
     *
     * @throws \Throwable when Valkey cannot be read, or has lost the snapshot
     */
    private static function compile(): array
    {
        $snapshot = BlocklistStore::snapshot();
        if ($snapshot !== null) {
            return self::compileEntries($snapshot["entries"]);
        }

        if (self::$snapshot !== null || self::newestStored() !== null) {
            throw new \RuntimeException("no synced snapshot in Valkey");
        }

        return self::compileLegacy();
    }

    private static function compileLegacy(): array
    {
        $directory = config("metager.metager.blocklist.legacy_directory") ?? config_path();

        return self::compileEntries(LegacyBlocklistFiles::entries($directory));
    }

    /**
     * Hash maps from a snapshot's entries (open-source/blocklist,
     * docs/snapshot.md). Values there are already normalised.
     *
     * @param list<array{id: int, kind: string, value: string, terms: ?list<string>}> $entries
     */
    private static function compileEntries(array $entries): array
    {
        $snapshot = self::empty();

        foreach ($entries as $entry) {
            $id = (int) ($entry["id"] ?? 0);
            $value = (string) ($entry["value"] ?? "");
            if ($value === "") {
                continue;
            }
            switch ($entry["kind"] ?? null) {
                case "domain":
                    $snapshot["domains"][$value][] = $id;
                    break;
                case "url":
                    $snapshot["urls"][$value][] = $id;
                    break;
                case "description":
                    $snapshot["descriptions"][$value][] = $id;
                    break;
                case "url_terms":
                    $terms = array_values(array_filter((array) ($entry["terms"] ?? []), fn($t) => is_string($t) && $t !== ""));
                    // An entry without terms would block the page for every
                    // query, which is what `url` is for; the service never
                    // sends one, and one that arrives anyway must not widen.
                    if ($terms !== []) {
                        $snapshot["terms"][$value][] = [$id, $terms];
                    }
                    break;
                // A kind this code does not know is skipped: a newer service
                // may send one (docs/matching.md there).
            }
        }

        return $snapshot;
    }

    private static function empty(): array
    {
        return ["domains" => [], "urls" => [], "descriptions" => [], "terms" => []];
    }
}
