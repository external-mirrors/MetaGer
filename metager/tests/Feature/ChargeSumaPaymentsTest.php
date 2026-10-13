<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Token purchases through suma-payments' checkout —
 * /konto/aufladen/<menge>/bezahlen (App\Authentication\SumaPaymentsChargeIssuer;
 * suma-crm docs/keymanager-migration-plan.md, Step 3).
 *
 * The keymanager says which methods have moved. MetaGer offers those as one
 * tile and drops its own tile for each; once all have moved, its method
 * page gives way to the consent page straight away. Consent here, then the
 * keymanager opens the order and the browser goes to suma-payments' picker.
 */
class ChargeSumaPaymentsTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear("account-logincode:" . self::A_KEY);
        Cache::forget("keymanager:suma-payments-methods");
    }

    private function keyserverKnows(array $moved, array $extraFakes = []): void
    {
        Http::preventStrayRequests();
        Http::fake(array_merge($extraFakes, [
            "*/api/json/checkout/suma-payments/methods" => Http::response(["methods" => $moved]),
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

    private function submit(array $fields): \Illuminate\Testing\TestResponse
    {
        return $this->signedIn()
            ->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/konto/aufladen/1000/bezahlen", $fields);
    }

    public function testNothingMovedLeavesTheMethodPageAsItWas(): void
    {
        $this->keyserverKnows([]);

        $this->signedIn()->get("/de-DE/konto/aufladen/1000")->assertOk()
            ->assertDontSee(route("account.checkout.suma", ["amount" => 1000]), false)
            ->assertSee(route("account.checkout.cash", ["amount" => 1000]), false)
            ->assertSee(route("account.checkout.micropayment.service", ["amount" => 1000, "service" => "prepay"]), false);
    }

    public function testNothingMovedHasNoConsentPage(): void
    {
        $this->keyserverKnows([]);

        $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")->assertRedirect(route("account") . "#charge");
    }

    public function testMovedMethodsShareOneTileAndTheirOwnTilesGo(): void
    {
        $this->keyserverKnows(["banktransfer", "sepa_directdebit", "cash"]);

        $this->signedIn()->get("/de-DE/konto/aufladen/1000")->assertOk()
            ->assertSee(route("account.checkout.suma", ["amount" => 1000]), false)
            ->assertSee("Überweisung, SEPA-Lastschrift, Bargeld per Post")
            ->assertDontSee(route("account.checkout.cash", ["amount" => 1000]), false)
            ->assertDontSee(route("account.checkout.micropayment.service", ["amount" => 1000, "service" => "prepay"]), false)
            ->assertSee(route("account.checkout.vrpayment", ["amount" => 1000]), false)
            ->assertSee(route("account.checkout.paypal.service", ["amount" => 1000, "fundingSource" => "card"]), false);
    }

    public function testOnceEverythingMovedThePickerIsAllThereIs(): void
    {
        $this->keyserverKnows(["banktransfer", "sepa_directdebit", "card", "wero", "paypal", "cash"]);

        $this->signedIn()->get("/de-DE/konto/aufladen/1000")
            ->assertRedirect(route("account.checkout.suma", ["amount" => 1000]));
    }

    public function testOnceEverythingMovedTheConsentPageHasNoWayBackToAMethodList(): void
    {
        $this->keyserverKnows(["banktransfer", "sepa_directdebit", "card", "wero", "paypal", "cash"]);

        $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")->assertOk()
            ->assertSee("Weiter zur Zahlung")
            ->assertDontSee("Andere Zahlungsart wählen")
            // No method step left: Menge → Bezahlen.
            ->assertSee("Aufladen in zwei Schritten")
            ->assertDontSee("Zahlungsart</span>", false);
    }

    public function testTheConsentPageAllowsTheRedirectToSumaPayments(): void
    {
        $this->keyserverKnows(["banktransfer"]);

        $csp = $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")
            ->assertOk()
            ->assertSee(route("account.checkout.suma.submit", ["amount" => 1000]), false)
            ->headers->get("Content-Security-Policy");

        $this->assertMatchesRegularExpression('/form-action[^;]*payments\.suma-ev\.de/', $csp);
    }

    public function testWithoutConsentNothingIsOrdered(): void
    {
        $this->keyserverKnows(["banktransfer"]);

        $this->submit([])->assertRedirect(route("account.checkout.suma", ["amount" => 1000]) . "?error=consent");

        Http::assertNotSent(fn ($request) => str_contains($request->url(), "/checkout/suma-payments") && $request->method() === "POST");
    }

    public function testConsentingOpensTheOrderAndLeavesForThePicker(): void
    {
        $this->keyserverKnows(["banktransfer"], [
            "*/api/json/key/*/checkout/suma-payments" => Http::response([
                "public_id" => "Z11440472",
                "redirect_url" => "https://payments.suma-ev.de/checkout/abc",
            ], 201),
        ]);

        $this->submit(["revocation" => "on"])->assertRedirect("https://payments.suma-ev.de/checkout/abc");

        Http::assertSent(fn ($request) => str_ends_with($request->url(), "/key/" . self::A_KEY . "/checkout/suma-payments")
            && $request["amount"] === 1000
            && is_string($request["return_origin"] ?? null)
            && ($request["locale"] ?? null) !== null);
    }

    public function testAnUnreachableKeymanagerSaysSo(): void
    {
        $this->keyserverKnows(["banktransfer"], [
            "*/api/json/key/*/checkout/suma-payments" => Http::response(["code" => 502], 502),
        ]);

        $this->submit(["revocation" => "on"])
            ->assertRedirect(route("account.checkout.suma", ["amount" => 1000]) . "?error=unreachable");
    }

    public function testWhileSomeMethodsStayTheStepsKeepTheMethodStep(): void
    {
        $this->keyserverKnows(["banktransfer"]);

        $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")->assertOk()
            ->assertSee("Aufladen in drei Schritten")
            ->assertSee("Zahlungsart</span>", false);
    }
}
