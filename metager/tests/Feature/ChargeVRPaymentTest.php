<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * VR Payment (Wero) — /konto/aufladen/<menge>/vrpayment.
 *
 * Architektonisch identisch zu ChargeMicropaymentTest: `vrpaymentSubmit`
 * legt die Ladung an (App\Authentication\VRPaymentChargeIssuer, dessen
 * Gegenstück pass/routes/api.js's /key/:key/checkout/vrpayment/wero-
 * Endpunkt) und leitet dann auf eine fremde, bereits mit dem Anbieter
 * abgestimmte Adresse weiter. Nur eine Zahlart — keine Wahl-Seite davor wie
 * bei micropayment.
 */
class ChargeVRPaymentTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

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

    private function signedIn(): self
    {
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    private function submit(array $fields, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->signedIn()
            ->withHeaders(array_merge(["Origin" => config("app.url")], $headers))
            ->post("/de-DE/konto/aufladen/1000/vrpayment", $fields);
    }

    public function testTheChooserLinksToVRPayment(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertSee(route("account.checkout.vrpayment", ["amount" => 1000]), false);
    }

    public function testTheFormPostsToItself(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/vrpayment")
            ->assertOk()
            ->assertSee(route("account.checkout.vrpayment.submit", ["amount" => 1000]), false);
    }

    /**
     * Wie bei micropayment (siehe ChargeMicropaymentTest): die Seite trägt
     * eine eigene CSP, deren `form-action` den VR-Payment-Host zulässt, sonst
     * blockieren Chrome/Safari die 303-Weiterleitung des Formulars auf die
     * Zahlungsseite. Geprüft wird der Controller-Vertrag, nicht der Browser.
     *
     * Der Host `*.vr-payment.de` ist abgeleitet, nicht beobachtet: das
     * vrpayment-SDK spricht `gateway.vr-payment.de` an und die vorhandenen
     * Tests hier setzen `checkout.vr-payment.de` als Zahlungsseite voraus.
     * Sollte Wero trotz dieses Fixes hängen bleiben, ist der echte Host der
     * erste Prüfpunkt.
     */
    public function testThePageWidensFormActionForTheRedirectToVRPayment(): void
    {
        $this->keyserverKnows();

        $csp = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/vrpayment")
            ->assertOk()
            ->headers->get("Content-Security-Policy");

        $this->assertNotNull($csp);
        $this->assertMatchesRegularExpression('/form-action[^;]*\bvr-payment\.de\b/', $csp);
        $this->assertStringContainsString("worker-src 'self' blob:", $csp);
        $this->assertStringContainsString("scripts.zdv.uni-mainz.de", $csp);
    }

    public function testSubmittingWithoutConsentIsRefused(): void
    {
        $this->keyserverKnows();

        $this->submit([])
            ->assertRedirect(route("account.checkout.vrpayment", ["amount" => 1000]) . "?error=consent");

        Http::assertNotSent(fn ($request) => str_contains($request->url(), "/checkout/vrpayment"));
    }

    public function testConsentingCreatesTheOrderAndLeavesForTheProvider(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/vrpayment/*" => Http::response([
                "public_id" => "Z1",
                "redirect_url" => "https://checkout.vr-payment.de/s/95151/payment/page/abc123",
            ], 201),
        ]);

        $this->submit(["revocation" => "on"])
            ->assertRedirect("https://checkout.vr-payment.de/s/95151/payment/page/abc123");
    }

    /**
     * The browser-facing origin travels to the keymanager as `return_origin`.
     * The same deployment answers on metager.de, metager.org, metager3.de and
     * the review previews; the keymanager only sees the host it was called on
     * server-to-server (KEY_SERVER), which is not the user's — so without this
     * a payment started from metager.org comes back to whatever KEY_SERVER
     * names. See App\Support\AppHosts.
     */
    public function testTheBrowserOriginIsForwardedAsReturnOrigin(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/vrpayment/*" => Http::response([
                "public_id" => "Z1",
                "redirect_url" => "https://checkout.vr-payment.de/s/95151/payment/page/abc123",
            ], 201),
        ]);

        $this->signedIn()
            ->withHeaders(["Origin" => "https://metager.org"])
            ->post("https://metager.org/de-DE/konto/aufladen/1000/vrpayment", ["revocation" => "on"]);

        Http::assertSent(fn ($request) => str_contains($request->url(), "/checkout/vrpayment/")
            && $request["return_origin"] === "https://metager.org");
    }

    public function testAnUnreachableKeyserverBouncesBackWithAnError(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/vrpayment/*" => Http::response(null, 500),
        ]);

        $this->submit(["revocation" => "on"])
            ->assertRedirect(route("account.checkout.vrpayment", ["amount" => 1000]) . "?error=unreachable");
    }

    public function testAReturnFromTheProviderWithAFailureShowsIt(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/vrpayment?error=vrpayment_failed")
            ->assertOk()
            ->assertSee(trans("checkout.vrpayment.error.failed"));
    }

    public function testAForeignFormIsRefused(): void
    {
        $this->keyserverKnows();

        $this->submit(["revocation" => "on"], ["Origin" => "https://evil.example"])
            ->assertForbidden();
    }
}
