<?php

namespace Tests\Feature;

use App\Jobs\DonationNotification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * `DonationController::paypalPayment()`'s `card` branch and the new
 * `weroLink()`/`weroLinkExecute()` pair (cutover-plan.md C6 — MetaGer's own
 * side) — both hand straight off to suma-crm's `POST /api/donations` via
 * `App\Donations\DonationCheckoutIssuer`, the same way `paypal`/`directdebit`
 * already do. No local PayPal-SDK/3DS/client-token code is exercised for
 * `card` any more — VR Payment's own hosted Payment Page does that job now.
 *
 * `Bus::fake()` is not optional here: `DonationNotification::handle()` makes
 * a real `file_get_contents()` POST to the live Zammad ticket system
 * (`config('metager.metager.ticketsystem.*')`, sourced from this developer's
 * own `.env` — phpunit.xml overrides neither the URL nor the API key), and
 * `phpunit.xml`'s `QUEUE_CONNECTION=sync` runs a dispatched `ShouldQueue` job
 * inline, synchronously, in the test process itself. Without this fake, every
 * test below that reaches a successful checkout redirect fires a real ticket
 * article on the live instance.
 */
class DonationCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake([DonationNotification::class]);
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

    public function testAOneOffCardDonationRedirectsToTheCrmCheckoutUrl(): void
    {
        $this->fakeCrm();

        $this->get("/spende/10/once/paypal/card", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect("https://payments.example.com/checkout/abc");

        Http::assertSent(fn ($request) => $request->url() === "https://crm.example.com/api/donations"
            && $request["method"] === "card"
            && $request["amount"] === 10.0
            && $request["recurring"] === false);
        Bus::assertDispatched(DonationNotification::class);
    }

    public function testACardDonationBelowFiveIsRejected(): void
    {
        $this->fakeCrm();

        $this->get("/spende/3/once/paypal/card", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect();

        Http::assertNothingSent();
    }

    public function testARecurringCardDonationCarriesTheNameQueryParam(): void
    {
        $this->fakeCrm();

        $this->get("/spende/10/monthly/paypal/card?name=Ada+Lovelace", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect("https://payments.example.com/checkout/abc");

        Http::assertSent(fn ($request) => $request["method"] === "card"
            && $request["recurring"] === true
            && $request["frequency"] === "monthly"
            && $request["name"] === "Ada Lovelace");
    }

    public function testACardDonationShowsAnErrorWhenTheCrmIsUnreachable(): void
    {
        Http::fake(["https://crm.example.com/api/donations" => Http::response(null, 500)]);

        $this->get("/spende/10/once/paypal/card", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect("/spende/10/once")
            ->assertSessionHasErrors("crm");
        Bus::assertNotDispatched(DonationNotification::class);
    }

    public function testTheWeroLinkFormRendersForARecurringInterval(): void
    {
        $this->get("/spende/10/monthly/wero_link", ["Sec-Fetch-Mode" => "navigate"])->assertOk();
    }

    public function testTheWeroLinkFormRejectsAOneTimeInterval(): void
    {
        $this->get("/spende/10/once/wero_link", ["Sec-Fetch-Mode" => "navigate"])
            ->assertRedirect("/spende/10");
    }

    public function testSubmittingTheWeroLinkFormRequiresAnEmail(): void
    {
        $this->fakeCrm();

        // weroLinkExecute() re-renders the form with a bound $errors view
        // variable directly (mirroring directdebitExecute()'s own pattern),
        // not a redirect — so the errors live on the response's view data,
        // not the session.
        $response = $this->post("/spende/10/monthly/wero_link", ["name" => "Ada Lovelace"]);

        $response->assertOk();
        $this->assertTrue($response->viewData("errors")->has("email"));

        Http::assertNothingSent();
    }

    public function testSubmittingTheWeroLinkFormRedirectsToTheCrmCheckoutUrl(): void
    {
        $this->fakeCrm();

        $this->post("/spende/10/monthly/wero_link", [
            "name" => "Ada Lovelace",
            "email" => "ada@example.com",
        ])->assertRedirect("https://payments.example.com/checkout/abc");

        Http::assertSent(fn ($request) => $request["method"] === "wero_link"
            && $request["recurring"] === true
            && $request["frequency"] === "monthly"
            && $request["name"] === "Ada Lovelace"
            && $request["email"] === "ada@example.com");
        Bus::assertDispatched(DonationNotification::class);
    }

    public function testAWeroLinkDonationShowsAnErrorWhenTheCrmIsUnreachable(): void
    {
        Http::fake(["https://crm.example.com/api/donations" => Http::response(null, 500)]);

        $this->post("/spende/10/monthly/wero_link", [
            "name" => "Ada Lovelace",
            "email" => "ada@example.com",
        ])->assertRedirect("/spende/10/monthly/wero_link")
            ->assertSessionHasErrors("crm");
        Bus::assertNotDispatched(DonationNotification::class);
    }
}
