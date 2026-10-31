<?php

namespace App\Search;

use App\MetaGer;
use App\Models\Configuration\Searchengines;
use App\Models\parserSkripte\Base\SerperBase;
use App\Support\RedisFailover;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Predis\PredisException;

/**
 * Brings back the results the first page had to drop because Serper returned
 * them as Google redirects.
 *
 * Since 30 October 2026 Serper resolves only the top three organic results
 * before answering; the rest arrive as `google.com/goto?url=…` and are dropped
 * (see GoogleRedirectResolver). Serper's `/resolve` endpoint turns them into
 * real addresses, but on its own schedule: a few links within seconds, the rest
 * after about half a minute. No result page can wait for that, so it happens on
 * load-more's schedule instead — the client is polling anyway, and every poll
 * rebuilds the search and re-ranks it, so a result that comes back lands at its
 * rank like any engine that answered late.
 *
 * Started from the first load-more rather than from the search itself, so only
 * a client that will come back for the results costs Serper a request: a page
 * without JavaScript, or an API call that never polls, asks for nothing.
 *
 * Two Redis round trips per poll at most: one push for every engine's missions,
 * the first time, and one pipeline reading every pending answer. The fetcher
 * carries out the missions like any other — see SerperBase::resolveMissions.
 */
class LateRedirectResolution
{
    /**
     * Queue what has not been queued, read what has arrived, and re-parse the
     * engines it changes.
     *
     * @return bool whether any engine's results changed
     */
    public function advance(): bool
    {
        $engines = $this->engines();
        if (empty($engines)) {
            return false;
        }

        $this->queueMissions($engines);

        $pending = [];
        foreach ($engines as $index => $engine) {
            foreach ($engine->lateLinks() as $link) {
                $pending[] = [$index, $link, $engine->resolveHash($link)];
            }
        }
        if (empty($pending)) {
            return false;
        }

        // Like EngineOrchestrator::readAnswers: an answer that cannot be read
        // is one that has not arrived, and the next poll asks again.
        try {
            $replies = RedisFailover::retry(fn() => Redis::pipeline(function ($pipe) use ($pending) {
                foreach ($pending as [, , $hash]) {
                    $pipe->lindex($hash, 0);
                }
            }));
        } catch (PredisException $e) {
            Log::warning("Could not read resolved Google redirects: " . $e->getMessage());

            return false;
        }

        $answers = [];
        foreach ($pending as $position => [$index, $link]) {
            $reply = $replies[$position] ?? null;
            $answers[$index][$link] = is_string($reply) ? (json_decode($reply)->body ?? null) : null;
        }

        $changed = false;
        foreach ($answers as $index => $engineAnswers) {
            $changed = $engines[$index]->applyResolvedAnswers($engineAnswers) || $changed;
        }

        return $changed;
    }

    /**
     * Whether any engine may still bring results back, which keeps load-more
     * from reporting the search as finished.
     */
    public function pending(): bool
    {
        foreach ($this->engines() as $engine) {
            if ($engine->isResolving()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<SerperBase> $engines
     */
    private function queueMissions(array $engines): void
    {
        $missions = [];
        foreach ($engines as $engine) {
            foreach ($engine->resolveMissions() as $mission) {
                $missions[] = json_encode($mission);
            }
        }

        if (empty($missions)) {
            return;
        }

        // Not the one call with no degraded form, as it is for the search
        // itself: missions that are lost here only mean the dropped results
        // stay dropped, and the window closes on them.
        try {
            RedisFailover::retry(fn() => Redis::rpush(MetaGer::FETCHQUEUE_KEY, ...$missions));
        } catch (PredisException $e) {
            Log::warning("Could not queue Google redirects for resolution: " . $e->getMessage());
        }
    }

    /**
     * @return array<SerperBase>
     */
    private function engines(): array
    {
        return array_values(array_filter(
            app(Searchengines::class)->getEnabledSearchengines(),
            fn($engine) => $engine instanceof SerperBase && $engine->loaded
        ));
    }
}
