<?php

namespace Tests\Feature;

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

    private function submit(array $fields): \Illuminate\Testing\TestResponse
    {
        return $this->signedIn()
            ->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/konto/aufladen/1000/bezahlen", $fields);
    }

    public function testThePackageGoesStraightToTheConsentPage(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/de-DE/konto/aufladen/1000")
            ->assertRedirect(route("account.checkout.suma", ["amount" => 1000]));
    }

    public function testTheConsentPageHasTwoStepsAndNoMethodList(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")->assertOk()
            ->assertSee("Weiter zur Zahlung")
            ->assertDontSee("Andere Zahlungsart wählen")
            // No method step left: Menge → Bezahlen.
            ->assertSee("Aufladen in zwei Schritten")
            ->assertDontSee("Zahlungsart</span>", false);
    }

    /**
     * The notice used to read "…und, je nach Zahlungsart, über VR Payment oder
     * PayPal", which suggests every payment goes through one of the two. Bank
     * transfer, direct debit and cash are handled by SUMA-EV itself — for a
     * privacy-minded buyer that is the deciding fact, so the notice says it.
     */
    public function testThePrivacyNoticeSaysWhichMethodsInvolveNoThirdParty(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")->assertOk()
            ->assertSee("Überweisung, SEPA-Lastschrift und Bargeld per Post wickeln wir selbst ab")
            ->assertSee("nur bei Karte und Wero (VR Payment) sowie bei PayPal");
    }

    /**
     * The lede used to say "Anonym geht weiterhin" — "still", relative to the
     * checkout pages suma-payments replaced. A first-time buyer has nothing to
     * compare against, so it reads as a reference to something they missed.
     */
    public function testTheLedeDoesNotCompareWithThePreviousCheckout(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")->assertOk()
            ->assertSee("Sie können anonym bezahlen")
            ->assertDontSee("weiterhin");
    }

    public function testTheEnglishPrivacyNoticeSaysItToo(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/en-GB/konto/aufladen/1000/bezahlen")->assertOk()
            ->assertSee("We handle bank transfer, SEPA direct debit and cash by post ourselves");
    }

    public function testTheConsentPageAllowsTheRedirectToSumaPayments(): void
    {
        $this->keyserverKnows();

        $csp = $this->signedIn()->get("/de-DE/konto/aufladen/1000/bezahlen")
            ->assertOk()
            ->assertSee(route("account.checkout.suma.submit", ["amount" => 1000]), false)
            ->headers->get("Content-Security-Policy");

        $this->assertMatchesRegularExpression('/form-action[^;]*payments\.suma-ev\.de/', $csp);
    }

    public function testWithoutConsentNothingIsOrdered(): void
    {
        $this->keyserverKnows();

        $this->submit([])->assertRedirect(route("account.checkout.suma", ["amount" => 1000]) . "?error=consent");

        Http::assertNotSent(fn ($request) => str_contains($request->url(), "/checkout/suma-payments") && $request->method() === "POST");
    }

    public function testConsentingOpensTheOrderAndLeavesForThePicker(): void
    {
        $this->keyserverKnows([
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

    public function testAnUnknownPackageGoesBackToTheAccount(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/de-DE/konto/aufladen/999")->assertRedirect(route("account") . "#charge");
    }

    public function testAnUnreachableKeymanagerSaysSo(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/suma-payments" => Http::response(["code" => 502], 502),
        ]);

        $this->submit(["revocation" => "on"])
            ->assertRedirect(route("account.checkout.suma", ["amount" => 1000]) . "?error=unreachable");
    }
}
