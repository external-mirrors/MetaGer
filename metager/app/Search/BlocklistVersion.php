<?php

namespace App\Search;

use Illuminate\Support\Facades\Redis;

/**
 * The Redis counter `blocklist:version`: incremented after every write to
 * `blocklist_entries`, read once per request by Blacklists to decide whether
 * its snapshot is still current.
 *
 * It does not start at 0. A snapshot is stored on disk under its version
 * (Blacklists::store), so a counter that restarted from 0 after Valkey lost
 * its data would name an old snapshot as current — the list from before the
 * last N edits. A missing key is therefore seeded with the current time in
 * milliseconds, which is larger than any version an earlier incarnation of the
 * counter can have reached, and incremented from there.
 */
class BlocklistVersion
{
    public const KEY = "blocklist:version";

    /**
     * The current version, or null when Redis cannot be asked.
     */
    public static function current(): ?string
    {
        try {
            $redis = Redis::connection();
            $version = $redis->get(self::KEY);
            if ($version === null || $version === false) {
                $redis->setnx(self::KEY, self::seed());
                $version = $redis->get(self::KEY);
            }

            return $version === null || $version === false ? null : (string) $version;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Tell every worker its snapshot is stale.
     *
     * @throws \Throwable when Redis cannot be reached
     */
    public static function bump(): void
    {
        $redis = Redis::connection();
        $redis->setnx(self::KEY, self::seed());
        $redis->incr(self::KEY);
    }

    private static function seed(): string
    {
        return (string) (int) (microtime(true) * 1000);
    }
}
