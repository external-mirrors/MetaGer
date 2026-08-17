<?php

namespace Tests\Support;

use App\MetaGer;
use Illuminate\Support\Facades\Cache;

/**
 * Stands in for the `requests:fetcher` worker.
 *
 * In production, fetching is not part of the request. FPM pushes a mission onto
 * a Redis list, a separate multi-curl worker fetches upstream and pushes the
 * response body back onto a second list, and FPM blocks on `Redis::brpop` until
 * it appears (see App\Console\Commands\RequestFetcher and
 * MetaGer::waitForMainResults). A test making one HTTP request cannot run that
 * second process, so this decorator inlines it: the moment the application
 * queues a mission, the canned response for that engine is placed where the
 * worker would have placed it.
 *
 * Faking *here* rather than at the parser is what makes these tests worth having.
 * Everything downstream of the fetch — the JSON the engine actually returns, the
 * parser, ranking, deduplication, the view — runs exactly as it does in
 * production. Nothing upstream of it does, so no test ever contacts a search
 * engine.
 *
 * The one thing this deliberately does not do is predict the Redis key. Results
 * are keyed by `md5(serialize($configuration))`, computed inside the request from
 * the engine's configuration, the query and the page — a test that recomputed it
 * would be asserting its own arithmetic. The mission carries its own
 * `resulthash`, so the fake reads it back out and stays correct however that
 * configuration changes.
 */
class FakeFetcher
{
    /**
     * Raw upstream response bodies, keyed by engine name (`brave`, `mojeek`, …).
     *
     * @var array<string, string>
     */
    private array $bodies;

    /**
     * Every mission the application queued, in order, decoded.
     *
     * Recorded because "which engines did this search actually ask for, and with
     * what URL" is a behaviour worth pinning in its own right — it is how a
     * search reaches the network, and D2 is about to delete 28 engines.
     *
     * @var list<array<string, mixed>>
     */
    private array $missions = [];

    /**
     * @param object $inner the real redis manager, which everything else passes through to
     * @param array<string, string> $bodies
     */
    public function __construct(private object $inner, array $bodies)
    {
        $this->bodies = $bodies;
    }

    /**
     * Everything that is not a fetch-queue push goes to the real Redis
     * untouched. The search path leans on genuine list semantics — brpop,
     * rpoplpush, expiry — and reimplementing those in a fake would be
     * reimplementing the part most likely to be wrong.
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        if (strtolower($method) === "rpush" && ($arguments[0] ?? null) === MetaGer::FETCHQUEUE_KEY) {
            return $this->serve($arguments);
        }

        return $this->inner->{$method}(...$arguments);
    }

    /**
     * `Redis::connection(...)` is used directly by the authorization code, so it
     * has to keep working. Those connections never carry the fetch queue.
     */
    public function connection(?string $name = null): mixed
    {
        return $this->inner->connection($name);
    }

    /**
     * Do what the worker does, minus the network.
     *
     * @param array<int, mixed> $arguments the original rpush arguments
     */
    private function serve(array $arguments): int
    {
        // predis takes rpush($key, array $values), phpredis takes it variadic.
        // Accept both rather than depend on which client is configured.
        $values = is_array($arguments[1] ?? null)
            ? $arguments[1]
            : array_slice($arguments, 1);

        foreach ($values as $mission) {
            $mission = json_decode($mission, true);
            if (!is_array($mission) || !isset($mission["resulthash"])) {
                continue;
            }

            $this->missions[] = $mission;

            // An engine with no fixture gets "no-result", which is the literal
            // string the worker writes when upstream answers with anything
            // outside 2xx. Silently returning nothing would hide a fixture whose
            // engine name is a typo; this way the engine reports no results,
            // which is what a real failing engine does.
            $body = $this->bodies[$mission["name"]] ?? "no-result";

            // Byte-for-byte the shape RequestFetcher writes: a JSON envelope with
            // the curl info alongside the body, then a 60 second expiry.
            $this->inner->lpush(
                $mission["resulthash"],
                json_encode(["info" => ["http_code" => 200], "body" => $body])
            );
            $this->inner->expire($mission["resulthash"], 60);

            // And the second thing the worker does with an answer, which this
            // used to skip: engines that declare a cacheDuration have their body
            // put in the cache under the same hash, and MetaGer::checkCache
            // looks there before queueing anything at all. Without this a test
            // could never reach the branch that makes a repeated search cheap.
            if (($mission["cacheDuration"] ?? 0) > 0) {
                Cache::put($mission["resulthash"], $body, $mission["cacheDuration"] * 60);
            }
        }

        return count($values);
    }

    /**
     * Delete every result list this fake wrote.
     *
     * Not optional housekeeping. Results are keyed by a hash of the engine
     * configuration and the query, so two tests searching the same term share a
     * key — and MetaGer reads results with `rpoplpush`, which rotates the list
     * rather than consuming it, then sets a 60 second expiry. A body written by
     * one test is therefore still sitting there for the next one, which then
     * passes on data it never provided. Left alone, a test asserting that an
     * engine returned *nothing* reads the previous test's fixture instead.
     */
    public function forgetServedResults(): void
    {
        foreach ($this->missions as $mission) {
            $this->inner->del($mission["resulthash"]);
            // The cache copy has to go with it, and for a sharper reason than
            // the list: a cached body makes checkCache skip the engine
            // altogether, so leaving one behind does not feed a later test
            // stale results — it stops that test's search from asking for any.
            Cache::forget($mission["resulthash"]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function missions(): array
    {
        return $this->missions;
    }

    /**
     * Names of the engines this search actually queued, in order.
     *
     * @return list<string>
     */
    public function queuedEngines(): array
    {
        return array_values(array_map(fn(array $m) => $m["name"], $this->missions));
    }
}
