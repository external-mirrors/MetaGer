<?php

namespace App\Search;

use App\MetaGer;
use App\Models\Configuration\SearchEngineRegistry;
use App\Models\Configuration\Searchengines;
use App\Models\Searchengine;
use App\SearchSettings;
use App\Support\RedisFailover;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Predis\PredisException;

/**
 * Asks the search engines, waits for them, and reads what came back.
 *
 * The three phases of a search that talk to Redis, in the order a request runs
 * them:
 *
 *   1. {@see start}                — what is cached already, and a fetch mission
 *                                    for everything else
 *   2. {@see waitForMainResults}   — block until the engines that define the page
 *                                    have answered, or the budget runs out
 *   3. {@see collectResults}       — read whatever else arrived meanwhile
 *
 * This used to be four methods on MetaGer, each looping over the engines and
 * talking to Redis one engine at a time. Doing it a phase at a time instead is
 * the whole reason for the class: every phase now knows the full set of engines
 * it is acting for, so it can ask for all of them in a single round trip.
 *
 * That matters more than the numbers suggest. The round trips are serialized —
 * each one waits for the previous answer — and in production Redis is a network
 * hop to a Valkey pod, not a socket on the same host. EngineOrchestrationTest
 * counts them.
 *
 * Fetching itself is not here and not in the request at all: a mission goes onto
 * a Redis list and the `requests:fetcher` worker does the curl. See
 * App\Console\Commands\RequestFetcher.
 */
class EngineOrchestrator
{
    /**
     * How long the page is willing to wait for the engines that define it.
     *
     * A product decision rather than a technical one — it trades completeness
     * against time-to-first-byte — so it is named here rather than left as a 6
     * in the middle of a loop.
     */
    public const WAIT_SECONDS = 6;

    /**
     * How long an answer stays in Redis after being read. Results are rotated
     * rather than consumed because load-more comes back for the same list.
     */
    private const ANSWER_TTL = 60;

    /**
     * Load what is cached, then queue a fetch for everything that is not.
     */
    public function start(MetaGer $metager): void
    {
        $this->loadFromCache($metager);
        $this->queueMissions();
    }

    /**
     * Answers still in the cache from an earlier search for the same thing.
     *
     * One `mget` for every engine, where this used to be a `Cache::has` followed
     * by a `Cache::get` per engine — two round trips each, the first of which
     * only asked whether the second would find anything.
     *
     * An engine loaded here is marked `cached`, which is what keeps
     * {@see queueMissions} from asking for it and {@see waitForMainResults} from
     * waiting on it. A fully cached search therefore touches the fetch queue not
     * at all.
     */
    public function loadFromCache(MetaGer $metager): void
    {
        if (!$metager->canCache()) {
            return;
        }

        $engines = $this->engines();
        if (empty($engines)) {
            return;
        }

        // A cache that cannot be read is a cache miss with extra steps: every
        // engine that does not load here is queued for a fetch instead, which
        // is exactly what would have happened on a cold cache.
        try {
            $bodies = RedisFailover::retry(fn() => Cache::many(array_values(array_unique(
                array_map(fn(Searchengine $engine) => $engine->getHash(), $engines)
            ))));
        } catch (PredisException $e) {
            Log::warning('Could not read the engine cache: ' . $e->getMessage());

            return;
        }

        foreach ($engines as $engine) {
            $body = $bodies[$engine->getHash()] ?? null;
            if ($body === null) {
                continue;
            }

            $engine->cached = true;
            $engine->loadResponse($metager, $body);
        }
    }

