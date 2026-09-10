<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Facades\Redis;
use Tests\Concerns\FakesSearchEngines;
use Tests\Support\FailingOverRedis;
use Tests\TestCase;

/**
 * The half of a search that happens before the search.
 *
 * {@see SearchSurvivesFailoverTest} pins the three points at which
 * EngineOrchestrator talks to Redis. It does not reach the ones that run
 * *first*: AuthenticationValidation is route middleware, so everything it does
 * happens before MetaGerSearch is entered at all, and all of it talks to the
 * same Valkey through the same master proxy.
 *
 * On every authenticated search, in this order:
 *
 *   - `SuggestionController::GET_SUGGESTION_GROUP_LIST` — `lrange`
 *   - `SuggestionController::ABORT_SUGGESTION_GROUP_REQUEST` — `rpush`,
 *     `pexpireat`
 *   - `SuggestionDebtAuthorization::GET_DEBT` — `hget`
 *   - `KeyUser::authorize` — `hgetall`, then the claim pipeline
 *   - `SuggestionDebtAuthorization::ADD_CREDIT`/`UPDATE_SETTINGS`/`ADD_DEBT` —
 *     `hincrbyfloat`, `hset`, `hexpireat`
 *
 * Only the claim pipeline was guarded. Everything else was bare, most of it
 * writes, and a write is what a demoted node answers `-READONLY`. So a Sentinel
 * promotion landing anywhere in that list answered a search with a 503 before
 * the search had begun — and the retry that was supposed to make drains free
 * never got a chance to run, because the request never reached the code that
 * has it.
 *
 * What is being traded away when these give up is suggestion-credit
 * bookkeeping: a tenth of a token, capped, self-expiring in two days. Set
 * against an error page, it is not a close call.
 *
 * @see \App\Support\RedisFailover
 * @see \App\Models\Authorization\SuggestionDebtAuthorization
 */
class AuthorizationSurvivesFailoverTest extends TestCase
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

    private function search(array $settings = []): \Illuminate\Testing\TestResponse
    {
        $query = array_merge(["eingabe" => "kaffee", "focus" => "web", "out" => "json"], $settings);

        return $this->get("/meta/meta.ger3?" . http_build_query($query));
    }

    /**
     * Suggestion credit is only kept for users who have address-bar
     * suggestions on — SuggestionDebtAuthorization::ADD_CREDIT returns
     * immediately otherwise — and the setting is off by default. So a test that
     * wants to see the credit *writes* has to ask for them; SearchSettings
     * reads a plain query parameter first of all, which is the shortest way to
     * say so.
     *
     * @return array<string, string>
     */
    private function withSuggestionCredit(): array
    {
        return ["suggestion_addressbar" => "on"];
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
     * The suggestion-credit write-back, which runs after the key has been
     * authorized but still before `$next($request)` — so its failure took the
     * search with it.
     */
    public function testASearchSurvivesAReadonlyReplyWhileWritingSuggestionCredit(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["hincrbyfloat" => 1]);

        $this->search($this->withSuggestionCredit())->assertOk();

        $this->assertTrue(
            $failer->allFailuresDelivered(),
            "the test did not actually simulate a failover: no hincrbyfloat was issued"
        );
    }

    /**
     * Reading the debt, and reading the claims — the two `hget`/`hgetall` reads
     * the middleware makes before it decides whether this search may proceed.
     */
    public function testASearchSurvivesAReadonlyReplyWhileReadingTheDebt(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["hget" => 1, "hgetall" => 1]);

        $this->search()->assertOk();

        $this->assertTrue(
            $failer->allFailuresDelivered(),
            "the test did not actually simulate a failover: no hget/hgetall was issued"
        );
    }

    /**
     * The expiry stamps that go with every credit and debt write. Separate
     * commands, separately capable of ending the request.
     */
    public function testASearchSurvivesAReadonlyReplyWhileStampingExpiry(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["hexpireat" => 1]);

        $this->search($this->withSuggestionCredit())->assertOk();

        $this->assertTrue(
            $failer->allFailuresDelivered(),
            "the test did not actually simulate a failover: no hexpireat was issued"
        );
    }

    /**
     * A promotion is not instant, so surviving one -READONLY is not the
     * guarantee — the retry has to keep going for as long as its budget allows.
     */
    public function testASearchSurvivesSeveralConsecutiveReadonlyReplies(): void
    {
        $this->fakeEngines();
        $failer = $this->failOverDuring(["hincrbyfloat" => 3]);

        $this->search($this->withSuggestionCredit())->assertOk();

        $this->assertTrue($failer->allFailuresDelivered());
    }

    /**
     * And when the failover outlasts the budget, the bookkeeping is what is
     * lost — not the search. This is the trade stated explicitly: enough
     * consecutive failures that no retry can succeed, and the page is still
     * answered.
     */
    public function testABookkeepingWriteThatCannotSucceedStillLeavesTheSearch(): void
    {
        $this->fakeEngines();
        $this->failOverDuring(["hincrbyfloat" => 99, "hset" => 99, "hexpireat" => 99]);

        $this->search($this->withSuggestionCredit())->assertOk();
    }
}
