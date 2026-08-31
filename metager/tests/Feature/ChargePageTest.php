<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Aufladen, Schritt zwei: /konto/aufladen/<menge> — die Zahlungsart wählen.
 *
 * App\Http\Controllers\ChargeController::show() ist die neue Startseite des
 * Bezahlvorgangs, der aus dem Keymanager hierher zieht. Was hier schiefgehen
 * kann, ist dasselbe wie bei /konto (AccountPageTest hat die Begründung) und
 * zusätzlich: eine Menge, die kein Paket ist, oder ein Besucher, der gerade
 * gar nicht aufladen darf, dürfen hier trotzdem nicht landen — beides prüft
 * der Controller noch einmal, unabhängig davon, ob /konto die Kachel
 * überhaupt gezeigt hätte.
 */
class ChargePageTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear("account-logincode:" . self::A_KEY);
    }

    /** @param list<array{amount: float|int, expiration: string}> $orders */
    private function keyserverKnows(?array $orders = null): void
    {
        $orders ??= [["amount" => 248, "expiration" => "2027-03-14 00:00:00"]];

        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => 248,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => $orders,
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]);
    }

    private function signedIn(): self
    {
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    public function testThePageRenders(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertSeeText("1.000")
            ->assertSee(route("account.checkout.cash", ["amount" => 1000]), false);
    }

    /**
     * Ein Weg ganz zurück zum Konto, nicht nur zu einer anderen Menge — der
     * fehlte anfangs (Feedback währenddessen: "no way to abort the payment
     * process and get back to the plain account page").
     */
    public function testThePageOffersAWayBackToThePlainAccount(): void
    {
        $this->keyserverKnows();

        // Nicht bloß assertSee(route("account")): "Menge ändern" verlinkt auf
        // route("account") . "#charge", das dieselbe Zeichenkette als
        // Teilstring enthält und den Test sonst auch ohne cancelUrl grün
        // ließe.
        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertSee('href="' . route("account") . '"', false);
    }

    public function testThePageIsNeverCached(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertHeader("Cache-Control", "no-store, private");
    }

    public function testAVisitorWithoutAKeyIsSentToSignIn(): void
    {
        Http::preventStrayRequests();

        $response = $this->get("/de-DE/konto/aufladen/1000");

        $response->assertRedirect();
        $this->assertStringContainsString("/anmelden", $response->headers->get("Location"));
    }

    /**
     * Nicht irgendein Ziel danach — genau dieselbe Menge, damit eine
     * Anmeldung mitten im Bezahlvorgang nicht auf der Paketwahl neu beginnt.
     */
    public function testSigningInReturnsToTheSameAmount(): void
    {
        Http::preventStrayRequests();

        $response = $this->get("/de-DE/konto/aufladen/1000");

        $this->assertStringContainsString(
            rawurlencode(route("account.checkout", ["amount" => 1000])),
            $response->headers->get("Location")
        );
    }

    /**
     * Eine Menge, die keine der aktuellen Paketgrößen ist — weder geraten
     * noch aus einem alten Lesezeichen mit inzwischen geänderten Paketen.
     */
    public function testAnAmountThatIsNotATierBouncesBackToTheAccount(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/999")
            ->assertRedirect(route("account") . "#charge");
    }

    /**
     * Drei offene Aufladungen sind das Limit (App\Landing\ChargeEligibility)
     * — dieselbe Prüfung wie auf /konto, hier ein zweites Mal, weil ein
     * Lesezeichen oder ein zweiter Tab die Kachel auf /konto umgehen kann.
     */
    public function testAKeyWithThreeOpenTopUpsIsBouncedBack(): void
    {
        $this->keyserverKnows(orders: [
            ["amount" => 500, "expiration" => "2027-01-01 00:00:00"],
            ["amount" => 500, "expiration" => "2027-02-01 00:00:00"],
            ["amount" => 500, "expiration" => "2027-03-01 00:00:00"],
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertRedirect(route("account") . "#charge");
    }

    public function testAProxySessionIsBouncedBack(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->withHeaders(["is-proxy" => "true"])
            ->get("/de-DE/konto/aufladen/1000")
            ->assertRedirect(route("account") . "#charge");
    }

    /**
     * Die Entwicklungs-Zahlungsart ist nur unter app()->environment('local')
     * erreichbar (wie routes/checkout/manual.js's NODE_ENV-Prüfung im
     * Keymanager) — phpunit.xml setzt APP_ENV=testing, also fehlt die Kachel
     * standardmäßig.
     */
    public function testTheDevelopmentPaymentIsHiddenOutsideLocal(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertDontSee(route("account.checkout.manual", ["amount" => 1000]), false);
    }

    public function testTheDevelopmentPaymentAppearsInLocal(): void
    {
        $this->keyserverKnows();
        $this->app["env"] = "local";

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertSee(route("account.checkout.manual", ["amount" => 1000]), false);
    }

    /**
     * Zahlungsarten, die noch nicht umgezogen sind, verlinken weiter zum
     * Keymanager — dieselbe Adresse, die /konto vorher für jede Zahlungsart
     * benutzt hat.
     */
    public function testOtherPaymentMethodsLinkOnToTheKeymanager(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertSee("/keys/key/" . self::A_KEY . "/checkout/1000#payment", false);
    }
}
