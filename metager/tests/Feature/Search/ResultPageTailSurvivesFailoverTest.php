<?php

namespace Tests\Feature\Search;

use App\MetaGer;
use App\QueryLogger;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\Concerns\FakesSearchEngines;
use Tests\Support\FailingOverRedis;
use Tests\TestCase;

/**
 * The Redis calls a search makes after it has already succeeded.
 *
 * Between {@see SearchSurvivesFailoverTest} (the engine orchestration) and
 * {@see AuthorizationSurvivesFailoverTest} (the middleware that runs first),
 * three unguarded calls were left, all of them at the end of the request:
 *
 *   - `Quicktips::retrieveResults` — `rpoplpush` and `expire`, reading back the
 *     info panel above the results. `startSearch` at the other end of the same
 *     box was already guarded; this half was not.
 *   - `QueryLogger::createLog` — an `rpush` from `MetaGer::createView()`, after
 *     the page has been built.
 *   - `Searchengine::createMission`'s quota counter, and the two counters in
 *     MetaGerSearch that went to `CollectorRegistry` directly instead of
 *     through App\PrometheusExporter.
 *
 * These are the worst ones to lose a request to, because by the time they run
 * the work is done: the engines have been queried, paid for and parsed, and the
 * page is assembled. A -READONLY reply threw all of it away to record a
 * statistic.
 *
 * @see \App\Support\RedisFailover
 */
class ResultPageTailSurvivesFailoverTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @param array<string, int> $failures
     */
    private function failOverDuring(array $failures): FailingOverRedis
    {
        $failer = new FailingOverRedis($this->app->make("redis"), $failures);

        $this->app->instance("redis", $failer);
        Redis::clearResolvedInstances();

        return $failer;
    }

    private function search(): \Illuminate\Testing\TestResponse
    {
        return $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");
    }

    private function fakeEngines(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);
    }

    /**
     * A quicktip waiting on its list, with nothing in the cache — which is the
     * normal state of a cold quicktip in production, and the only one that
     * reaches the `rpoplpush` polling loop.
     *
     * Not driven through a page, because through a page it cannot happen:
     * Tests\Support\FakeFetcher inlines the worker synchronously, and quicktips
     * declare a cacheDuration, so by the time the request reads the quicktip
     * back the cache is already warm and `retrieveResults` returns on its first
     * line. A search-level test here passes without executing a single line of
     * what it claims to cover.
     *
     * The hash is read off the object rather than recomputed. It is
     * `md5($url)` over a URL built inside the constructor from the query, the
     * locale and two settings; a test that rebuilt it would be asserting its
     * own arithmetic and would drift the moment the URL gains a parameter —
     * the same reasoning FakeFetcher gives for reading `resulthash` out of the
     * mission instead of predicting it.
     */
    private function coldQuicktip(): array
    {
        $quicktips = new \App\Models\Quicktips\Quicktips("kaffee", "de-DE", 2000);

        $hash = (new \ReflectionProperty(\App\Models\Quicktips\Quicktips::class, "hash"))
            ->getValue($quicktips);

        \Illuminate\Support\Facades\Cache::forget($hash);
        Redis::del($hash);
        Redis::del(MetaGer::FETCHQUEUE_KEY);

        return [$quicktips, $hash];
    }

    /**
     * Reading the quicktip back.
     *
     * `rpoplpush` is a write and so is the `expire` beside it, and this was the
     * one bare pair left on the result path — Quicktips::startSearch, at the
     * other end of the same box, has been guarded since the failover work
     * began.
     */
    public function testAQuicktipSurvivesAReadonlyReplyWhileBeingReadBack(): void
    {
        [$quicktips, $hash] = $this->coldQuicktip();
        Redis::lpush($hash, json_encode(["info" => [], "body" => "<feed/>"]));

        $failer = $this->failOverDuring(["rpoplpush" => 1]);

        $result = $quicktips->retrieveResults($hash, false);

        Redis::del($hash);

        $this->assertTrue(
            $failer->allFailuresDelivered(),
            "the test did not actually simulate a failover: no rpoplpush was issued"
        );
        $this->assertSame("<feed/>", $result);
    }

    /**
     * And when it cannot be read at all, the caller gets `false` and renders
     * without the box — rather than an exception, which from
     * MetaGerSearch::search() is a 503 for a search that has already run.
     */
    public function testAQuicktipThatCannotBeReadAnswersFalseRatherThanThrowing(): void
    {
        [$quicktips, $hash] = $this->coldQuicktip();
        Redis::lpush($hash, json_encode(["info" => [], "body" => "<feed/>"]));

        $this->failOverDuring(["rpoplpush" => 99]);

        $result = $quicktips->retrieveResults($hash, false);

        Redis::del($hash);

        $this->assertFalse($result);
    }

    /**
     * The query log, which is the very last thing a search does.
     *
     * Driven directly rather than through a page: `rpush` is also how fetch
     * missions are queued, and FailingOverRedis fails the *first* n calls of a
     * command — so a search-level test would spend the failures on
     * EngineOrchestrator::queueMissions (already guarded) and never reach this
     * one. Failing enough rpushes to get here would instead fail the mission
     * queue permanently, which is a different test with a different answer.
     */
    public function testTheQueryLogSurvivesAReadonlyReply(): void
    {
        $metager = Mockery::mock(MetaGer::class);
        $metager->shouldReceive("getFokus")->andReturn("web");
        $this->app->instance(MetaGer::class, $metager);

        $logger = new QueryLogger();
        $this->failOverDuring(["rpush" => 1]);

        $before = Redis::llen(QueryLogger::REDIS_KEY);
        $logger->createLog();

        $this->assertSame(
            $before + 1,
            Redis::llen(QueryLogger::REDIS_KEY),
            "the log line was dropped on a failover that a retry covers"
        );
    }

    /**
     * A query log that genuinely cannot be written loses the line, not the
     * search. It runs from MetaGer::createView(), so propagating would discard
     * a page that is already rendered.
     */
    public function testAQueryLogThatCannotBeWrittenIsDropped(): void
    {
        $metager = Mockery::mock(MetaGer::class);
        $metager->shouldReceive("getFokus")->andReturn("web");
        $this->app->instance(MetaGer::class, $metager);

        $logger = new QueryLogger();
        $this->failOverDuring(["rpush" => 99]);

        $logger->createLog();

        $this->assertTrue(true, "createLog propagated instead of dropping the line");
    }
}
