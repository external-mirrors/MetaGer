<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Die Landeseite nach einer Weiterleitung — /konto/aufladen/abschluss/<referenz>.
 *
 * Micropayment ist die erste Zahlungsart, die hierher zurückkommt; VR
 * Payment und PayPal teilen sich dieselbe Seite, sobald sie folgen
 * (App\Http\Controllers\ChargeController::returned()). Dieselbe
 * Zugehörigkeitsprüfung wie bei der Barzahlung — eine fremde Referenz zeigt
 * hier nichts, obwohl die Nummer selbst kein Geheimnis ist.
 */
class ChargeReturnedTest extends TestCase
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

    public function testAPaidOrderShowsThanks(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::A_KEY,
                "paid" => true,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/abschluss/Z1")
            ->assertOk()
            ->assertSee(trans("checkout.returned.heading"))
            ->assertDontSee(trans("checkout.returned.pending"));
    }

    public function testAnUnpaidOrderShowsItIsStillPending(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::A_KEY,
                "paid" => false,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/abschluss/Z1")
            ->assertOk()
            ->assertSee(trans("checkout.returned.pending"));
    }

    /**
     * Nach geglückter Zahlung ist "suchen" der nächste Schritt — die Seite bot
     * ihn bisher nicht an, nur den Weg zurück zum Konto.
     */
    public function testAPaidOrderPointsOnToTheStartpage(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::A_KEY,
                "paid" => true,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/abschluss/Z1")
            ->assertOk()
            ->assertSee(trans("checkout.returned.next"))
            ->assertSee('href="' . route("startpage") . '"', false);
    }

    /**
     * Auch solange die Zahlung noch bearbeitet wird, führt ein Weg zur Suche —
     * dort nur nicht als primärer.
     */
    public function testAPendingOrderStillOffersTheStartpage(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::A_KEY,
                "paid" => false,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/abschluss/Z1")
            ->assertOk()
            ->assertSee('href="' . route("startpage") . '"', false);
    }

    public function testTheResponseIsNeverCached(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response([
                "public_id" => "Z1",
                "amount" => 1000,
                "price" => "10.00",
                "expires_at" => "2027-05-14T00:00:00.000Z",
                "key" => self::A_KEY,
                "paid" => true,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/abschluss/Z1")
            ->assertHeader("Cache-Control", "no-store, private");
    }

    public function testAVisitorWithoutAKeyIsSentToSignIn(): void
    {
        $this->keyserverKnows();

        $this->get("/de-DE/konto/aufladen/abschluss/Z1")
            ->assertRedirect();
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
                "paid" => true,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/abschluss/Z1")
            ->assertNotFound();
    }

    public function testAnUnknownReferenceIs404(): void
    {
        $this->keyserverKnows([
            "*/api/json/checkout/*" => Http::response(["code" => 404, "error" => "not_found"], 404),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/abschluss/Z999999")
            ->assertNotFound();
    }
}
