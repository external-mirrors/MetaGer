<?php

namespace Tests\Unit\Authentication;

use App\Authentication\KeyUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * What a key is allowed to spend, and how two requests using the same key stay
 * out of each other's way.
 *
 * A key is money. Charge lives on the keyserver, but a search spends it long
 * before the keyserver hears about it: the engines are asked first and
 * discharged afterwards, and several requests can be in that window at once
 * for the same key — the same key on a phone and a laptop, or the browser
 * extension firing suggestions alongside a search.
 *
 * Claims are how that is held together. Before spending, a request writes what
 * it intends to spend into `keyserver:claims:<key>` under an id unique to
 * itself, and treats everyone *else's* claims as charge that is already gone.
 * The field carries a TTL, so a request that dies without paying releases its
 * claim by itself.
 *
 * These tests exist because that arithmetic is the gate on an empty key, and
 * because the round trips it costs are about to be reduced — pinning the
 * behaviour first is what makes it possible to tell the reduction from a
 * regression. The one line that must never move is
 * {@see testAnEmptyKeyIsRefused}.
 *
 * Deliberately *not* pinned as exact: what happens when two requests race for
 * the last token. Claims are read and written without a lock, so a key can go
 * slightly negative under a race. That is a known and accepted trade — the
 * amount at risk is a fraction of a cent, and the lock needed to close it would
 * sit in front of every search.
 */
class KeyUserClaimsTest extends TestCase
{
    private const KEY = "claims-test-key";

    protected function tearDown(): void
    {
        $this->claimsConnection()->del("keyserver:claims:" . self::KEY);

        parent::tearDown();
    }

    private function claimsConnection(): mixed
    {
        return Redis::connection(config("cache.stores.redis.connection"));
    }

    /**
     * A KeyUser for a key with a known charge.
     *
     * KeyUser::getKeyData reads `keyserver:key:<key>` from the cache before it
     * asks the keyserver, so priming that is what keeps these tests off the
     * network.
     */
    private function keyUser(float $charge): KeyUser
    {
        Cache::put("keyserver:key:" . self::KEY, [
            "key" => self::KEY,
            "charge" => $charge,
        ], now()->addMinutes(10));

        return new KeyUser(self::KEY);
    }

    /**
     * What Redis says this key has claimed against it, across all processes.
     */
    private function claimedInRedis(): float
    {
        $claims = $this->claimsConnection()->hgetall("keyserver:claims:" . self::KEY);

        return array_sum(array_map(floatval(...), $claims ?: []));
    }

    public function testAKeyWithEnoughChargeIsAuthorized(): void
    {
        $this->assertTrue($this->keyUser(charge: 10.0)->authorize(1.0));
    }

    /**
     * The line this whole mechanism exists to hold. An exhausted key does not
     * get to search, whatever else is optimized around it.
     */
    public function testAnEmptyKeyIsRefused(): void
    {
        $this->assertFalse($this->keyUser(charge: 0.0)->authorize(1.0));
    }

    public function testAKeyIsRefusedAsSoonAsTheCostExceedsTheCharge(): void
    {
        $this->assertTrue($this->keyUser(charge: 2.0)->authorize(2.0), "A key with exactly the cost on it can spend it.");
        $this->assertFalse($this->keyUser(charge: 2.0)->authorize(2.1));
    }

    /**
     * Charge another request has spoken for is charge this one cannot have.
     * Without this, two concurrent searches on a nearly empty key would each
     * see the full balance and both spend it.
     */
    public function testAnotherProcessesClaimCountsAgainstTheCharge(): void
    {
        $other = $this->keyUser(charge: 6.0);
        $this->assertTrue($other->authorize(5.0), "The first request could not claim, so the second one proves nothing.");

        $mine = $this->keyUser(charge: 6.0);

        $this->assertFalse(
            $mine->authorize(2.0),
            "5 of 6 tokens are claimed by another request, so 2 more must not be authorized."
        );
    }

