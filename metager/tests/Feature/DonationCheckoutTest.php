<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * `DonationController::checkout()` — the donation form only settles amount
 * and interval, then hands off to suma-crm's `POST /api/donations` via
 * `App\Donations\DonationCheckoutIssuer`: suma-payments' own picker asks for
 * the method (bank transfer included) and an optional email.
 */
class DonationCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            "metager.metager.crm.url" => "https://crm.example.com",
            "metager.metager.crm.internal_url" => "https://crm.example.com",
            "metager.metager.crm.token" => "secret-token",
        ]);
    }

    private function fakeCrm(string $checkoutUrl = "https://payments.example.com/checkout/abc"): void
    {
        Http::fake([
            "https://crm.example.com/api/donations" => Http::response(["checkout_url" => $checkoutUrl], 201),
        ]);
    }

    public function testTheIntervalStepOffersNoPaymentMethodsAndAsksForNothing(): void
    {
        $this->get("/spende/10", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSee('name="interval" value="monthly"', false)
            ->assertDontSee('name="name"', false)
            ->assertDontSee('name="email"', false);
    }

    public function testTheOldPaymentMethodStepLeadsBackToTheIntervalStep(): void
    {
        $this->get("/spende/10/monthly", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect("/spende/10");
    }

    public function testAnInvalidIntervalStaysOnTheIntervalStep(): void
    {
        $this->fakeCrm();

        $this->post("/spende/10", ["interval" => "weekly"])->assertRedirect("/spende/10");
        Http::assertNothingSent();
    }

    public function testAOneOffDonationIsHandedOffWithAmountAndIntervalOnly(): void
    {
        $this->fakeCrm();

        $this->post("/spende/10", ["interval" => "once"])
            ->assertRedirect("https://payments.example.com/checkout/abc");

        Http::assertSent(fn ($request) => $request->url() === "https://crm.example.com/api/donations"
            && $request["amount"] === 10.0
            && $request["recurring"] === false
            && $request["frequency"] === null
            && str_ends_with($request["cancel_url"], "/spende/10")
            && !isset($request["method"], $request["name"], $request["email"]));
    }

    public function testARecurringDonationCarriesItsFrequency(): void
    {
        $this->fakeCrm();

        $this->post("/spende/10", ["interval" => "monthly"])
            ->assertRedirect("https://payments.example.com/checkout/abc");

        Http::assertSent(fn ($request) => $request["recurring"] === true && $request["frequency"] === "monthly");
    }

    public function testTheDonorStaysOnTheIntervalStepWhenTheCrmIsUnreachable(): void
    {
        Http::fake(["https://crm.example.com/api/donations" => Http::response(null, 500)]);

        $response = $this->post("/spende/10", ["interval" => "monthly"]);

        $response->assertOk();
        $this->assertTrue($response->viewData("errors")->has("crm"));
    }

    public function testTheThankYouPageAcceptsTheMethodSumaPaymentsAppends(): void
    {
        $url = URL::signedRoute("thankyou", ["amount" => 10, "interval" => "once", "timestamp" => 1]);

        $this->get($url . "&payment_method=sepa_directdebit", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSee(__("spende.payment-method.methods.directdebit"));
    }

    public function testTheThankYouPageNamesAOneOffWeroPayment(): void
    {
        $url = URL::signedRoute("thankyou", ["amount" => 10, "interval" => "once", "timestamp" => 1]);

        $this->get($url . "&payment_method=wero", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSee(__("spende.payment-method.methods.wero_link"));
    }

    public function testTheThankYouPageRejectsAnUnsignedLink(): void
    {
        $this->get("/spende/10/once/1/finished", ["Sec-Fetch-Mode" => "navigate"])
            ->assertNotFound();
    }
}
