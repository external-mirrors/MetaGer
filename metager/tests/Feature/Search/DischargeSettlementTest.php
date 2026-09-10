<?php

namespace Tests\Feature\Search;

use App\Authentication\KeyUser;
use App\Console\Commands\SettleKeyDischarges;
use App\Events\KeyChanged;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * The charge a search no longer waits for.
 *
 * `POST /key/<key>/discharge` was the last thing on either hot path that
 * needed the keyserver to answer while a user waited, and behind the keyserver
 * is the only Postgres those paths depended on at all — so a CNPG switchover, a
 * keyserver rollout or a drained node reached the result page here and nowhere
 * else. It was also the one place a fee was simply lost when that happened,
 * because a charge that could not be made had nowhere to go.
 *
 * The search now writes the charge to Redis and `keys:settle-discharges` makes
 * it. Two properties have to hold for that to be an improvement rather than a
 * way to lose money quietly:
 *
 *   - the tokens stay reserved until the charge is actually recorded, so the
 *     gap between the two cannot be spent twice;
 *   - a keyserver that is down costs a delay, not the fee.
 *
 * And one has to hold for it not to be a regression: a failure that *might*
 * have charged the key is never retried.
 *
 * @see \App\Console\Commands\SettleKeyDischarges
 * @see \App\Authentication\KeyUser::makePayment()
 */
class DischargeSettlementTest extends TestCase
{
    use FakesSearchEngines;

    private const KEY = "settlement-test-key";

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis()->del(SettleKeyDischarges::REDIS_KEY);
        $this->redis()->del(KeyUser::claimsCacheKey(self::KEY));
        Cache::forget(KeyUser::keyDataCacheKey(self::KEY));
        Cache::forget(KeyUser::rememberedKeyDataCacheKey(self::KEY));

        // The settler dispatches KeyChanged so an open tab updates. Irrelevant
        // here, and it would otherwise reach for this environment's broadcaster.
        Event::fake([KeyChanged::class]);

        $this->keyserverBehaviour = fn() => $this->fail("the keyserver was asked to discharge unexpectedly");