    /**
     * A request's own claim is not held against it, or the second engine of a
     * search would be refused on the strength of the first one.
     */
    public function testAProcessIsNotBlockedByItsOwnClaim(): void
    {
        $user = $this->keyUser(charge: 6.0);

        $this->assertTrue($user->authorize(5.0));
        $this->assertTrue(
            $user->authorize(1.0),
            "The same request was refused because of a claim it had made itself."
        );
    }

    /**
     * The claim has to reach Redis, because its whole job is to be visible to
     * the *other* request. A process that only tracked it in memory would let
     * two concurrent searches spend the same tokens.
     */
    public function testTheClaimIsWrittenWhereOtherProcessesCanSeeIt(): void
    {
        $this->keyUser(charge: 10.0)->authorize(3.0);

        $this->assertEqualsWithDelta(
            3.0,
            $this->claimedInRedis(),
            0.001,
            "The claim never reached Redis, so a concurrent request would not know about it."
        );
    }

    /**
     * A claim with no duration reserves nothing — used when only the answer
     * matters, as in Searchengines' loop deciding which engines the key can
     * afford at all.
     */
    public function testAZeroDurationClaimReservesNothing(): void
    {
        $user = $this->keyUser(charge: 10.0);

        $this->assertTrue($user->authorize(3.0, 0));
        $this->assertSame(
            0.0,
            $this->claimedInRedis(),
            "A claim was reserved for a check that only asked whether the key could afford it."
        );
    }

    /**
     * Paying releases the claim: the tokens have left the key for real now, so
     * holding them aside as well would charge the key twice over.
     */
    public function testPayingReleasesTheClaim(): void
    {
        $this->fakeDischarge(remainingCharge: 7.0);

        $user = $this->keyUser(charge: 10.0);
        $user->authorize(3.0);

        $this->assertTrue($user->makePayment(3.0), "The discharge did not go through.");
        $this->assertEqualsWithDelta(
            0.0,
            $this->claimedInRedis(),
            0.001,
            "The claim outlived the payment it stood in for, so the key is short by that much until it expires."
        );
    }

    /**
     * Paying more than was claimed claims the difference first, so the payment
     * is still covered by a claim while it is in flight.
     */
    public function testPayingMoreThanWasClaimedToppsTheClaimUpFirst(): void
    {
        $this->fakeDischarge(remainingCharge: 6.0);

        $user = $this->keyUser(charge: 10.0);
        $user->authorize(1.0);

        $this->assertTrue($user->makePayment(4.0));
        $this->assertEqualsWithDelta(
            0.0,
            $this->claimedInRedis(),
            0.001,
            "Claimed 1, topped up to 4, paid 4 — so nothing should be left claimed. Without the top-up this lands at -3, "
                . "meaning the key was holding a claim smaller than the payment it was covering."
        );
    }

    /**
     * A payment that exceeds what the key can cover is refused, and nothing is
     * discharged.
     */
    public function testAPaymentBeyondTheChargeIsRefused(): void
    {
        $this->fakeDischarge(remainingCharge: 0.0);

        $user = $this->keyUser(charge: 2.0);
        $user->authorize(1.0);

        $this->assertFalse(
            $user->makePayment(4.0),
            "A key with 2 tokens on it paid 4."
        );
        Http::assertNothingSent();
    }

    public function testAFreePaymentCostsNothingAndAsksNobody(): void
    {
        $this->fakeDischarge(remainingCharge: 10.0);

        $user = $this->keyUser(charge: 10.0);

        $this->assertTrue($user->makePayment(0.0));
        Http::assertNothingSent();
    }

    /**
     * The keyserver answers a discharge with the key's new charge. Faked rather
     * than reached: this suite never leaves the container.
     */
    private function fakeDischarge(float $remainingCharge): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/discharge" => Http::response(["key" => self::KEY, "charge" => $remainingCharge]),
        ]);
    }
}
