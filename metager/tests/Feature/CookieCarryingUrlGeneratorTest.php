<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * `route()` and `url()` carry a cookie-blind visitor's `key` into every
 * same-origin link they generate — the query-string counterpart to
 * `URL::formatPathUsing`'s locale prefix, covered by
 * LocalePrefixedUrlGenerationTest.
 *
 * Swaps the container's `request` binding directly rather than dispatching a
 * real HTTP request, mirroring SignedUrlUnderLocalePrefixTest's
 * `underSpanishPrefix()`: CookieCarryingUrlGenerator reads its own internal
 * `$request`, set via the `rebinding('request', ...)` listener registered in
 * AppServiceProvider, so swapping the binding is enough — no route needs to
 * actually be dispatched, and no Vite build is needed for this suite.
 */
class CookieCarryingUrlGeneratorTest extends TestCase
{
    private const KEY = "aaaaaaaa-bbbb-cccc-dddd-eeeeee123456";

    private function asRequest(Request $request): void
    {
        $this->app->instance('request', $request);
    }

    public function testKeyIsCarriedWhenTheCookieIsMissing(): void
    {
        $this->asRequest(Request::create("/?key=" . self::KEY));

        $this->assertStringContainsString("key=" . self::KEY, route("account"));
        $this->assertStringContainsString("key=" . self::KEY, url("/about"));
    }

    public function testKeyIsAbsentWhenTheCookieIsPresent(): void
    {
        $request = Request::create("/?key=" . self::KEY);
        $request->cookies->set("key", self::KEY);
        $this->asRequest($request);

        $this->assertStringNotContainsString("key=", route("account"));
        $this->assertStringNotContainsString("key=", url("/about"));
    }

    /**
     * The webextension sends `key` as a header, never as a cookie. Its
     * requests must not have the key smeared into every link — that would
     * put it where it does not need to be for a client that already sends it
     * on every request of its own accord (see KeyAuthGuard's docblock on the
     * SafeBrowse link for the same reasoning about query vs. header).
     */
    public function testKeyIsNotCarriedForAHeaderOnlyRequest(): void
    {
        $request = Request::create("/");
        $request->headers->set("key", self::KEY);
        $this->asRequest($request);

        $this->assertStringNotContainsString("key=", route("account"));
    }

    /**
     * An explicit route parameter always wins over the carried key — this
     * only ever fills in what a caller did not already say.
     */
    public function testAnExplicitRouteParameterIsNotOverwritten(): void
    {
        $this->asRequest(Request::create("/?key=" . self::KEY));

        $this->assertStringContainsString(
            "key=different",
            route("account", ["key" => "different"])
        );
    }

    /**
     * `URL::signedRoute()` calls `route()` internally, twice — once to
     * compute the signature, once for the final URL. If either call carried
     * the key, it would end up baked into the signed payload itself and
     * travel wherever that signed URL goes (an email, a shared link) long
     * after this visit is over, and `hasValidSignature()` would start
     * silently depending on the key being present and unchanged.
     */
    public function testASignedUrlNeverContainsTheKey(): void
    {
        $this->asRequest(Request::create("/?key=" . self::KEY));

        $signed = URL::signedRoute("thankyou", [
            "amount" => 5,
            "interval" => "once",
            "funding_source" => "banktransfer",
            "timestamp" => time(),
        ]);

        $this->assertStringNotContainsString(self::KEY, $signed);
        $this->assertStringNotContainsString("key=", $signed);
    }

    /**
     * External URLs must never receive the key — `url()`'s host check exists
     * for exactly this. Asset generation ({@see \Illuminate\Support\Facades\Vite})
     * does not go through `to()`/`route()` at all and is unaffected either
     * way, matching the existing exemption for the locale prefix.
     */
    public function testAnExternalUrlIsLeftUntouched(): void
    {
        $this->asRequest(Request::create("/?key=" . self::KEY));

        $this->assertSame("https://example.com/foo", url("https://example.com/foo"));
    }
}
