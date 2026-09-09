<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Facades\Redis;
use Tests\Concerns\FakesSearchEngines;
use Tests\Support\FailingOverRedis;
use Tests\TestCase;

/**
 * A planned node drain must not cost a user their search.
 *
 * That is the whole requirement, and on 2026-09-08 it was not met: four drains
 * produced four windows in which writes reached a Valkey node that Sentinel had
 * demoted underneath them and came back `-READONLY`. The infrastructure half of
 * the answer is to fail the master away from a node *before* draining it, which
 * skips the subchart's 22-second write pause entirely (its preStop hook exits
 * immediately for a pod that is not the master). This is the other half: the
 * ~1-2s of a Sentinel promotion, in which no node in the cluster will accept a
 * write, and which nothing on the server side can remove — HAProxy is a TCP
 * proxy and cannot replay a command onto a different backend mid-stream.
 *
 * So the client has to retry, and these pin that it does, at each of the three
 * points in a search that talks to Redis. The failure is injected as the exact
 * exception Predis raises for `-READONLY`, because the class matters: Predis'
 * own retry layer catches `CommunicationException` and `-LOADING` and this is
 * neither, which is why it reached users in the first place.
 *
 * @see \App\Support\RedisFailover
 */
class SearchSurvivesFailoverTest extends TestCase
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

    private function search(string $query = "kaffee"): \Illuminate\Testing\TestResponse
    {
        return $this->get("/meta/meta.ger3?eingabe=" . urlencode($query) . "&focus=web&out=json");
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
     * Queueing the fetch missions is the one call with no degraded form — an
     * unqueued mission is an engine that never answers — so it is the one that
     * most has to survive.
     */
    public function testASearchSurvivesAReadonlyReplyWhenQueueingMissions(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["rpush" => 1]);

        $this->search()->assertOk();

        $this->assertTrue(
            $failer->allFailuresDelivered(),
            "the test did not actually simulate a failover: no rpush was issued"
        );
    }

    /**
     * The wait for results, where a search spends most of its time and is
     * therefore most likely to be holding a connection when a node goes.
     */
    public function testASearchSurvivesAReadonlyReplyWhileWaitingForResults(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["brpop" => 1]);

        $this->search()->assertOk();

        $this->assertTrue(
            $failer->allFailuresDelivered(),
            "the test did not actually simulate a failover: no brpop was issued"
        );
    }

    /**
     * Reading the answers back, on the way to the page.
     */
    public function testASearchSurvivesAReadonlyReplyWhileReadingAnswers(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["pipeline" => 1]);

        $this->search()->assertOk();

        $this->assertTrue(
            $failer->allFailuresDelivered(),
            "the test did not actually simulate a failover: no pipeline was issued"
        );
    }

    /**
     * A promotion is over in a second or two, but it is not instant, so one
     * retry is not the guarantee — the retry has to keep going for as long as
     * the budget allows.
     */
    public function testASearchSurvivesSeveralConsecutiveReadonlyReplies(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["rpush" => 3]);

        $this->search()->assertOk();

        $this->assertTrue($failer->allFailuresDelivered());
    }
}
