<?php

namespace App\Search;

use Illuminate\Support\Facades\Http;

/**
 * Fetch MetaGer's list from the blocklist service when it has changed.
 *
 * GET /api/lists/metager with the last ETag as If-None-Match; a 304 costs
 * nothing, a 200 is checked and stored (BlocklistStore). The contract is
 * docs/consumers.md and docs/snapshot.md in open-source/blocklist.
 *
 * Anything but a well-formed metager snapshot keeps what is stored: a list
 * that empties because a fetch failed is every removal silently undone. An
 * empty list the service actually sends is stored like any other.
 */
class BlocklistSync
{
    public const UPDATED = "updated";
    public const UNCHANGED = "unchanged";
    public const FAILED = "failed";
    public const UNCONFIGURED = "unconfigured";

    /** The snapshot format this code understands. */
    private const FORMAT = 1;

    /**
     * @return array{0: string, 1: string} status, a line for the log
     */
    public function sync(): array
    {
        $url = config("metager.metager.blocklist.url");
        if (!is_string($url) || $url === "") {
            return [self::UNCONFIGURED, "blocklist service not configured (BLOCKLIST_URL), nothing to sync"];
        }

        $etag = BlocklistStore::etag();
        $request = Http::timeout((int) config("metager.metager.blocklist.timeout", 5))
            ->withToken((string) config("metager.metager.blocklist.token"))
            ->acceptJson();
        if ($etag !== null) {
            $request = $request->withHeaders(["If-None-Match" => $etag]);
        }

        try {
            $response = $request->get(rtrim($url, "/") . "/api/lists/metager");
        } catch (\Throwable $e) {
            return [self::FAILED, "blocklist service unreachable, keeping the stored list: " . $e->getMessage()];
        }

        if ($response->status() === 304) {
            return [self::UNCHANGED, "blocklist unchanged"];
        }
        if ($response->status() !== 200) {
            return [self::FAILED, "blocklist service answered {$response->status()}, keeping the stored list"];
        }

        $snapshot = $response->json();
        $problem = $this->problem($snapshot);
        if ($problem !== null) {
            return [self::FAILED, "blocklist service sent $problem, keeping the stored list"];
        }

        BlocklistStore::put($snapshot, $response->header("ETag") ?: null);

        return [self::UPDATED, sprintf("blocklist updated to version %d, %d entries", $snapshot["version"], count($snapshot["entries"]))];
    }

    private function problem(mixed $snapshot): ?string
    {
        if (!is_array($snapshot)) {
            return "something that is not a JSON object";
        }
        if (($snapshot["format"] ?? null) !== self::FORMAT) {
            return "format " . json_encode($snapshot["format"] ?? null) . ", which this code does not know";
        }
        if (($snapshot["service"] ?? null) !== "metager") {
            return "the list of " . json_encode($snapshot["service"] ?? null);
        }
        if (!is_int($snapshot["version"] ?? null) || !isset($snapshot["entries"]) || !is_array($snapshot["entries"])) {
            return "a snapshot without version or entries";
        }

        return null;
    }
}
