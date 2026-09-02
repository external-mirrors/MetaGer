<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Die Entwicklungs-Zahlungsart — /konto/aufladen/<menge>/entwicklung.
 *
 * Ported aus routes/checkout/manual.js's `NODE_ENV === "development"`-Bremse,
 * hier `app()->environment('local')` — nur eben mit 404 statt der alten
 * Seite, die außerhalb der Entwicklung mit einem bloßen `throw` 500'te.
 * phpunit.xml setzt APP_ENV=testing, also muss jeder Test, der die Zahlungsart
 * tatsächlich erreichen will, `local` erst erzwingen.
 */
class ChargeManualTest extends TestCase
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

    public function testThePageIs404OutsideLocal(): void
    {
        // Auch die 404-Seite selbst kennt den angemeldeten Schlüssel — die
        // Kontokachel im Layout fragt ihn auf jeder Seite ab, Fehlerseiten
        // eingeschlossen — deshalb braucht auch dieser Test den Keyserver.
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/entwicklung")
            ->assertNotFound();
    }

    public function testSubmittingIs404OutsideLocal(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/konto/aufladen/1000/entwicklung")
            ->assertNotFound();
    }

    public function testThePageRendersInLocal(): void
    {
        $this->app["env"] = "local";
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/entwicklung")
            ->assertOk()
            ->assertSee(route("account.checkout.manual.submit", ["amount" => 1000]), false);
    }

    public function testSubmittingChargesImmediatelyAndReturnsToTheAccount(): void
    {
        $this->app["env"] = "local";
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/manual" => Http::response([
                "key" => self::A_KEY,
                "key_charge" => 1000,
                "key_expiration" => "2028-01-01 00:00:00",
                "payment_reference" => "Z1",
                "charged" => 1000,
            ], 201),
        ]);

        $this->signedIn()
            ->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/konto/aufladen/1000/entwicklung")
            ->assertRedirect(route("account") . "#charge");
    }

    public function testAForeignFormIsRefused(): void
    {
        $this->app["env"] = "local";
        $this->keyserverKnows();

        $this->signedIn()
            ->withHeaders(["Origin" => "https://evil.example"])
            ->post("/de-DE/konto/aufladen/1000/entwicklung")
            ->assertForbidden();
    }

    public function testAnAmountThatIsNotATierIsRefused(): void
    {
        $this->app["env"] = "local";
        $this->keyserverKnows();

        $this->signedIn()
            ->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/konto/aufladen/999/entwicklung")
            ->assertRedirect(route("account") . "#charge");
    }
}
