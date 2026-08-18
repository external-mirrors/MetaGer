<?php

namespace Tests\Feature\Authentication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The key guard resolves at most once per request.
 *
 * It used to resolve once per *call* for anonymous visitors: `user()` returned
 * null without recording that it had looked, so the next call went back to the
 * cookie jar, the headers and the query string and worked it all out again.
 * Every `Auth::check()`, every `Auth::guest()`, every `@auth` in a blade.
 *
 * The call site that made this worth fixing is
 * Searchengines::__construct, which asked for the user once per configured
 * engine — sixteen times on a web search, for an answer that cannot change
 * between the first engine and the last.
 *
 * Memoisation is also the honest semantics. Within one request there is one
 * visitor, so a guard that can change its mind halfway through is a guard that
 * can hand two different answers to two pieces of the same page.
 */
class KeyAuthGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake([
            "*/discharge" => Http::response(["key" => "test-key", "charge" => 1000.0]),
            "*" => Http::response(""),
        ]);
        // The provider reads the key server through the cache; without this the
        // key resolves to null and every assertion below passes vacuously.
        Cache::put("keyserver:key:test-key", ["key" => "test-key", "charge" => 1000.0], now()->addMinutes(10));
    }

    private function guard(Request $request)
    {
        $this->app->instance("request", $request);
        // The Request facade caches its resolved instance, so rebinding the
        // container entry alone leaves the guard reading the old request.
        Facade::clearResolvedInstance("request");
        Auth::forgetGuards();

        return Auth::guard("key");
    }

    public function testAnAnonymousVisitorIsResolvedOnlyOnce(): void
    {
        $guard = $this->guard(Request::create("/meta/meta.ger3", "GET", ["eingabe" => "kaffee"]));

        $this->assertNull($guard->user());

        // A key appearing on the request after the guard has already answered
        // must not change the answer. Before memoisation it did, which is the
        // observable form of "this whole lookup runs again every time".
        $this->app->make("request")->query->set("key", "test-key");

        $this->assertNull(
            $guard->user(),
            "The guard went back to the request and resolved a second, different user mid-request."
        );
    }

    public function testAKeyOnTheQueryStringResolvesToTheSameUserEveryTime(): void
    {
        $guard = $this->guard(Request::create("/meta/meta.ger3", "GET", ["key" => "test-key"]));

        $first = $guard->user();

        $this->assertNotNull($first);
        $this->assertSame($first, $guard->user(), "The guard rebuilt the user instead of reusing it.");
    }

    /**
     * Logging out has to stick. The cookie is only *queued* for deletion, so it
     * is still readable for the rest of the request — a guard that re-resolved
     * would find it and log the visitor straight back in.
     */
    public function testLoggingOutSticksForTheRestOfTheRequest(): void
    {
        $request = Request::create("/meta/meta.ger3", "GET", ["key" => "test-key"]);
        $guard = $this->guard($request);

        $this->assertNotNull($guard->user());

        $guard->logout();

        $this->assertNull($guard->user(), "The visitor was logged back in by the key still on the request.");
    }

    /**
     * The anonymous-token header is a login like any other and was already
     * memoised; pinned so the rewrite cannot lose it, along with the
     * login_method it sets — the SafeBrowse link reads that to decide whether to
     * put the key in a URL.
     */
    public function testTheAnonymousTokenHeaderStillLogsInAsAHeader(): void
    {
        $request = Request::create("/meta/meta.ger3", "GET");
        $request->headers->set("anonymous-token-key", "test-key");

        $guard = $this->guard($request);
        $user = $guard->user();

        $this->assertNotNull($user);
        $this->assertTrue($user->temporary);
        $this->assertSame("header", $guard->login_method);
        $this->assertSame($user, $guard->user());
    }
}
