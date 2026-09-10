<?php

namespace Tests\Feature\Search;

use App\Console\Commands\RequestFetcher;
use App\MetaGer;
use Illuminate\Support\Facades\Redis;
use Tests\Support\FailingOverRedis;
use Tests\TestCase;

/**
 * A Sentinel failover must not kill the fetcher.
 *
 * The client-side retry that keeps a *search* alive across a promotion
 * (App\Support\RedisFailover, pinned by SearchSurvivesFailoverTest) covered fpm
 * and stopped there. The fetch worker talks to the same Valkey, through the
 * same master proxy, and had no guard on any of it — while being the process
 * that a search is actually waiting on.
 *
 * Three writes, all of which a demoted node answers `-READONLY`:
 *
 *   - the healthcheck stamp, at the top of every loop iteration
 *   - the queue pop, `blpop`/`lpop` — pops are writes
 *   - the answer write-back, `lpush` + `expire`
 *
 * None was caught, and `handle()`'s loop sits in a `try`/`finally` with no
 * `catch`, so any of them ended the process. That is worse than it sounds: the
 * multicurl handle dies with it, so every engine response in flight is
 * discarded — requests already made, already paid for, already answered. Every
 * search waiting on one of those hashes then waits out
 * EngineOrchestrator::WAIT_SECONDS and renders without those engines. A drain
 * that was supposed to cost nobody a page costs everyone mid-search six seconds
 * and a thinner result list.
 *
 * The failure is injected as the exact exception Predis raises for `-READONLY`,
 * for the same reason SearchSurvivesFailoverTest does: Predis' own retry layer
 * catches `CommunicationException` and `-LOADING` and this is neither, which is
 * why it reached production in the first place.
 *
 * @see \App\Support\RedisFailover
 * @see \Tests\Feature\Search\SearchSurvivesFailoverTest
 */
class RequestFetcherFailoverTest extends TestCase
{
    /**
     * The command with its Redis-touching seams reachable.
     *
     * They are `protected` on the command precisely so this can drive them:
     * none is reachable from `handle()` without a live curl transfer and an
     * endless loop, and the failover behaviour is the whole point of them.
     */
    private function fetcher(): object
    {
        return new class extends RequestFetcher {
            public function stamp(): void
            {
                $this->stampHealthcheck();
            }

            public function deliver(string $hash, string $payload): void
            {
                $this->deliverAnswer($hash, $payload);
            }

            public function poll(int $operationsRunning, int $messagesLeft): int
            {
                return $this->checkNewJobs($operationsRunning, $messagesLeft);
            }
        };
    }

    /**
     * @param array<string, int> $failures command => how many times it fails first
     */
    private function failOverDuring(array $failures): FailingOverRedis
    {
        $failer = new FailingOverRedis($this->app->make("redis"), $failures);

        $this->app->instance("redis", $failer);
        Redis::clearResolvedInstances();

        return $failer;
    }

    /**
     * The one a search is blocked on. An engine answered; this is what hands
     * that answer to the fpm process sitting in brpop for it.
     */
    public function testAFetchedAnswerSurvivesAReadonlyReply(): void
    {
        $hash = "failover-test-" . uniqid();
        $this->failOverDuring(["pipeline" => 1]);

        $this->fetcher()->deliver($hash, json_encode(["info" => [], "body" => "<results/>"]));

        $stored = Redis::lrange($hash, 0, -1);
        Redis::del($hash);

        $this->assertCount(
            1,
            $stored,
            "the answer was dropped — the search waiting on it renders without this engine"
        );
        // Decoded rather than matched as a substring: json_encode escapes the
        // slash, so the raw payload reads `<results\/>` and a naive
        // assertStringContainsString fails on a write that in fact succeeded.
        // EngineOrchestrator::unwrap() reads this field, so this is also the
        // shape a search actually consumes.
        $this->assertSame("<results/>", json_decode($stored[0])->body);
    }

    /**
     * The most likely place for a failover to land, simply because it runs more
     * often than anything else here.
     */
    public function testTheHealthcheckStampSurvivesAReadonlyReply(): void
    {
        $this->failOverDuring(["set" => 1]);

        // Before the retry this threw, out of handle()'s uncaught loop, and the
        // process ended. PHPUnit reports that without any assertion of ours.
        $this->fetcher()->stamp();

        $this->assertNotNull(
            Redis::get(RequestFetcher::HEALTHCHECK_KEY),
            "the stamp never landed, so the liveness probe would fail the pod next"
        );
    }

    /**
     * A pop is a write. Both branches of checkNewJobs use one — `blpop` when
     * the worker is idle, `lpop` when it is not.
     */
    public function testPollingTheQueueSurvivesAReadonlyReply(): void
    {
        Redis::del(MetaGer::FETCHQUEUE_KEY);
        $this->failOverDuring(["lpop" => 1]);

        // messagesLeft 0 takes the non-blocking `lpop` branch; an empty queue
        // means no job is added, which is fine — what is under test is that the
        // pop itself does not end the process.
        $added = $this->fetcher()->poll(operationsRunning: 2, messagesLeft: 0);

        $this->assertSame(0, $added);
    }

    /**
     * Giving up is allowed; taking the process with it is not.
     *
     * A failover that outlasts RedisFailover's budget still has to leave the
     * worker running — the transfers already in flight in its multicurl handle
     * are worth more than the one answer that could not be written back.
     */
    public function testAnAnswerThatCannotBeWrittenBackDoesNotEndTheWorker(): void
    {
        $hash = "failover-test-" . uniqid();
        // More failures than the retry budget can absorb: 50ms, 100ms, 200ms,
        // 400ms, 800ms, 1.6s already exceeds RedisFailover::BUDGET_SECONDS.
        $this->failOverDuring(["pipeline" => 99]);

        $this->fetcher()->deliver($hash, json_encode(["info" => [], "body" => "lost"]));

        $this->assertTrue(true, "deliverAnswer swallowed the failure instead of propagating it");
    }
}
