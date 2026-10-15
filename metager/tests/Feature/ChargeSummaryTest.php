<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Die Paket-Kurzfassung über der Zustimmungsseite —
 * partials/checkout-summary.blade.php
 * (App\Http\Controllers\ChargeController::render()).
 *
 *  - **Der Preis steht dabei.** Feedback dazu: wer bezahlen will, will dabei
 *    sehen, was das Paket kostet.
 *  - **Eine Menge, die kein Paket ist, landet hier gar nicht.** Die Seite
 *    wird auch direkt über ihre Adresse erreicht und rendert sonst eine
 *    Seite für einen Betrag, den es nicht zu kaufen gibt — und render()
 *    müsste einen Preis für ihn finden.
 */
class ChargeSummaryTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    /** @return array<string, array{0: string}> */
    public static function methodPages(): array
    {
        return [
            "Zustimmung" => ["/de-DE/konto/aufladen/1000/bezahlen"],
        ];
    }

    /** @return array<string, array{0: string}> */
    public static function methodShowRoutes(): array
    {
        return [
            "Paketwahl" => ["/de-DE/konto/aufladen/999"],
            "Zustimmung" => ["/de-DE/konto/aufladen/999/bezahlen"],
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
