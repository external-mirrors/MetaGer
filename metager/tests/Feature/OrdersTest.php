<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Bestellungen und Rechnungen — /konto/bestellungen,
 * App\Http\Controllers\OrderController.
 *
 * Aus dem `/key/<uuid>/orders`-Bereich des Keymanagers hierher gezogen. Wie
 * {@see ChargeReturnedTest} steht und fällt jede Seite mit der
 * Zugehörigkeitsprüfung: die öffentliche Nummer ist klein und fortlaufend,
 * eine fremde Bestellung zeigt hier nichts.
 */
class OrdersTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";
    private const OTHER_KEY = "aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee";

    private function signedIn(): self
    {
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    /**
     * @param array<string, \Illuminate\Http\Client\Response> $extra
     */
    private function keyserver(array $extra = []): void
    {
        Http::preventStrayRequests();
        Http::fake(array_merge($extra, [
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => 248,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => 248, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]));
    }

    private function order(array $overrides = []): array
    {
        return array_merge([
            "public_id" => "Z1",
            "amount" => 1000,
            "price" => "10.00",
            "expires_at" => "2027-05-14T00:00:00.000Z",
            "created_at" => "2026-05-14T09:30:00.000Z",
            "key" => self::A_KEY,
            "paid" => true,
            "payments" => [[
                "public_id" => "A1",
                "net" => "9.35",
                "vat" => "0.65",
                "gross" => "10.00",
                "vat_rate" => 7,
                "token_count" => 1000,
                "converted_price" => null,
                "converted_currency" => null,
                "payment_processor" => "Paypal",
                "created_at" => "2026-05-14T09:30:00.000Z",
                "invoice_available" => false,
            ]],
        ], $overrides);
    }

    public function testTheLookupFormRenders(): void
    {
        $this->keyserver();

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen")
            ->assertOk()
            ->assertSee(trans("orders.lookup.heading"))
            ->assertSee('name="reference"', false);
    }

    public function testAVisitorWithoutAKeyIsSentToSignIn(): void
    {
        $this->keyserver();

        $this->get("/de-DE/konto/bestellungen")->assertRedirect();
    }

    public function testAnArrayReferenceQueryDoesNotBreakTheForm(): void
    {
        $this->keyserver();

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen?reference[]=1")
            ->assertOk()
            ->assertSee(trans("orders.lookup.heading"));
    }

    public function testAValidReferenceRedirectsToItsDetailPage(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/bestellungen", ["reference" => "Z1"])
            ->assertRedirect(route("account.orders.show", ["reference" => "Z1"]));
    }

    public function testAMalformedReferenceIsRejectedInTheForm(): void
    {
        $this->keyserver();

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/bestellungen", ["reference" => "not-a-reference"])
            ->assertOk()
            ->assertSee(trans("orders.lookup.error.invalid"));
    }

    public function testAnUnknownReferenceSaysSoWithoutLeaking(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response(["code" => 404, "error" => "not_found"], 404),
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/bestellungen", ["reference" => "Z999999"])
            ->assertOk()
            ->assertSee(trans("orders.lookup.error.not_found"));
    }

    public function testAReferenceBelongingToAnotherKeyIsNotFound(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY])),
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/bestellungen", ["reference" => "Z1"])
            ->assertOk()
            ->assertSee(trans("orders.lookup.error.not_found"));
    }

    public function testAForeignOriginIsRejected(): void
    {
        $this->keyserver();

        $this->signedIn()
            ->withHeader("Origin", "https://evil.example")
            ->post("/de-DE/konto/bestellungen", ["reference" => "Z1"])
            ->assertForbidden();
    }

    public function testTheDetailPageShowsTheLineItems(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1")
            ->assertOk()
            ->assertSee("A1")
            ->assertSee("9.35 €")
            ->assertSee("10.00 €")
            ->assertSee(trans("orders.show.thanks"))
            ->assertSee(route("account.orders.confirmation", ["reference" => "Z1"]), false)
            ->assertHeader("Cache-Control", "no-store, private");
    }

    public function testTheDetailPageShowsPendingWhenNothingIsBookedYet(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["paid" => false, "payments" => []])),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1")
            ->assertOk()
            ->assertSee(trans("orders.show.pending"))
            ->assertDontSee(trans("orders.show.download_confirmation"));
    }

    public function testADetailPageForAnotherKeysOrderIs404(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY])),
        ]);

        $this->signedIn()->get("/de-DE/konto/bestellungen/Z1")->assertNotFound();
    }

    public function testTheConfirmationPdfIsProxiedThrough(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/confirmation.pdf*" => Http::response(
                "%PDF-1.7 ...",
                200,
                ["Content-Type" => "application/pdf"],
            ),
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $response = $this->signedIn()->get("/de-DE/konto/bestellungen/Z1/auftragsbestaetigung.pdf");

        $response->assertOk();
        $this->assertSame("application/pdf", $response->headers->get("Content-Type"));
        $this->assertStringStartsWith("%PDF-", $response->getContent());
        $response->assertHeader("Content-Disposition", 'inline; filename="Z1.pdf"');

        // Der Keyserver macht die Sprachverhandlung über ?lang= — kommt dort
        // kein brauchbares Gebietsschema an, fällt er am Ende auf den Host
        // zurück, und eine Server-zu-Server-Anfrage hat keinen. Der deutsche
        // Nutzer bekäme ein englisches PDF, ohne dass etwas rot wird.
        Http::assertSent(fn ($request) => str_contains($request->url(), "confirmation.pdf")
            && str_contains($request->url(), "lang=de-DE"));
    }

    public function testTheConfirmationPdfForAnotherKeysOrderIs404(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/confirmation.pdf*" => Http::response("%PDF-1.7", 200, ["Content-Type" => "application/pdf"]),
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY])),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1/auftragsbestaetigung.pdf")
            ->assertNotFound();
    }

    public function testTheConfirmationPdfIs404WhenTheKeyserverHasNoPdf(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/confirmation.pdf*" => Http::response(["code" => 404, "error" => "not_found"], 404),
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1/auftragsbestaetigung.pdf")
            ->assertNotFound();
    }
}
