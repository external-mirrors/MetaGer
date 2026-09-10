<?php

namespace Tests\Unit\Authentication;

use App\Authentication\KeyState;
use App\Authentication\KeyUser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A keyserver that does not answer must not log anyone out.
 *
 * These two calls — the charge read and the discharge — were the only ones in
 * the application reaching the keyserver with no timeout and no catch. Every
 * other caller sets both (KeyIssuer, LoginCodeIssuer, ChargeOrderIssuer,
 * OrderHistoryIssuer, CampaignIssuer, KeyResolver, KeyPrice, AppRelease); these
 * two were missed, and they are the two that run on the start page and the
 * result page.
 *
 * What that cost, concretely:
 *
 *  - **Time.** Laravel's defaults are 10s to connect and 30s to read
 *    (PendingRequest::__construct). A keyserver pod being rescheduled does not
 *    answer slowly, it does not answer at all, so the start page sat on the
 *    connect timeout. Enough of those at once and fpm's worker pool is gone,
 *    which takes down every other route with it.
 *  - **The answer.** `Http::get()` throws ConnectionException on a failed
 *    connect, and nothing caught it, so the page 500ed. Where it did not, a
 *    null `charge` reads as "no key" everywhere downstream: the account renders
 *    logged out, every paid engine is disabled, and the search redirects to the
 *    start page. A perfectly good key, reported broken, because something the
 *    user does not own was restarted.
 *
 * The fallback is the last answer the keyserver gave for that key, kept for an
 * hour. It is read *only* when the keyserver cannot be reached — an answer we
 * do not like (404, 401) is still an answer, and contradicting it with a
 * remembered charge would be a different bug.
 *
 * @see \App\Authentication\KeyUser
 */
class KeyUserUnreachableKeyserverTest extends TestCase
{
    private const KEY = "b3f1c2d4-5e6a-4b7c-8d9e-0f1a2b3c4d5e";

    /**
     * What the keyserver does on the next request.
     *
     * These tests need the keyserver to behave one way and then another within
     * a single test — answer once so there is something to remember, then stop
     * answering — and `Http::fake()` cannot express that by being called twice.
     * It *merges* into the stub list and the first registered match wins, so the
     * second call is silently ignored. `Http::clearResolvedInstances()` does not
     * help either: the facade re-resolves the same Factory singleton out of the
     * container, stubs and all. (That is worth knowing before writing the
     * obvious version of this test and watching it pass for the wrong reason.)
     *
     * So: one stub, installed once, that asks this property what to do.
     *
     * @var callable
     */
    private $keyserverBehaviour;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget("keyserver:key:" . self::KEY);
        Cache::forget("keyserver:key:" . self::KEY . ":last");

        $this->keyserverSays(0.0);

        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/*" => fn($request) => ($this->keyserverBehaviour)($request),
        ]);
    }

    /** The keyserver cannot be reached at all. */
    private function keyserverUnreachable(): void
    {
        $this->keyserverBehaviour = function () {
            throw new ConnectionException(
                'cURL error 7: Failed to connect to keys.metager.de port 443: Connection refused'
            );
        };
    }

    /** The keyserver answers normally, with this charge. */
    private function keyserverSays(float $charge): void
    {
        $this->keyserverBehaviour = fn() => Http::response(["key" => self::KEY, "charge" => $charge]);
    }

    /** The keyserver answers, but refuses this key. */
    private function keyserverRefusesTheKey(): void
    {
        $this->keyserverBehaviour = fn() => Http::response(["detail" => "not found"], 404);
    }

    /**
     * Populate the fallback the way a normal request does — by asking the
     * keyserver once while it is up — rather than by writing the cache key
     * directly. The name of that key is an implementation detail; that a
     * successful read leaves something behind for the next failure is not.
     */
    private function warmTheFallback(float $charge): void
    {
        $this->keyserverSays($charge);
        (new KeyUser(self::KEY))->getCharge();
        Cache::forget("keyserver:key:" . self::KEY);
    }

    public function testAnUnreachableKeyserverDoesNotThrow(): void
    {
        $this->keyserverUnreachable();

        // Before the catch, this was an uncaught ConnectionException — a 500 on
        // the start page. PHPUnit reports it without any assertion of ours.
        $charge = (new KeyUser(self::KEY))->getCharge();

        $this->assertNull($charge, "with nothing remembered there is nothing to answer with");
    }

    public function testTheLastKnownChargeAnswersWhileTheKeyserverIsDown(): void
    {
        $this->warmTheFallback(1234.5);
        $this->keyserverUnreachable();

        $this->assertSame(1234.5, (new KeyUser(self::KEY))->getCharge());
    }

    /**
     * The charge is what the key *state* is derived from, and the state is what
     * the start page branches on — so a fallback that restored the number but
     * not the state would still render the landing page at a signed-in user.
     */
    public function testTheKeyStateSurvivesToo(): void
    {
        $this->warmTheFallback(500.0);
        $this->keyserverUnreachable();

        $this->assertSame(KeyState::FULL, (new KeyUser(self::KEY))->getKeyState());
    }

    /**
     * A 404 is the keyserver telling us this key does not exist. Answering with
     * a remembered charge would override a fact with a memory.
     */
    public function testARefusedKeyIsNotPaperedOverByTheFallback(): void
    {
        $this->warmTheFallback(1234.5);
        $this->keyserverRefusesTheKey();

        $this->assertNull((new KeyUser(self::KEY))->getCharge());
    }

    /**
     * The fallback must not become the answer for good: once the keyserver is
     * back, the request after it asks again rather than finding the remembered
     * copy sitting in the hot cache.
     */
    public function testTheFallbackIsNotWrittenBackIntoTheHotCache(): void
    {
        $this->warmTheFallback(1234.5);
        $this->keyserverUnreachable();

        (new KeyUser(self::KEY))->getCharge();

        $this->assertNull(
            Cache::get("keyserver:key:" . self::KEY),
            "a stale charge left in the hot cache would outlive the outage"
        );
    }

    /**
     * An unreachable keyserver during a discharge means the charge did not
     * happen, and this says so. `true` would be the tempting answer — it keeps
     * the search free of consequences — but it would also silently give the
     * search away, which is a decision for the operator and not for a catch
     * block. Both callers tolerate `false`: MetaGerSearch discharges once after
     * the page is already answered and ignores the result.
     */
    public function testAnUnreachableKeyserverFailsThePaymentRatherThanThePage(): void
    {
        $this->warmTheFallback(1000.0);
        $this->keyserverUnreachable();

        $user = new KeyUser(self::KEY);
        $user->authorize(1.0);

        $this->assertFalse($user->makePayment(1.0));
    }

    /**
     * A payment of nothing is the common case on the search path
     * (AuthenticationValidation passes the suggestion debt, normally zero) and
     * must not reach the network at all — otherwise every search would pay the
     * connect timeout during an outage for a charge of zero.
     */
    public function testAFreePaymentAsksTheKeyserverNothing(): void
    {
        // Any request at all fails the test, which is the assertion: a charge
        // of nothing must not cost a network round trip. During an outage this
        // is the difference between every search paying the connect timeout and
        // none of them doing so.
        $this->keyserverBehaviour = fn() => $this->fail("the keyserver was asked to discharge nothing");

        $this->assertTrue((new KeyUser(self::KEY))->makePayment(0.0));
    }
}
