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

    public function testTheInvoiceFormRenders(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1/rechnung")
            ->assertOk()
            ->assertSee(trans("orders.invoice.heading"))
            ->assertSee('name="first_name"', false);
    }

    public function testTheInvoiceFormIsGoneOnceAnOrderHasNoPayments(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["paid" => false, "payments" => []])),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1/rechnung")
            ->assertNotFound();
    }

    public function testTheInvoiceFormOffersADownloadLinkOnceOneAlreadyExists(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order([
                "payments" => [array_merge($this->order()["payments"][0], ["invoice_available" => true])],
            ])),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1/rechnung")
            ->assertOk()
            ->assertSee(trans("orders.invoice.ready"))
            ->assertSee(route("account.orders.invoice.pdf", ["reference" => "Z1"]), false)
            ->assertDontSee('name="first_name"', false);
    }

    public function testAnInvoiceFormForAnotherKeysOrderIs404(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY])),
        ]);

        $this->signedIn()->get("/de-DE/konto/bestellungen/Z1/rechnung")->assertNotFound();
    }

    public function testSubmittingTheInvoiceFormForwardsItAndRedirectsOnSuccess(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/invoice" => Http::response(["receipt_public_id" => "R1"], 201),
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $response = $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/bestellungen/Z1/rechnung", [
                "first_name" => "Max",
                "last_name" => "Mustermann",
                "address1" => "Hauptstraße 1",
                "zip" => "12345",
                "city" => "Musterstadt",
            ]);

        $response->assertRedirect(route("account.orders.invoice", ["reference" => "Z1"]));

        Http::assertSent(fn ($request) => str_contains($request->url(), "/invoice")
            && !str_contains($request->url(), "invoice.pdf")
            && $request["first_name"] === "Max"
            && $request["city"] === "Musterstadt");
    }

    public function testSubmittingTheInvoiceFormShowsKeyserverValidationErrors(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/invoice" => Http::response([
                "code" => 422,
                "errors" => [["path" => "last_name", "msg" => "Invalid value"]],
            ], 422),
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/bestellungen/Z1/rechnung", [
                "first_name" => "Max",
                "last_name" => "",
                "address1" => "Hauptstraße 1",
                "zip" => "12345",
                "city" => "Musterstadt",
            ])
            ->assertOk()
            ->assertSee(trans("orders.invoice.error.invalid"));
    }

    public function testSubmittingTheInvoiceFormForAnotherKeysOrderIs404(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY])),
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/bestellungen/Z1/rechnung", ["first_name" => "Max"])
            ->assertNotFound();
    }

    public function testSubmittingTheInvoiceFormWithAForeignOriginIsRejected(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()
            ->withHeader("Origin", "https://evil.example")
            ->post("/de-DE/konto/bestellungen/Z1/rechnung", ["first_name" => "Max"])
            ->assertForbidden();
    }

    public function testTheInvoicePdfIsProxiedThrough(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/invoice.pdf*" => Http::response(
                "%PDF-1.7 invoice",
                200,
                ["Content-Type" => "application/pdf"],
            ),
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $response = $this->signedIn()->get("/de-DE/konto/bestellungen/Z1/rechnung.pdf");

        $response->assertOk();
        $this->assertSame("application/pdf", $response->headers->get("Content-Type"));
        $this->assertStringStartsWith("%PDF-", $response->getContent());
        $response->assertHeader("Content-Disposition", 'inline; filename="Z1-rechnung.pdf"');
    }

    public function testTheInvoicePdfForAnotherKeysOrderIs404(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/invoice.pdf*" => Http::response("%PDF-1.7", 200, ["Content-Type" => "application/pdf"]),
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY])),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1/rechnung.pdf")
            ->assertNotFound();
    }

    public function testTheInvoicePdfIs404WhenTheKeyserverHasNoPdfYet(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*/invoice.pdf*" => Http::response(["code" => 404, "error" => "not_found"], 404),
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1/rechnung.pdf")
            ->assertNotFound();
    }
}
