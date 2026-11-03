<?php

namespace App\Search;

use App\Models\BlocklistEntry;
use Illuminate\Support\Facades\Log;

/**
 * The operator blocklist: which results MetaGer will not show, and whose
 * descriptions it will not print.
 *
 * The entries live in the `blocklist_entries` table (App\Models\BlocklistEntry)
 * and are edited live by suma-crm through /api/blocklist — docs/blocklist-api.md
 * is the contract. Four kinds:
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
 * The table is compiled into a snapshot of hash maps — host → ids, link → ids,
 * link → term sets — so a lookup is one isset() per kind whatever the length
 * of the list, and terms are only looked at for a link that already hit. The
 * snapshot is stamped with the Redis counter `blocklist:version`
 * (BlocklistVersion), which every write increments, and kept per worker: in
 * memory for long-running processes, and as an OPcache-held PHP file per
 * version for FPM, which keeps nothing in memory between requests (see
 * directory()). Each request reads the counter once; only a changed counter
 * costs a database read, once per pod. So the database is off the result path
 * except right after an edit.
 *
 * ## Failure
 *
 * If Redis cannot be asked, the last snapshot stays in use; if the database
 * cannot be read, likewise. The filter falls back to the old state, never to
 * an empty list — a blocklist that silently empties is a removal request
 * silently undone. Only a worker that has never managed to load once runs
 * without one, and logs it on every request until it does.
 *
 * ## What changed with the move from the three files
 *
 * The files (blacklistDomains.txt, blacklistUrl.txt with its `page|query`
 * lines, blacklistDescriptionUrl.txt) were imported by
 * `artisan blocklist:import-files` and are not read any more. Two quirks
 * went with them, on purpose: the url list was not trimmed, and neither of
 * the first two lists loaded unless both files existed. Values are also
 * normalised on both sides now (BlocklistNormalizer) — host lowercased,
 * `www.` and a trailing slash dropped — so `www.example.org/a/` and
 * `example.org/a` are one entry, and query terms are compared as words,
 * folded for case and diacritics, rather than as one exact lowercased string.
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

    /** Set while {@see withoutBlocking} runs. */
    private bool $bypassed = false;

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
        if ($this->bypassed) {
            return false;
        }

        return $this->blockingEntryIds($strippedHost, $strippedLink, $query) !== [];
    }

    /**
     * Should this result be shown without its description?
     */
    public function blocksDescription(string $strippedLink): bool
    {
        if ($this->bypassed) {
            return false;
        }

        return $this->descriptionEntryIds($strippedLink) !== [];
    }

    /**
     * Ids of every entry that drops this result for this query, ignoring any
     * bypass. What /api/blocklist/test and /check report.
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
     * Ids of every entry that blanks this result's description, ignoring any
     * bypass.
     *
     * @return list<int>
     */
    public function descriptionEntryIds(string $strippedLink): array
    {
        return $this->snapshot()["descriptions"][BlocklistNormalizer::strippedLink($strippedLink)] ?? [];
    }

    /**
     * Run $callback with the filter switched off on this instance — for the
     * check endpoint, which has to see what upstream returns. Only this
     * request's instance is affected; every other request filters as usual.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function withoutBlocking(callable $callback): mixed
    {
        $previous = $this->bypassed;
        $this->bypassed = true;

        try {
            return $callback();
        } finally {
            $this->bypassed = $previous;
        }
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
            Log::warning("blocklist: version unreadable and no snapshot stored, reading the table");
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

    private static function compileOrEmpty(): array
    {
        try {
            return self::compile();
        } catch (\Throwable $e) {
            Log::error("blocklist: no snapshot could be loaded, results are unfiltered: " . $e->getMessage());
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
        return storage_path("framework/cache/blocklist");
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

    private static function compile(): array
    {
        $snapshot = self::empty();

        // toBase(): plain rows, no models — this can be thousands of lines, and
        // the soft-delete scope is still applied.
        $rows = BlocklistEntry::query()->toBase()->select(["id", "kind", "value", "terms"])->get();

        foreach ($rows as $row) {
            $id = (int) $row->id;
            switch ($row->kind) {
                case BlocklistEntry::KIND_DOMAIN:
                    $snapshot["domains"][$row->value][] = $id;
                    break;
                case BlocklistEntry::KIND_URL:
                    $snapshot["urls"][$row->value][] = $id;
                    break;
                case BlocklistEntry::KIND_DESCRIPTION:
                    $snapshot["descriptions"][$row->value][] = $id;
                    break;
                case BlocklistEntry::KIND_URL_TERMS:
                    $terms = $row->terms === null || $row->terms === "" ? [] : explode(" ", $row->terms);
                    // An entry without terms would block the page for every
                    // query, which is what `url` is for. The API refuses one;
                    // skipping it here keeps a hand-edited row from widening.
                    if ($terms !== []) {
                        $snapshot["terms"][$row->value][] = [$id, $terms];
                    }
                    break;
            }
        }

        return $snapshot;
    }

    private static function empty(): array
    {
        return ["domains" => [], "urls" => [], "descriptions" => [], "terms" => []];
    }
}