        Http::preventStrayRequests();
        Http::fake(["*/discharge" => fn($request) => ($this->keyserverBehaviour)($request)]);
    }

    protected function tearDown(): void
    {
        $this->redis()->del(SettleKeyDischarges::REDIS_KEY);
        $this->redis()->del(KeyUser::claimsCacheKey(self::KEY));

        parent::tearDown();
    }

    private function redis(): mixed
    {
        return Redis::connection(config("cache.stores.redis.connection"));
    }

    /**
     * A KeyUser that has authorized and paid — which is to say, queued.
     */
    private function keyUserWhoHasPaid(float $charge, float $cost): KeyUser
    {
        Cache::put(KeyUser::keyDataCacheKey(self::KEY), [
            "key" => self::KEY,
            "charge" => $charge,
        ], now()->addMinutes(10));

        $user = new KeyUser(self::KEY);
        $user->authorize($cost);
        $user->makePayment($cost);

        return $user;
    }

    private function claimed(): float
    {
        $claims = $this->redis()->hgetall(KeyUser::claimsCacheKey(self::KEY));

        return array_sum(array_map(floatval(...), $claims ?: []));
    }

    private function queueLength(): int
    {
        return (int) $this->redis()->llen(SettleKeyDischarges::REDIS_KEY);
    }

    /**
     * What the keyserver does on the next discharge.
     *
     * One stub, installed once in setUp, delegating here. `Http::fake()` merges
     * into the stub list and the first registered match wins, so calling it a
     * second time to change the answer mid-test is silently ignored — and
     * `Http::clearResolvedInstances()` does not help, because the facade
     * re-resolves the same Factory singleton out of the container, stubs and
     * all. Two tests here need the keyserver to be down and then up, which is
     * exactly the shape that cannot be written the obvious way.
     *
     * @var callable
     */
    private $keyserverBehaviour;

    /** The keyserver confirms the discharge and reports the new balance. */
    private function keyserverAccepts(float $remaining): void
    {
        $this->keyserverBehaviour = fn() => Http::response(["key" => self::KEY, "charge" => $remaining]);
    }

    /**
     * @param string $message the cURL error, which is the only thing that
     *                        distinguishes a request that never left from one
     *                        that may have been applied
     */
    private function keyserverUnreachable(string $message): void
    {
        $this->keyserverBehaviour = fn() => throw new ConnectionException($message);
    }

    /**
     * The whole point, in one test: the request writes the charge down and asks
     * nobody.
     */
    public function testPayingQueuesTheChargeInsteadOfMakingIt(): void
    {
        $this->keyserverBehaviour = fn() => $this->fail("the discharge was made in the foreground");

        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);

        $this->assertSame(1, $this->queueLength());
    }

    /**
     * And the settler makes it, with the amount the search queued.
     */
    public function testTheSettlerDischargesTheQueuedAmount(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);
        $this->keyserverAccepts(remaining: 96.0);

        $this->artisan("keys:settle-discharges")->assertSuccessful();

        Http::assertSent(
            fn($request) => str_contains($request->url(), "/discharge")
                && $request->data()["amount"] === 4.0
        );
        $this->assertSame(0, $this->queueLength());
    }

    /**
     * The claim is the only thing standing between "spent" and "recorded as
     * spent". It has to survive the request and be released by the settler, or
     * those tokens are spendable a second time in the window between the two.
     */
    public function testTheClaimStandsUntilTheChargeIsRecorded(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);

        $this->assertEqualsWithDelta(4.0, $this->claimed(), 0.001, "the reservation was dropped while the charge was still queued");

        $this->keyserverAccepts(remaining: 96.0);
        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $this->assertEqualsWithDelta(0.0, $this->claimed(), 0.001, "the settled charge is still reserved as well, so the key is short by it");
    }

    /**
     * The reservation has to outlive the settlement window, not the search.
     *
     * `authorize()` gives a claim thirty seconds, which is a search. A queued
     * charge can wait five runs of a once-a-minute command, and a claim that
     * expired in the meantime would put tokens that are already spent back on
     * the key.
     */
    public function testQueueingExtendsTheClaimPastTheSearch(): void
    {
        $user = $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);

        $ttl = $this->redis()->httl(KeyUser::claimsCacheKey(self::KEY), [$user->id]);

        $this->assertGreaterThan(
            60,
            (int) $ttl[0],
            "the claim still expires on the search's own 30s, so a charge waiting on a retry outlives its reservation"
        );
        $this->assertLessThanOrEqual(KeyUser::SETTLEMENT_WINDOW_SECONDS, (int) $ttl[0]);
    }

    /**
     * A keyserver that is not there costs a delay, not the fee — which is the
     * whole difference from the foreground version, where the charge was made
     * once and lost if that failed.
     */
    public function testAnUnreachableKeyserverKeepsTheChargeQueued(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);
        $this->keyserverUnreachable("cURL error 7: Failed to connect to keys.metager.de port 443: Connection refused");

        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $this->assertSame(1, $this->queueLength(), "the charge was dropped on a failure that never reached the keyserver");
        $this->assertEqualsWithDelta(4.0, $this->claimed(), 0.001, "the tokens were handed back while the charge was still owed");

        // And it settles once the keyserver is back.
        $this->keyserverAccepts(remaining: 96.0);
        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $this->assertSame(0, $this->queueLength());
    }

    /**
     * A timeout is not retried.
     *
     * Guzzle reports a refused connection and a request that timed out
     * mid-flight as the same exception class, and only the cURL error number in
     * the message tells them apart. A timeout may be a discharge that was
     * applied and whose reply was lost, so retrying it would charge a user
     * twice — worse than the operator losing the fee.
     *
     * The claim is deliberately left standing in that case too: if the key may
     * have been charged, handing the tokens back would be the second half of a
     * double spend. It expires on its own.
     */
    public function testATimeoutIsDroppedRatherThanRetried(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);
        $this->keyserverUnreachable("cURL error 28: Operation timed out after 5001 milliseconds");

        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $this->assertSame(
            0,
            $this->queueLength(),
            "a discharge that may already have been applied was queued for another attempt"
        );
        $this->assertEqualsWithDelta(
            4.0,
            $this->claimed(),
            0.001,
            "tokens that may already have been charged were handed back"
        );
    }

    /**
     * A keyserver that answers and refuses has spoken. Nothing was charged, so
     * the reservation goes back and the charge is not tried again.
     */
    public function testARefusedDischargeIsNotRetriedAndReleasesTheClaim(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);

        $this->keyserverBehaviour = fn() => Http::response(["detail" => "no such key"], 404);

        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $this->assertSame(0, $this->queueLength());
        $this->assertEqualsWithDelta(0.0, $this->claimed(), 0.001);
    }

    /**
     * Five runs and then it is given up on, so an unreachable keyserver cannot
     * fill Valkey with charges nobody will ever pay.
     */
    public function testAChargeIsAbandonedAfterEnoughAttempts(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);
        $this->keyserverUnreachable("cURL error 7: Connection refused");

        for ($run = 0; $run < 6; $run++) {
            $this->artisan("keys:settle-discharges")->assertSuccessful();
        }

        $this->assertSame(0, $this->queueLength(), "a charge nobody can settle stayed on the queue for ever");
        $this->assertEqualsWithDelta(0.0, $this->claimed(), 0.001, "the abandoned charge is still reserving tokens");
    }

    /**
     * The settled balance is written where the next page will read it, so the
     * charge shows up without another keyserver round trip.
     */
    public function testTheSettledBalanceIsWrittenBackToTheCache(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);
        $this->keyserverAccepts(remaining: 96.0);

        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $this->assertEqualsWithDelta(96.0, Cache::get(KeyUser::keyDataCacheKey(self::KEY))["charge"], 0.001);
        $this->assertEqualsWithDelta(96.0, Cache::get(KeyUser::rememberedKeyDataCacheKey(self::KEY))["charge"], 0.001);
    }

    /**
     * Until then the request's own estimate stands in, so the balance on the
     * page moves when the user spends rather than a minute later.
     *
     * Estimated, not asserted as authoritative: the settler overwrites it with
     * the keyserver's own number, and the ten-second TTL means an estimate that
     * turns out wrong is corrected by the next lookup rather than standing.
     */
    public function testTheBalanceMovesBeforeTheChargeIsSettled(): void
    {
        $user = $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);

        $this->assertSame(96.0, $user->getCharge(), "the page would show a balance that has not moved");
        $this->assertSame(96.0, Cache::get(KeyUser::keyDataCacheKey(self::KEY))["charge"]);
    }

    /**
     * The same charge on the queue twice is paid once.
     *
     * KeyUser::queueDischarge() writes through App\Support\RedisFailover,
     * which re-issues a write whose reply a Sentinel promotion swallowed —
     * and `lpush` is not idempotent, so a promotion landing between the write
     * and its reply leaves the same charge queued twice. Retrying is right
     * (not retrying drops every charge made during a drain) and charging a
     * user twice is not, so the id carried with the charge is what settles it:
     * the first run to reach an id gets to pay it and every copy after it is
     * thrown away.
     */
    public function testAChargeQueuedTwiceIsOnlyPaidOnce(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);

        // Exactly what a retried lpush leaves behind: the same payload, twice.
        $queued = $this->redis()->lindex(SettleKeyDischarges::REDIS_KEY, 0);
        $this->redis()->lpush(SettleKeyDischarges::REDIS_KEY, $queued);
        $this->assertSame(2, $this->queueLength());

        $this->keyserverAccepts(remaining: 96.0);
        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $discharges = collect(Http::recorded())
            ->filter(fn($pair) => str_contains($pair[0]->url(), "/discharge"))
            ->count();

        $this->assertSame(1, $discharges, "the same charge was discharged twice, so the user paid twice for one search");
        $this->assertSame(0, $this->queueLength());
    }

    /**
     * And the duplicate guard does not eat a charge that only failed to reach
     * the keyserver: nothing was paid, so the id has to stay payable.
     *
     * Without the release in reschedule(), the retry this whole mechanism
     * exists to allow would be refused by the guard on its second attempt and
     * the charge lost — a fix that turns a rare double charge into a
     * guaranteed lost one.
     */
    public function testARetriedChargeIsStillPayable(): void
    {
        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);
        $this->keyserverUnreachable("cURL error 7: Connection refused");
        $this->artisan("keys:settle-discharges")->assertSuccessful();

        $this->keyserverAccepts(remaining: 96.0);
        $this->artisan("keys:settle-discharges")->assertSuccessful();

        Http::assertSent(fn($request) => str_contains($request->url(), "/discharge"));
        $this->assertSame(0, $this->queueLength());
        $this->assertEqualsWithDelta(0.0, $this->claimed(), 0.001);
    }

    /**
     * The balance must not go back *up* while the charge is queued.
     *
     * The estimate {@see \App\Authentication\KeyUser::makePayment()} writes is
     * what keeps the number on the page moving when a user spends. If it
     * expires before the settler has run, the next lookup asks the keyserver,
     * which has not been told about the charge yet and truthfully answers the
     * old balance — so the account pill counts down, waits, and then counts
     * back up, before finally settling. The foreground discharge never had that
     * window because the keyserver knew immediately.
     *
     * Thirty seconds is inside the settler's schedule (once a minute) and well
     * past the ten seconds getKeyData() caches a keyserver answer for, which is
     * exactly the gap.
     */
    public function testTheBalanceDoesNotGoBackUpWhileTheChargeIsQueued(): void
    {
        // What the keyserver still says, because nobody has discharged yet.
        Http::fake(["*/api/json/key/*" => Http::response(["key" => self::KEY, "charge" => 100.0])]);

        $this->keyUserWhoHasPaid(charge: 100.0, cost: 4.0);

        $this->travel(30)->seconds();

        $this->assertSame(
            96.0,
            (new KeyUser(self::KEY))->getCharge(),
            "the spent balance reappeared while the charge was still in the queue"
        );
    }

    /**
     * An empty queue is the normal state — this runs every minute and most
     * minutes have nothing in them — and must cost one Redis read, not a
     * keyserver request.
     */
    public function testAnEmptyQueueAsksNobodyAnything(): void
    {
        // setUp's stub already fails on any discharge request, which is the
        // assertion: an empty queue costs one Redis read and no network.
        $this->artisan("keys:settle-discharges")->assertSuccessful();
    }
}
