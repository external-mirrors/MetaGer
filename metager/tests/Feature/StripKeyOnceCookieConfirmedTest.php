<?php

namespace Tests\Feature;

use App\Authentication\CookieSupport;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * `App\Http\Middleware\StripKeyOnceCookieConfirmed` — the clean half of the
 * one-hop key check that `CookieSupport::withKeyCheck()` sets up.
 *
 * `CookieBlindNoticeTest` covers the notice this middleware sits in front
 * of; `AccountAppCallbackTest` covers the app-handback flow this middleware
 * must stay out of. This file is about the middleware's own decision: bounce
 * once more when the cookie is confirmed, leave everything else untouched.
 */
class StripKeyOnceCookieConfirmedTest extends TestCase
{
    private const KEY = "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456";

    private function keyserverKnows(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*/logincode" => Http::response(["key" => self::KEY, "code" => "123456"]),
            "*/api/json/key/*" => Http::response([
                "key" => self::KEY,
                "charge" => 42.0,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => 42.0, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]);
    }

    #[Test]
    public function it_bounces_to_the_same_page_without_key_or_the_marker_once_the_cookie_is_confirmed(): void
    {
        $this->keyserverKnows();

        $response = $this->withCookie("key", self::KEY)
            ->get("/?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1&dark_mode=dark")
            ->assertRedirect();

        $location = $response->headers->get("Location");
        $this->assertStringNotContainsString("key=", $location);
        $this->assertStringNotContainsString(CookieSupport::MARKER, $location);
        // An unrelated query parameter survives the bounce.
        $this->assertStringContainsString("dark_mode=dark", $location);
    }

    #[Test]
    public function the_locale_prefix_survives_the_bounce(): void
    {
        $this->keyserverKnows();

        $response = $this->withCookie("key", self::KEY)
            ->get("/de-DE/konto?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1")
            ->assertRedirect();

        $this->assertStringContainsString("/de-DE/konto", $response->headers->get("Location"));
    }

    #[Test]
    public function it_does_nothing_when_the_cookie_has_not_arrived(): void
    {
        $this->keyserverKnows();

        // No withCookie() at all: the exact shape of a genuinely cookie-blind
        // landing. CookieBlindNoticeTest pins what renders here; this test is
        // only about this middleware staying out of the way.
        $this->get("/?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1")->assertOk();
    }

    #[Test]
    public function it_does_nothing_without_the_marker_even_with_a_matching_cookie(): void
    {
        $this->keyserverKnows();

        // A shared or bookmarked ?key=... link on a browser that accepts
        // cookies fine — CookieSupport's own docblock names this exact case.
        // Nothing here just authenticated, so nothing should bounce.
        $this->withCookie("key", self::KEY)->get("/?key=" . self::KEY)->assertOk();
    }

    /**
     * `AccountController::show()`'s app-handback branch needs the actual key
     * value to build the verified App Link the key travels back on — it must
     * run whether or not the cookie survived. Without the `isHandback()`
     * exclusion, this middleware would bounce the request to a key-stripped
     * `/konto` before that branch ever saw it, and the app would never get
     * its key back.
     */
    #[Test]
    public function it_leaves_an_app_handback_alone_even_with_a_matching_cookie(): void
    {
        $this->keyserverKnows();

        $response = $this->withCookie("key", self::KEY)
            ->get("/konto?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1&keystore=release&variant=playstore");

        $location = $response->headers->get("Location");
        $this->assertStringContainsString("/app/callback/playstore", $location);
        $this->assertStringContainsString("key=" . self::KEY, $location);
    }
}
