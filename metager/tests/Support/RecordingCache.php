<?php

namespace Tests\Support;

use Illuminate\Cache\Repository;

/**
 * A cache repository that remembers which keys were asked for, and how.
 *
 * RecordingRedis cannot see this: the test suite runs with `CACHE_STORE=array`
 * (phpunit.xml), so cache reads never reach Redis here even though they are
 * Redis commands in production. Counting them at the repository is the level
 * where the distinction that matters is still visible — one `many` for every
 * engine is one `mget` against Redis, where a `has` and a `get` per engine are
 * two round trips each.
 *
 * Install it over the real repository with the store it already has, so
 * everything written before the swap is still readable after it:
 *
 *     Cache::swap($recorder = new RecordingCache(Cache::getStore()));
 */
class RecordingCache extends Repository
{
    /** @var list<array{method: string, keys: list<string>}> */
    private array $calls = [];

    public function has($key): bool
    {
        $this->calls[] = ["method" => "has", "keys" => [(string) $key]];

        return parent::has($key);
    }

    public function get($key, $default = null): mixed
    {
        $this->calls[] = ["method" => "get", "keys" => [(string) $key]];

        return parent::get($key, $default);
    }

    public function many(array $keys): array
    {
        $this->calls[] = ["method" => "many", "keys" => array_map(strval(...), $keys)];

        return parent::many($keys);
    }

    /**
     * How often a method was called at all.
     */
    public function countOf(string $method): int
    {
        return count(array_filter($this->calls, fn(array $call) => $call["method"] === $method));
    }

    /**
     * How often a method was called for one of the given keys.
     *
     * @param list<string> $keys
     */
    public function countFor(string $method, array $keys): int
    {
        return count(array_filter(
            $this->calls,
            fn(array $call) => $call["method"] === $method && array_intersect($call["keys"], $keys) !== []
        ));
    }

    /**
     * The keys of the first call to a method, for asserting that one batched
     * read really covered everything.
     *
     * @return list<string>
     */
    public function keysOfFirst(string $method): array
    {
        foreach ($this->calls as $call) {
            if ($call["method"] === $method) {
                return $call["keys"];
            }
        }

        return [];
    }

    /**
     * Every distinct key touched that starts with the given prefix.
     *
     * For asserting how a namespace of keys is used — that two different
     * clients got two entries, say — without a test having to know how the key
     * is built.
     *
     * @return list<string>
     */
    public function keysMatching(string $prefix): array
    {
        $matching = [];
        foreach ($this->calls as $call) {
            foreach ($call["keys"] as $key) {
                if (str_starts_with($key, $prefix)) {
                    $matching[$key] = true;
                }
            }
        }

        return array_keys($matching);
    }

    /**
     * Every call in order, as "method key,key" strings, for a failure message.
     *
     * @return list<string>
     */
    public function trace(): array
    {
        return array_map(fn(array $call) => $call["method"] . " " . implode(",", $call["keys"]), $this->calls);
    }
}