    /**
     * One push for every mission, rather than one push per engine.
     *
     * The missions are independent and the worker pops them in batches anyway,
     * so there was never a reason to wait for Redis between them.
     */
    public function queueMissions(): void
    {
        $missions = [];
        foreach ($this->engines() as $engine) {
            $mission = $engine->createMission();
            if ($mission !== null) {
                $missions[] = json_encode($mission);
            }
        }

        if (empty($missions)) {
            return;
        }

        // The one Redis call in a search that has no degraded form: an
        // unqueued mission is an engine that will never answer, so there is
        // nothing to render with. Retried across a failover and then allowed
        // to propagate — bootstrap/app.php turns it into a 503 with a
        // meta-refresh, which is the honest answer if Redis is genuinely gone.
        //
        // Retrying a write means a mission can be queued twice if the reply
        // was lost rather than the write: the worker fetches it twice and the
        // second answer sits on the same list until ANSWER_TTL drops it.
        // Cheap, bounded, and much better than the alternative of answering a
        // user with an error page during a planned drain.
        RedisFailover::retry(fn() => Redis::rpush(MetaGer::FETCHQUEUE_KEY, ...$missions));
    }

    /**
     * Block until the engines that define this fokus have answered.
     *
     * `main` in config/foki.json names them: the page is built around those, so
     * rendering before they arrive would show a result list that visibly fills
     * in afterwards. Everything else is read in {@see collectResults} with
     * whatever it has by then.
     *
     * The wait is a multi-key `brpop`, which is also why Redis cannot run in
     * cluster mode here — keys for different engines hash to different slots and
     * a multi-key command across slots is a CROSSSLOT error. HA has to be
     * sentinel-based.
     *
     * `brpop` consumes what it returns, so the answer goes straight back on the
     * list for load-more to find. Those two commands are pipelined; nothing
     * between them depends on the other.
     */
    public function waitForMainResults(MetaGer $metager): void
    {
        $engines = $this->engines();
        $waitingFor = $this->hashesToWaitFor($engines);

        $timeStart = microtime(true);
        while (sizeof($waitingFor) > 0) {
            if ((microtime(true) - $timeStart) >= self::WAIT_SECONDS) {
                break;
            }

            // A failover mid-wait must not cost the page. Retrying reconnects
            // through HAProxy, which by then has seen the promotion; giving up
            // stops the waiting rather than failing the request, because
            // whatever loaded from the cache is still worth rendering and
            // collectResults gets one more chance at the rest.
            try {
                $answer = RedisFailover::retry(fn() => Redis::brpop($waitingFor, self::WAIT_SECONDS));
            } catch (PredisException $e) {
                Log::warning('Stopped waiting for engine results: ' . $e->getMessage());
                return;
            }

            if ($answer === null) {
                continue;
            }

            [$hash, $payload] = $answer;

            // Putting the answer back is for load-more, which comes back for
            // the same list later. The payload is already in hand, so failing
            // to rotate it costs a later load-more, not this page — losing the
            // result we are holding because we could not write it back would
            // be strictly worse than rendering it and moving on.
            try {
                RedisFailover::retry(fn() => Redis::pipeline(function ($pipe) use ($hash, $payload) {
                    $pipe->lpush($hash, $payload);
                    $pipe->expire($hash, self::ANSWER_TTL);
                }));
            } catch (PredisException $e) {
                Log::warning('Could not return an engine answer for load-more: ' . $e->getMessage());
            }

            foreach ($engines as $engine) {
                if ($engine->getHash() === $hash) {
                    $engine->loadResponse($metager, $this->unwrap($payload));
                }
            }

            $waitingFor = array_values(array_filter($waitingFor, fn(string $h) => $h !== $hash));
        }
    }

    /**
     * Read every engine that has not been loaded yet.
     *
     * All of them in one pipeline, where this used to be a rotate and a fresh
     * expiry per engine, one engine at a time. An engine with nothing on its
     * list simply has not answered; the page renders without it.
     */
    public function collectResults(MetaGer $metager): void
    {
        $engines = $this->engines();

        $pending = array_filter(
            $engines,
            // A cached engine that did not load has nothing waiting in Redis —
            // it was never fetched for this request.
            fn(Searchengine $engine) => !$engine->loaded && !$engine->cached
        );

        $answers = $this->readAnswers($pending);

        $totalResults = 0;
        foreach ($engines as $engine) {
            if (!$engine->loaded) {
                try {
                    $engine->loadResponse($metager, $this->unwrap($answers[$engine->getHash()] ?? null));
                } catch (\ErrorException $e) {
                    Log::error($e);
                }
            }

            if (!empty($engine->totalResults) && $engine->totalResults > $totalResults) {
                $totalResults = $engine->totalResults;
            }
            if (!empty($engine->alteredQuery) && !empty($engine->alterationOverrideQuery)) {
                $metager->alteredQuery = $engine->alteredQuery;
                $metager->alterationOverrideQuery = $engine->alterationOverrideQuery;
            }
        }

        $metager->reportTotalResults($totalResults);
    }

