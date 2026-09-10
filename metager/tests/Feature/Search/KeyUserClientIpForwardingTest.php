<?php

namespace Tests\Feature\Search;

use App\Events\KeyChanged;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * KeyUser calls the keyserver with a shared Bearer token
 * (config('metager.metager.keymanager.access_token')), so from the keyserver's login rate
 * limiter's point of view every MetaGer request looks like the same caller unless the actual
 * end user's IP travels with it. Without this, one abusive `?key=` guesser behind MetaGer would
 * exhaust the keyserver's per-IP budget for every MetaGer user at once — see the keymanager plan's
 * Phase 2a note on "ein einzelner Angreifer über `?key=` sperrt die ganze Seite".
 *
 * `getKeyData()` (charge lookup) and `makePayment()` (discharge) are the two call sites; both are
 * exercised here via a real search request rather than by invoking KeyUser directly, so the
 * assertion covers the actual `Request::ip()` the framework resolves for that request.
 *
 * The discharge no longer happens during the request — it is queued and
 * `keys:settle-discharges` makes it (App\Console\Commands\SettleKeyDischarges).
 * That is exactly why the header still needs a test: the settler is a background
 * process with no request and no `Request::ip()` of its own, so the address has
 * to be carried in the queued payload or it silently stops being sent, and the
 * keyserver goes back to seeing all of MetaGer as one caller.
 */
class KeyUserClientIpForwardingTest extends TestCase
{
    use FakesSearchEngines;

    private const KEY = 'client-ip-forwarding-test-key';
    private const CLIENT_IP = '203.0.113.42';

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims(self::KEY);
        $this->forgetQueuedDischarges();

        parent::tearDown();
    }

    public function testTheDischargeCallForwardsTheEndUsersIp(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/discharge" => Http::response(["key" => self::KEY, "charge" => 1000.0]),
            "*" => Http::response(""),
        ]);
        Cache::put("keyserver:key:" . self::KEY, ["key" => self::KEY, "charge" => 1000.0], now()->addMinutes(10));
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $this->call(
            'GET',
            '/meta/meta.ger3?eingabe=kaffee&focus=web&out=json',
            [],
            ['key' => self::KEY],
            [],
            ['REMOTE_ADDR' => self::CLIENT_IP],
        )->assertOk();

        // The request only wrote the charge down. Settling is what talks to the
        // keyserver, and it is the call whose headers are under test.
        $this->artisan('keys:settle-discharges')->assertSuccessful();

        Http::assertSent(
            fn($request) =>
            str_contains($request->url(), '/discharge')
                && $request->hasHeader('X-Forwarded-For', self::CLIENT_IP)
        );
    }

    public function testTheChargeLookupForwardsTheEndUsersIp(): void
    {
        // getKeyData() dispatches KeyChanged on a cache miss, which would otherwise try to reach
        // this environment's Reverb broadcaster - irrelevant to what this test checks.
        Event::fake([KeyChanged::class]);

        Http::preventStrayRequests();
        // No cache entry: forces getKeyData() to actually reach the keyserver instead of
        // answering from Cache::get("keyserver:key:...").
        Http::fake([
            "*/key/*" => Http::response(["key" => self::KEY, "charge" => 1000.0]),
            "*" => Http::response(""),
        ]);
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $this->call(
            'GET',
            '/meta/meta.ger3?eingabe=tee&focus=web&out=json',
            [],
            ['key' => self::KEY],
            [],
            ['REMOTE_ADDR' => self::CLIENT_IP],
        )->assertOk();

        Http::assertSent(
            fn($request) =>
            str_contains($request->url(), '/key/' . self::KEY)
                && !str_contains($request->url(), '/discharge')
                && $request->hasHeader('X-Forwarded-For', self::CLIENT_IP)
        );
    }
}
