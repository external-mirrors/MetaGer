<?php

namespace App\Search;

use Illuminate\Support\Facades\Redis;

/**
 * MetaGer's copy of its list from the blocklist service, in Valkey.
 *
 * `blocklist:snapshot` holds the snapshot exactly as the service sent it
 * (open-source/blocklist, docs/snapshot.md), `blocklist:etag` the ETag to send
 * back on the next fetch. Written by `blocklist:sync` (BlocklistSync), read by
 * Blacklists when `blocklist:version` says its compiled copy is stale.
 *
 * Valkey rather than the database: every pod reads it, it is small, and
 * losing it costs one full fetch a minute later. Until then Blacklists keeps
 * the snapshot it compiled last.
 */
class BlocklistStore
{
    public const SNAPSHOT = "blocklist:snapshot";
    public const ETAG = "blocklist:etag";

    /**
     * @return ?array{format: int, service: string, version: int, changed_at: ?string, entries: list<array{id: int, kind: string, value: string, terms: ?list<string>}>}
     */
    public static function snapshot(): ?array
    {
        $json = Redis::connection()->get(self::SNAPSHOT);
        if (!is_string($json) || $json === "") {
            return null;
        }

        $snapshot = json_decode($json, true);

        return is_array($snapshot) && isset($snapshot["entries"]) && is_array($snapshot["entries"]) ? $snapshot : null;
    }

    public static function etag(): ?string
    {
        $etag = Redis::connection()->get(self::ETAG);

        return is_string($etag) && $etag !== "" ? $etag : null;
    }

    /**
     * Store a new snapshot and tell every worker. Snapshot and ETag go in one
     * MULTI, so a reader never pairs the new list with the old ETag; the
     * version is bumped after, so a worker that sees it finds the new list.
     */
    public static function put(array $snapshot, ?string $etag): void
    {
        Redis::connection()->transaction(function ($tx) use ($snapshot, $etag) {
            $tx->set(self::SNAPSHOT, json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            if ($etag === null) {
                $tx->del(self::ETAG);
            } else {
                $tx->set(self::ETAG, $etag);
            }
        });

        BlocklistVersion::bump();
    }

    /** For tests: the suite shares one Valkey. */
    public static function forget(): void
    {
        Redis::connection()->del(self::SNAPSHOT, self::ETAG);
    }
}