    /**
     * Rotate every pending engine's list and refresh its expiry, in one round
     * trip, and hand back the raw payloads keyed by hash.
     *
     * `rpoplpush` onto the same key rather than a plain pop: load-more asks for
     * the same list again later, so consuming it here would empty it.
     *
     * @param iterable<Searchengine> $engines
     * @return array<string, string|null>
     */
    private function readAnswers(iterable $engines): array
    {
        $hashes = [];
        foreach ($engines as $engine) {
            $hashes[$engine->getHash()] = true;
        }
        $hashes = array_keys($hashes);

        if (empty($hashes)) {
            return [];
        }

        // The last Redis call of the request, and the most degradable one: an
        // engine with nothing readable here is indistinguishable from an
        // engine that simply has not answered, which the page already renders
        // without. Failing the whole search at this point — after the results
        // have been waited for — would throw away work that is already done.
        try {
            $replies = RedisFailover::retry(fn() => Redis::pipeline(function ($pipe) use ($hashes) {
                foreach ($hashes as $hash) {
                    $pipe->rpoplpush($hash, $hash);
                    $pipe->expire($hash, self::ANSWER_TTL);
                }
            }));
        } catch (PredisException $e) {
            Log::warning('Could not read engine answers: ' . $e->getMessage());

            return [];
        }

        $answers = [];
        foreach ($hashes as $index => $hash) {
            // Two commands per hash, so the rotate's reply is at every other
            // position. A list with nothing on it answers false or null.
            $reply = $replies[$index * 2] ?? null;
            $answers[$hash] = is_string($reply) ? $reply : null;
        }

        return $answers;
    }

    /**
     * The hashes to block on: the fokus' main engines, minus any already loaded
     * from the cache.
     *
     * If the user has switched all of the main engines off, every enabled engine
     * counts as one — otherwise the page would render before anything had
     * answered.
     *
     * @param iterable<Searchengine> $engines
     * @return list<string>
     */
    private function hashesToWaitFor(iterable $engines): array
    {
        $fokus = app(SearchSettings::class)->fokus;
        $mainEngines = app(SearchEngineRegistry::class)->foki->{$fokus}->main;

        $main = [];
        foreach ($mainEngines as $mainEngine) {
            foreach ($engines as $engine) {
                if ($engine->name === $mainEngine) {
                    $main[] = $engine;
                }
            }
        }

        if (empty($main)) {
            $main = $engines;
        } else {
            // An engine already loaded from the cache will never appear on a
            // list, so waiting for it would mean waiting out the whole budget.
            // Only applied when a main engine matched: with none enabled the
            // fallback above is already "wait for whatever there is".
            $main = array_filter($main, fn(Searchengine $engine) => !$engine->loaded);
        }

        $hashes = [];
        foreach ($main as $engine) {
            $hashes[$engine->getHash()] = true;
        }

        return array_keys($hashes);
    }

    /**
     * Take the response body out of the envelope the fetcher wrote.
     *
     * The worker stores `{"info": …, "body": …}` — the curl info alongside the
     * response — so what a list holds is not what a parser wants.
     */
    private function unwrap(?string $payload): ?string
    {
        if ($payload === null) {
            return null;
        }

        $decoded = json_decode($payload);

        return $decoded->body ?? $payload;
    }

    /**
     * @return array<Searchengine>
     */
    private function engines(): array
    {
        return app(Searchengines::class)->getEnabledSearchengines();
    }
}
