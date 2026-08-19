<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * A paid search discharges the key, whichever transport carried it.
 *
 * The key reaches MetaGer four ways — `key` cookie, `Key` header, `key` query
 * parameter, and `Anonymous-Token-Key` header — and KeyAuthGuard resolves all
 * four to the same KeyUser. Only the last one marks it `temporary`, and that
 * flag is documented as affecting nothing but getKeyState(). So a search that
 * charges through one transport has to charge through the others: the amount
 * owed is a property of the engines that ran, not of how the caller identified
 * themselves.
 *
 * Written against a live report that discharges land for a cookie login and not
 * for an Anonymous-Token-Key one. The existing search tests all authenticate
 * with $this->be(), which installs the user directly on the guard and therefore
 * cannot see a difference between transports at all — every one of them would
 * stay green while this was broken.
 *
 * The assertion is on the outgoing discharge call rather than on the response,
 * because a search that fails to charge still renders a perfectly good result
 * page. That is exactly why this can be live without anything looking wrong.
 */
class AnonymousTokenKeyDischargeTest extends TestCase
{
    use FakesSearchEngines;

    private const KEY = 'transport-test-key';

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims(self::KEY);

        parent::tearDown();
    }

    /**
     * Primes the keyserver fake and the cache, but deliberately does *not* call
     * $this->be() — the whole point is to make the guard resolve the key off the
     * request the way production does.
     */
    private function primeKey(float $charge = 1000.0): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/discharge" => Http::response(["key" => self::KEY, "charge" => $charge]),
            "*" => Http::response(""),
        ]);

        Cache::put("keyserver:key:" . self::KEY, [
            "key" => self::KEY,
            "charge" => $charge,
        ], now()->addMinutes(10));

        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);
    }

    public function testAKeyCookieDischargesTheKey(): void
    {
        $this->primeKey();

        // withUnencryptedCookie, not withCookie: the latter encrypts, and
        // bootstrap/app.php removes EncryptCookies from the web group, so the
        // guard would receive the ciphertext itself as the key.
        $response = $this->withUnencryptedCookie('key', self::KEY)
            ->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");

        $this->assertSame(
            200,
            $response->getStatusCode(),
            "redirected to: " . ($response->headers->get("Location") ?? "(none)")
            . "\nguard user  : " . (\Auth::guard("key")->user()?->key ?? "NULL — the cookie never resolved")
            . "\ndischarges  : " . json_encode($this->recordedDischargeAmounts())
        );

        Http::assertSent(
            fn($request) => str_contains($request->url(), '/discharge'),
        );
    }

    /**
     * The reported case. Same key, same query, same engines — only the transport
     * differs.
     */
    public function testAnAnonymousTokenKeyHeaderDischargesTheKey(): void
    {
        $this->primeKey();

        $response = $this->withHeader('Anonymous-Token-Key', self::KEY)
            ->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");

        $this->assertSame(200, $response->getStatusCode());

        $discharges = collect(Http::recorded())
            ->filter(fn($pair) => str_contains($pair[0]->url(), '/discharge'))
            ->count();

        $this->assertGreaterThan(
            0,
            $discharges,
            "A paid search authenticated with Anonymous-Token-Key never discharged the key. "
            . "The same search with a `key` cookie does, so the engines ran and something was owed — "
            . "the charge is being skipped for this transport only."
        );
    }

    /**
     * Both transports must agree on the amount, not merely both charge
     * something. A discharge for a different sum is the same bug wearing a
     * disguise.
     */
    public function testBothTransportsDischargeTheSameAmount(): void
    {
        // Distinct queries on purpose. Engine results are cached per query, and
        // MetaGerSearch only bills engines that are `!cached` — so running the
        // same search twice makes the second one free for a reason that has
        // nothing to do with the transport, and would report a phantom bug.
        $this->primeKey();
        $this->withUnencryptedCookie('key', self::KEY)
            ->get("/meta/meta.ger3?eingabe=kaffee-cookie&focus=web&out=json");
        $viaCookie = $this->recordedDischargeAmounts();

        $this->forgetSearchUserClaims(self::KEY);

        // Two searches in one test method share the application instance, so the
        // second request would otherwise reuse the first one's Searchengines
        // (every engine already marked `cached`, hence nothing owed) and the
        // guard's memoised KeyUser (still the *cookie* login, so the transport
        // under test would never be read). Both have to go, or this method
        // measures request-scope leakage rather than the transport.
        $this->forgetRequestScopedServices();
        \Auth::forgetGuards();

        $this->primeKey();
        $this->withHeader('Anonymous-Token-Key', self::KEY)
            ->get("/meta/meta.ger3?eingabe=kaffee-header&focus=web&out=json");
        $viaHeader = $this->recordedDischargeAmounts();

        // Both-empty would satisfy assertSame while meaning "neither transport
        // charged anything", which is the very bug this file is about.
        $this->assertNotEmpty(
            $viaCookie,
            "The cookie login charged nothing, so this comparison proves nothing."
        );

        $this->assertSame(
            $viaCookie,
            $viaHeader,
            "The two transports charged different amounts for the same search.\n"
            . "  cookie: " . json_encode($viaCookie) . "\n"
            . "  header: " . json_encode($viaHeader)
        );
    }

    /**
     * Characterization test: a `key` cookie silently outranks an
     * `Anonymous-Token-Key` header, and the cookie's key is the one that pays.
     *
     * This is the behaviour that produced a live "anonymous keys are never
     * charged" report. A browser holding a leftover `key` cookie *and* running
     * the extension sends both. KeyAuthGuard reads the cookie first and only
     * falls through to the header when cookie, `Key` header and `key` query
     * parameter are all empty — so the search is charged to the cookie's key
     * while the extension sits on a websocket subscribed to the anonymous key's
     * channel. The anonymous key's balance never moves and no KeyChanged ever
     * arrives, which looks exactly like a broken event pipeline and is not one.
     *
     * Pinned rather than fixed, because the precedence is a product decision:
     * the header is injected deliberately per request while the cookie is
     * ambient state that can be arbitrarily stale, which is an argument for the
     * header winning — but changing the order moves who pays, so it is not a
     * change to make silently. If the order is deliberately changed, this test
     * is supposed to fail.
     */
    public function testAKeyCookieSilentlyOutranksAnAnonymousTokenKeyHeader(): void
    {
        $cookieKey = 'cookie-key';
        $anonymousKey = 'anonymous-key';

        Http::preventStrayRequests();
        Http::fake([
            "*/discharge" => Http::response(["key" => $cookieKey, "charge" => 1000.0]),
            "*" => Http::response(""),
        ]);
        foreach ([$cookieKey, $anonymousKey] as $key) {
            Cache::put("keyserver:key:" . $key, ["key" => $key, "charge" => 1000.0], now()->addMinutes(10));
        }
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $this->withUnencryptedCookie('key', $cookieKey)
            ->withHeader('Anonymous-Token-Key', $anonymousKey)
            ->get("/meta/meta.ger3?eingabe=kaffee-collision&focus=web&out=json")
            ->assertOk();

        $charged = collect(Http::recorded())
            ->map(fn($pair) => $pair[0]->url())
            ->filter(fn($url) => str_contains($url, '/discharge'))
            ->values()
            ->all();

        $this->assertNotEmpty($charged, "Nothing was charged, so this proves nothing about which key paid.");

        $this->assertStringContainsString(
            urlencode($cookieKey),
            $charged[0],
            "Expected the cookie's key to be the one charged."
        );
        $this->assertStringNotContainsString(
            urlencode($anonymousKey),
            $charged[0],
            "The anonymous-token-key was charged, so the documented precedence has changed."
        );

        $this->forgetSearchUserClaims($cookieKey);
        $this->forgetSearchUserClaims($anonymousKey);
    }

    /**
     * @return array<int, mixed> the `amount` of every discharge POST, in order
     */
    private function recordedDischargeAmounts(): array
    {
        return collect(Http::recorded())
            ->filter(fn($pair) => str_contains($pair[0]->url(), '/discharge'))
            ->map(fn($pair) => $pair[0]->data()['amount'] ?? null)
            ->values()
            ->all();
    }
}
