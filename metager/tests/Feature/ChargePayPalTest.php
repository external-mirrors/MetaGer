<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * PayPal — /konto/aufladen/<menge>/paypal.
 *
 * Anders als jede andere Zahlart in diesem Vorgang: die Seite selbst ist
 * SDK-getrieben, kein einfaches POST-Formular. `paypalServiceShow` spricht
 * schon beim Rendern zum Keyserver (App\Authentication\PayPalChargeIssuer::
 * show(), Gegenstück zu pass/routes/api.js's GET .../checkout/paypal/
 * :funding_source), und `paypalOrderCreate`/`paypalOrderCapture` sind JSON-
 * Ziele, die resources/js/checkout-paypal.js per fetch aufruft — nicht ein
 * Formular-Submit wie bei cash/micropayment/vrpayment.
 */
class ChargePayPalTest extends TestCase
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

    private function postToCheckout(string $path, array $fields, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->signedIn()
            ->withHeaders(array_merge(["Origin" => config("app.url")], $headers))
            ->post($path, $fields);
    }

    /**
     * Es gibt keine eigene PayPal-Wahl-Seite mehr — die sieben Zahlweisen
     * sind Kacheln auf dem allgemeinen Zahlungsarten-Chooser, wie jede andere
     * Zahlweise auch. ChargePageTest::testEveryPaypalTileIsPresentButHidden
     * InMarkup prüft dasselbe Markup zusätzlich in der allgemeinen Chooser-
     * Testklasse; hier steht es noch einmal, spezifisch für PayPal.
     */
    public function testTheChooserLinksToAllSevenFundingSources(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk();

        foreach (["paypal", "card", "p24", "bancontact", "blik", "eps", "mybank"] as $fundingSource) {
            $response->assertSee(
                route("account.checkout.paypal.service", ["amount" => 1000, "fundingSource" => $fundingSource]),
                false
            );
        }
    }

    public function testTheServicePageRendersWithTheKeyserverConfig(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/paypal" => Http::response([
                "client_id" => "test-client-id",
                "direct_card_enabled" => false,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/paypal/paypal")
            ->assertOk()
            ->assertSee("test-client-id", false)
            ->assertHeader("Content-Security-Policy");
    }

    public function testTheServicePageIssuesAFreshNonceEachRequest(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/paypal" => Http::response([
                "client_id" => "test-client-id",
                "direct_card_enabled" => false,
            ]),
        ]);

        $first = $this->signedIn()->get("/de-DE/konto/aufladen/1000/paypal/paypal")->headers->get("Content-Security-Policy");
        $second = $this->signedIn()->get("/de-DE/konto/aufladen/1000/paypal/paypal")->headers->get("Content-Security-Policy");

        $this->assertNotSame($first, $second, "jede Anfrage muss ihre eigene Nonce bekommen");
    }

    /**
     * Regression test: resources/js/checkout-paypal.js only calls
     * loadCardPayment() — which renders into #checkout-paypal-card-container
     * — when `fundingSource === "card" && directCardEnabled`. Everywhere
     * else, including `fundingSource === "card"` with direct card mode
     * switched off, it renders PaymentFields/Buttons into
     * #checkout-paypal-payment-fields/#checkout-paypal-payment-button
     * instead. The blade used to branch on `$fundingSource === 'card'`
     * alone, so a "card" page with direct card mode off shipped the
     * Advanced-Card-Fields markup while the JS looked for the
     * PaymentFields/Buttons containers that were never rendered —
     * `paypal.PaymentFields(...).render("#checkout-paypal-payment-fields")`
     * then throws because its target selector matches nothing.
     */
    public function testTheCardPageRendersThePaymentFieldsContainerWhenDirectCardModeIsOff(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/card" => Http::response([
                "client_id" => "test-client-id",
                "direct_card_enabled" => false,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/paypal/card")
            ->assertOk()
            ->assertSee('id="checkout-paypal-payment-fields"', false)
            ->assertSee('id="checkout-paypal-payment-button"', false)
            ->assertDontSee('id="checkout-paypal-card-container"', false);
    }

    public function testTheCardPageRendersTheCardContainerWhenDirectCardModeIsOn(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/card" => Http::response([
                "client_id" => "test-client-id",
                "direct_card_enabled" => true,
                "client_token" => "test-client-token",
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/paypal/card")
            ->assertOk()
            ->assertSee('id="checkout-paypal-card-container"', false)
            ->assertDontSee('id="checkout-paypal-payment-fields"', false);
    }

    public function testAnUnknownFundingSourceBouncesBackToTheChooser(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/paypal/not-a-funding-source")
            ->assertNotFound();
    }

    public function testAnUnreachableKeyserverBouncesTheServicePageBackWithAnError(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/paypal" => Http::response(null, 500),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/paypal/paypal")
            ->assertRedirect(route("account.checkout", ["amount" => 1000]) . "?error=unreachable");
    }

    public function testOrderCreateCreatesAnOrderAndReturnsThePaypalOrderId(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/paypal/order/create" => Http::response([
                "public_id" => "Z1",
                "paypal_order_id" => "PAYPAL-ORDER-1",
            ], 201),
        ]);

        $this->postToCheckout("/de-DE/konto/aufladen/1000/paypal/paypal/order/create", [])
            ->assertCreated()
            ->assertJson(["payment_reference" => "Z1", "paypal_order_id" => "PAYPAL-ORDER-1"]);
    }

    /**
     * The browser-facing origin travels to the keymanager as `return_origin`
     * on order create, so the keymanager can store it on the order and build
     * PayPal's capture-response redirect (and its async webhook redirect) from
     * it — see App\Support\AppHosts.
     */
    public function testOrderCreateForwardsTheBrowserOriginAsReturnOrigin(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/paypal/order/create" => Http::response([
                "public_id" => "Z1",
                "paypal_order_id" => "PAYPAL-ORDER-1",
            ], 201),
        ]);

        $this->signedIn()
            ->withHeaders(["Origin" => "https://metager.org"])
            ->post("https://metager.org/de-DE/konto/aufladen/1000/paypal/paypal/order/create", []);

        Http::assertSent(fn ($request) => str_contains($request->url(), "/checkout/paypal/paypal/order/create")
            && $request["return_origin"] === "https://metager.org");
    }

    public function testOrderCreateRefusesAForeignOrigin(): void
    {
        $this->keyserverKnows();

        $this->postToCheckout("/de-DE/konto/aufladen/1000/paypal/paypal/order/create", [], ["Origin" => "https://evil.example"])
            ->assertForbidden();
    }

    public function testOrderCreateRelaysAnUnreachableKeyserverAsAJsonError(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/paypal/order/create" => Http::response(null, 500),
        ]);

        $this->postToCheckout("/de-DE/konto/aufladen/1000/paypal/paypal/order/create", [])
            ->assertStatus(502);
    }

    public function testOrderCaptureRelaysTheKeyserversResponseVerbatim(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/paypal/order/capture" => Http::response([
                "redirect_url" => "https://metager.de/de-DE/konto/aufladen/abschluss/Z1",
            ], 200),
        ]);

        $this->postToCheckout("/de-DE/konto/aufladen/1000/paypal/paypal/order/capture", ["payment_reference" => "Z1"])
            ->assertOk()
            ->assertJson(["redirect_url" => "https://metager.de/de-DE/konto/aufladen/abschluss/Z1"]);
    }

    public function testOrderCaptureRelaysAThreeDSecureFailureVerbatim(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/paypal/card/order/capture" => Http::response([
                "errors" => [["type" => "PAYPAL_CARD_3D_ERROR", "msg" => "3D Verification Failed"]],
            ], 400),
        ]);

        $this->postToCheckout("/de-DE/konto/aufladen/1000/paypal/card/order/capture", ["payment_reference" => "Z1"])
            ->assertStatus(400)
            ->assertJson(["errors" => [["type" => "PAYPAL_CARD_3D_ERROR", "msg" => "3D Verification Failed"]]]);
    }

    public function testOrderCaptureRejectsAMalformedPaymentReference(): void
    {
        $this->keyserverKnows();

        $this->postToCheckout("/de-DE/konto/aufladen/1000/paypal/paypal/order/capture", ["payment_reference" => "not-a-reference"])
            ->assertStatus(400);
    }

    public function testOrderCaptureRefusesAForeignOrigin(): void
    {
        $this->keyserverKnows();

        $this->postToCheckout(
            "/de-DE/konto/aufladen/1000/paypal/paypal/order/capture",
            ["payment_reference" => "Z1"],
            ["Origin" => "https://evil.example"]
        )->assertForbidden();
    }

    public function testAVisitorWithoutAKeyIsSentToSignInOnTheServicePage(): void
    {
        $this->keyserverKnows();

        $this->get("/de-DE/konto/aufladen/1000/paypal/paypal")
            ->assertRedirect();
    }
}
