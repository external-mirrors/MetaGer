<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Barzahlung — /konto/aufladen/<menge>/bar.
 *
 * Der erste lokal laufende Bezahlvorgang, und der einzige, der eine
 * Postgres-Zeile beim Keymanager anlegt (App\Authentication\ChargeOrderIssuer,
 * dessen Gegenstück pass/routes/api.js's neue /key/:key/checkout- und
 * /checkout/:public_id-Endpunkte). Zwei Dinge sind hier nicht offensichtlich:
 *
 *  - **Ein Neuladen der Ergebnisseite darf keinen zweiten Auftrag anlegen.**
 *    Die alte Kasse im Keymanager rendert nach dem Anlegen dieselbe Adresse
 *    noch einmal; dieser Port benutzt stattdessen POST/redirect/GET.
 *  - **Eine fremde Auftragsnummer darf hier nichts zeigen.** Nummern sind
 *    klein und fortlaufend, kein Geheimnis — die Zugehörigkeit zum
 *    angemeldeten Schlüssel ist die einzige Zugangskontrolle.
 */
class ChargeCashTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";
    private const OTHER_KEY = "aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee";

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
            ->post("/de-DE/konto/aufladen/1000/bar", $fields);
    }

    public function testTheConsentFormRenders(): void
    {
        // Die spezifischere Route zuerst - Http::fake matched der Reihe nach,
        // und */api/json/key/* (unten in keyserverKnows()) würde sonst auch
        // hierauf zutreffen.
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bar")
            ->assertOk()
            ->assertSee('name="revocation"', false)
            ->assertSee('required', false);
    }

    public function testTheFormPostsToItself(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bar")
            ->assertSee(route("account.checkout.cash.submit", ["amount" => 1000]), false);
    }

    public function testSubmittingWithoutConsentIsRefused(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
            ], 201),
        ]);

        $this->submit([])
            ->assertRedirect(route("account.checkout.cash", ["amount" => 1000]) . "?error=consent");

        Http::assertNotSent(fn ($request) => str_contains($request->url(), "/checkout"));
    }

    public function testConsentingCreatesTheOrderAndRedirectsToIt(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
            ], 201),
        ]);

        $this->submit(["revocation" => "on"])
            ->assertRedirect(route("account.checkout.cash.created", ["amount" => 1000, "reference" => "Z1"]));
    }

    public function testAnUnreachableKeyserverBouncesBackWithAnError(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout" => Http::response(null, 500),
        ]);

        $this->submit(["revocation" => "on"])
            ->assertRedirect(route("account.checkout.cash", ["amount" => 1000]) . "?error=unreachable");
    }

    public function testAForeignFormIsRefused(): void
    {
        $this->keyserverKnows();

        $this->submit(["revocation" => "on"], ["Origin" => "https://evil.example"])
            ->assertForbidden();
    }

    public function testTheCreatedOrderPageShowsTheOrderId(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::A_KEY,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bar/Z1")
            ->assertOk()
            ->assertSee('value="Z1"', false)
            ->assertSee("14.05.2027", false);
    }

    /**
     * Das eigentliche Neuladen-Problem, das POST/redirect/GET löst: die
     * Ergebnisseite ist ein reines GET, das die Ladung erneut abfragt, statt
     * etwas zu glauben, das nur durch die Weiterleitung mitgereist wäre — sie
     * ruft dafür nie den Anlegen-Endpunkt auf, egal wie oft sie aufgerufen
     * wird. (Ein zweiter echter Aufruf derselben Route innerhalb *eines*
     * Testfalls 404t hier unabhängig davon aus einem Grund, der nichts mit
     * dieser Seite zu tun hat — reproduzierbar auch mit /konto, das es lange
     * vor diesem Umzug gab — deshalb reicht ein Aufruf, um zu zeigen, dass er
     * den POST-Endpunkt nicht berührt.)
     */
    public function testTheCreatedOrderPageNeverCreatesAnOrder(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::A_KEY,
            ]),
        ]);

        $this->signedIn()->get("/de-DE/konto/aufladen/1000/bar/Z1")->assertOk();

        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), "/checkout")
            && $request->method() === "POST");
    }

    public function testAnOrderBelongingToAnotherKeyIsNotShown(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::OTHER_KEY,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bar/Z1")
            ->assertNotFound();
    }

    public function testAnUnknownReferenceIs404(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response(["code" => 404, "error" => "not_found"], 404),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bar/Z999999")
            ->assertNotFound();
    }
}
