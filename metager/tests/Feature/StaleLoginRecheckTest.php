<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The one-shot re-check a bfcached startpage makes, seen from the server.
 *
 * The back/forward cache is the one cache no header can reach: only
 * `Cache-Control: no-store` keeps a page out of it, and the startpage cannot
 * pay that — it would lose the validator that lets the busiest page in the
 * product revalidate for 304 bytes
 * ({@see \App\Http\Middleware\HttpCache::revalidatable()}). So a visitor who
 * signs in and presses Back gets a photograph of the logged-out page, with
 * accountBreadcrumb.js's "welcome back" already painted on it.
 *
 * resources/js/startpage/staleLoginCheck.js asks this endpoint once on
 * `pageshow` with `event.persisted`; its own behaviour is pinned in
 * staleLoginCheck.test.js. What is pinned here is the pair of things only the
 * server can promise: that the hero carries the endpoint at all, and that the
 * endpoint answers the same question the page asked when it rendered.
 */
class StaleLoginRecheckTest extends TestCase
{
    private const KEY = "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456";

    private function keyserverKnows(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/*" => Http::response(["key" => self::KEY, "charge" => 42.0]),
            "*" => Http::response(""),
        ]);
    }

    public function testTheLandingHeroCarriesTheEndpoint(): void
    {
        $this->get("/")->assertOk()->assertSee(
            'data-login-check="' . route("startpage:loggedin") . '"',
            false
        );
    }

    /**
     * Nothing to re-check on the signed-in render: a stale "you are signed in"
     * cannot lock anyone out, and the hook the script keys off is the hero,
     * which is not on that page at all.
     */
    public function testTheSignedInStartpageCarriesNoEndpoint(): void
    {
        $this->keyserverKnows();

        $this->withUnencryptedCookie("key", self::KEY)->get("/")
            ->assertOk()
            ->assertDontSee("data-login-check", false);
    }

    public function testAnAnonymousVisitorIsToldTheyAreStillAnonymous(): void
    {
        $this->post(route("startpage:loggedin"))->assertStatus(401);
    }

    public function testAKeyCookieAnswers200(): void
    {
        $this->keyserverKnows();

        $this->withUnencryptedCookie("key", self::KEY)
            ->post(route("startpage:loggedin"))
            ->assertOk();
    }

    /**
     * The regression for the predicate this endpoint used to use.
     *
     * It read only the legacy Authorization service, whose `parseKey()` sees no
     * key here — the webextension's anonymous token arrives as its own header
     * and is resolved by the guard, not by that service. So a visitor the
     * startpage renders the *search bar* for was told 401, and the re-check
     * would have confirmed a staleness that was not there. It now asks
     * `index.blade.php`'s `$signedIn`, both halves of it.
     */
    public function testAnAnonymousTokenVisitorAnswers200(): void
    {
        $this->keyserverKnows();

        $this->withHeader("anonymous-token-key", self::KEY)
            ->post(route("startpage:loggedin"))
            ->assertOk();
    }
}
