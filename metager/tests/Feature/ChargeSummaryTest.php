<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Die Paket-Kurzfassung über jedem Schritt des Bezahlvorgangs —
 * partials/checkout-summary.blade.php, eingebunden von der Paketwahl und von
 * jeder Zahlweisen-Seite (App\Http\Controllers\ChargeController::render()).
 *
 * Zwei Dinge, die vorher nur die Paketwahl konnte und die Zahlweisen-Seiten
 * nicht:
 *
 *  - **Der Preis steht dabei.** Auf /konto/aufladen/<menge> stand er von
 *    Anfang an; sobald eine Zahlweise gewählt war, zeigte die Kurzfassung nur
 *    noch die Tokenzahl. Feedback dazu: wer eine Zahlweise wählt, will dabei
 *    sehen, was das Paket kostet.
 *  - **Eine Menge, die kein Paket ist, landet hier gar nicht.** Bisher prüfte
 *    das nur show(); die Zahlweisen-Seiten werden direkt über ihre Adresse
 *    erreicht und rendern sonst eine Seite für einen Betrag, den es nicht zu
 *    kaufen gibt — und render() müsste einen Preis für ihn finden.
 */
class ChargeSummaryTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    /**
     * Jede Seite, die die Kurzfassung einbindet, mit einer gültigen Menge in
     * ihrer Adresse. Bar und die Entwicklungs-Zahlart brauchen keinen
     * zusätzlichen Keyserver-Stub; die drei weiterleitenden holen beim
     * Rendern nichts nach.
     *
     * @return array<string, array{0: string}>
     */
    public static function methodPages(): array
    {
        return [
            "Paketwahl" => ["/de-DE/konto/aufladen/1000"],
            "Bar" => ["/de-DE/konto/aufladen/1000/bar"],
            "Wero" => ["/de-DE/konto/aufladen/1000/vrpayment"],
            "Micropayment (prepay)" => ["/de-DE/konto/aufladen/1000/micropayment/prepay"],
            "Micropayment (Lastschrift)" => ["/de-DE/konto/aufladen/1000/micropayment/lastschrift"],
            // PayPal rendert den Rahmen mit eigener CSP und einem eigenen
            // js-Schlüssel im $extra von render() — die Kurzfassung muss auch
            // durch diesen Pfad kommen.
            "PayPal (Wallet)" => ["/de-DE/konto/aufladen/1000/paypal/paypal"],
        ];
    }

    /** @return array<string, array{0: string}> */
    public static function methodShowRoutes(): array
    {
        return [
            "Paketwahl" => ["/de-DE/konto/aufladen/999"],
            "Bar" => ["/de-DE/konto/aufladen/999/bar"],
            "Wero" => ["/de-DE/konto/aufladen/999/vrpayment"],
            "Micropayment (prepay)" => ["/de-DE/konto/aufladen/999/micropayment/prepay"],
            "PayPal (Wallet)" => ["/de-DE/konto/aufladen/999/paypal/paypal"],
        ];
    }

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
            "*/api/json/key/*/checkout/paypal/*" => Http::response([
                "client_id" => "sb-client-id",
                "direct_card_enabled" => false,
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

    #[DataProvider("methodPages")]
    public function testEveryStepShowsThePackagePrice(string $url): void
    {
        $this->keyserverKnows();

        // 1000 Token · 0,01 € = 10 €, wie account.page.charge.price es rendert.
        $this->signedIn()
            ->get($url)
            ->assertOk()
            ->assertSee("10 €");
    }

    #[DataProvider("methodShowRoutes")]
    public function testAnAmountThatIsNotATierBouncesBackToTheAccount(string $url): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get($url)
            ->assertRedirect(route("account") . "#charge");
    }
}
