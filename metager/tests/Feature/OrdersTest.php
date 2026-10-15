<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Bestellungen und Erstattungen — /konto/bestellungen,
 * App\Http\Controllers\OrderController.
 *
 * Aus dem `/key/<uuid>/orders`-Bereich des Keymanagers hierher gezogen. Wie
 * {@see ChargeReturnedTest} steht und fällt jede Seite mit der
 * Zugehörigkeitsprüfung: die öffentliche Nummer ist klein und fortlaufend,
 * eine fremde Bestellung zeigt hier nichts.
 *
 * Die Erstattung bewegt kein Geld — der Keyserver öffnet nur ein
 * Zammad-Ticket und bucht das ungenutzte Guthaben zurück; ein 409 vom
 * Keyserver (schon angefragt) ist deshalb kein Fehler für den Aufrufer, ein
 * 403 (Zahlungsart unterstützt keine Erstattung) schon.
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
            // Lokalisiert (App\Support\Money): der Keyserver liefert "9.35",
            // die deutsche Seite zeigt "9,35 €" — vorher stand der englische
            // Dezimalpunkt in derselben Tabelle wie das lokalisierte
            // "1.000 Token".
            // Das Leerzeichen vor dem € ist ein geschütztes (U+00A0) — so
            // setzt es der Intl-Formatter im Deutschen, und so soll es auch
            // in der Tabelle stehen, damit die Währung nie allein umbricht.
            ->assertSee("9,35\u{00A0}€")
            ->assertSee("10,00\u{00A0}€")
            ->assertSee("1.000")
            ->assertSee(trans("orders.show.thanks"))
            ->assertDontSee(route("account.orders.confirmation", ["reference" => "Z1"]), false)
            ->assertDontSee(route("account.orders.invoice", ["reference" => "Z1"]), false)
            // Eine Erstattung wird auf der Seite der Bestellung im CRM beantragt.
            ->assertDontSee(route("account.orders.refund", ["reference" => "Z1"]), false)
            ->assertHeader("Cache-Control", "no-store, private");
    }

    public function testTheDetailPageLeadsToTheOrdersPageInTheCrmOnceTheKeyserverLinksIt(): void
    {
        $crm = "https://my.suma-ev.de/bestellung/Z1/1.60MEKpEs0PvRka04hcSrjOxF7y52S6K5";
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["crm_url" => $crm])),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/bestellungen/Z1")
            ->assertOk()
            ->assertSee("Beleg und Rechnung")
            ->assertSee($crm . "?lang=de-DE", false)
            ->assertDontSee(route("account.orders.invoice", ["reference" => "Z1"]), false)
            ->assertDontSee(route("account.orders.confirmation", ["reference" => "Z1"]), false);
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
            ->assertDontSee(trans("orders.show.documents"));
    }

    public function testADetailPageForAnotherKeysOrderIs404(): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY])),
        ]);

        $this->signedIn()->get("/de-DE/konto/bestellungen/Z1")->assertNotFound();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function oldDocumentAddresses(): array
    {
        return [
            "Auftragsbestätigung" => ["get", "/de-DE/konto/bestellungen/Z1/auftragsbestaetigung.pdf"],
            "Rechnungsformular" => ["get", "/de-DE/konto/bestellungen/Z1/rechnung"],
            "Rechnungsformular abgeschickt" => ["post", "/de-DE/konto/bestellungen/Z1/rechnung"],
            "Rechnungs-PDF" => ["get", "/de-DE/konto/bestellungen/Z1/rechnung.pdf"],
            "Erstattungsformular" => ["get", "/de-DE/konto/bestellungen/Z1/erstattung"],
        ];
    }

    #[DataProvider("oldDocumentAddresses")]
    public function testAnOldDocumentAddressLeadsToTheOrdersPageInTheCrm(string $method, string $uri): void
    {
        $crm = "https://my.suma-ev.de/bestellung/Z1/1.60MEKpEs0PvRka04hcSrjOxF7y52S6K5";
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["crm_url" => $crm])),
        ]);

        $this->signedIn()->{$method}($uri)
            ->assertStatus(303)
            ->assertRedirect($crm . "?lang=de-DE")
            ->assertHeader("Cache-Control", "no-store, private");

        // Nichts mehr an den Keyserver außer der Bestellung selbst — kein
        // InvoiceNinja-Formular, kein PDF, keine Erstattung.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), ".pdf")
            || str_ends_with(parse_url($request->url(), PHP_URL_PATH), "/invoice")
            || str_ends_with(parse_url($request->url(), PHP_URL_PATH), "/refund"));
    }

    #[DataProvider("oldDocumentAddresses")]
    public function testAnOldDocumentAddressLeadsToTheOrderHereWithoutACrmLink(string $method, string $uri): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order()),
        ]);

        $this->signedIn()->{$method}($uri)
            ->assertRedirect(route("account.orders.show", ["reference" => "Z1"]));
    }

    #[DataProvider("oldDocumentAddresses")]
    public function testAnOldDocumentAddressOfAnotherKeysOrderIs404(string $method, string $uri): void
    {
        $this->keyserver([
            "*/api/json/checkout/*" => Http::response($this->order(["key" => self::OTHER_KEY, "crm_url" => "https://my.suma-ev.de/bestellung/Z1/x"])),
        ]);

        $this->signedIn()->{$method}($uri)->assertNotFound();
    }
}
