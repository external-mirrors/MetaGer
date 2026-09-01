<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Wero (VR Payment) over the onion address — App\Http\Controllers\ChargeController.
 *
 * VR Payment's space rejects a `.onion` success/failure URL outright, so a
 * payment started there would leave MetaGer and never find its way back. That
 * is exactly the "silently dropping is worse" case: rather than let the
 * keymanager quietly omit the return URLs (which it does for localhost as a dev
 * convenience), Wero is simply not offered on an onion address, and every route
 * to it — the tile, a bookmarked checkout page, a hand-crafted POST — ends at
 * the payment-method chooser with `?error=wero_unavailable`.
 *
 * The v3 onion host is {@see \App\Support\AppHosts::ONION}; requests here carry
 * it so `ChargeController::weroAvailable()` sees it.
 */
class ChargeWeroOnionTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";
    private const ONION = "metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion";

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear("account-logincode:" . self::A_KEY);
    }

    private function keyserverKnows(array $extraFakes = []): void
    {
        Http::preventStrayRequests();
        Http::fake(array_merge($extraFakes, [
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => 248,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => 248, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]));
    }

    private function onion(string $path): \Illuminate\Testing\TestResponse
    {
        return $this->withUnencryptedCookie("key", self::A_KEY)
            ->withHeaders(["Sec-Fetch-Mode" => "navigate"])
            ->get("http://" . self::ONION . $path);
    }

    public function testTheChooserOmitsTheWeroTileOnTheOnionAddress(): void
    {
        $this->keyserverKnows();

        $this->onion("/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertDontSee(route("account.checkout.vrpayment", ["amount" => 1000]), false)
            // the other methods are still there
            ->assertSee(route("account.checkout.cash", ["amount" => 1000]), false);
    }

    public function testTheChooserStillShowsTheWeroTileOnANonOnionHost(): void
    {
        $this->keyserverKnows();

        $this->withUnencryptedCookie("key", self::A_KEY)
            ->withHeaders(["Sec-Fetch-Mode" => "navigate"])
            ->get("https://metager.de/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertSee(route("account.checkout.vrpayment", ["amount" => 1000]), false);
    }

    public function testTheWeroCheckoutPageBouncesToTheChooserOnTheOnionAddress(): void
    {
        $this->keyserverKnows();

        $this->onion("/de-DE/konto/aufladen/1000/vrpayment")
            ->assertRedirect(route("account.checkout", ["amount" => 1000]) . "?error=wero_unavailable");
    }

    public function testTheChooserShowsTheOnionExplanationForThatError(): void
    {
        $this->keyserverKnows();

        $this->onion("/de-DE/konto/aufladen/1000?error=wero_unavailable")
            ->assertOk()
            ->assertSee(trans("checkout.vrpayment.error.onion"));
    }

    public function testASubmitToWeroIsRefusedOnTheOnionAddress(): void
    {
        $this->keyserverKnows();

        $this->withUnencryptedCookie("key", self::A_KEY)
            ->withHeaders(["Origin" => "http://" . self::ONION])
            ->post("http://" . self::ONION . "/de-DE/konto/aufladen/1000/vrpayment", ["revocation" => "on"])
            ->assertRedirect(route("account.checkout", ["amount" => 1000]) . "?error=wero_unavailable");

        Http::assertNotSent(fn ($request) => str_contains($request->url(), "/checkout/vrpayment/"));
    }
}
