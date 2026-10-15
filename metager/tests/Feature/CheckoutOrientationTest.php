<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Was der Bezahlvorgang über sich selbst sagt — die Gestaltungsrunde nach dem
 * Umzug aus dem Keymanager.
 *
 * Die Seiten funktionierten; sie erklärten sich nur nicht. Drei Dinge fehlten
 * und werden hier festgehalten, weil sie sonst beim nächsten Umbau
 * unbemerkt wieder herausfallen:
 *
 *  - **Wo bin ich?** Keine Seite sagte, wo im Ablauf man steht
 *    (partials/checkout-steps.blade.php).
 *  - **Was habe ich danach?** Die Kurzfassung nannte Menge und Preis, aber
 *    nicht den Stand, um dessentwillen der Vorgang stattfindet
 *    (partials/checkout-summary.blade.php).
 *  - **Wohin geht es?** Der Hinweis, dass es zu suma-payments weitergeht,
 *    steht vor dem Knopf, nicht dahinter.
 */
class CheckoutOrientationTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear("account-logincode:" . self::A_KEY);
    }

    /**
     * @param array<string, \Illuminate\Http\Client\Response> $extra
     */
    private function keyserverKnows(float $charge = 248, array $extra = []): void
    {
        Http::preventStrayRequests();
        Http::fake(array_merge($extra, [
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => $charge,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => $charge, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]));
    }

    private function signedIn(): self
    {
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    public function testTheConsentPageIsStepTwoOfTwo(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bezahlen")
            ->assertOk()
            ->assertSee(trans("checkout.page.steps.pay"))
            ->assertSee('checkout-steps__step--current', false);

        // Die Menge liegt hinter einem und führt als Link zurück.
        $this->assertSame(1, substr_count($response->getContent(), "checkout-steps__step--done"));
    }

    /**
     * Die Zahl, wegen der der ganze Vorgang stattfindet. Der Preis allein
     * beantwortet sie nicht, sobald schon etwas auf dem Schlüssel liegt.
     */
    public function testTheSummarySaysWhatTheBalanceWillBeAfterwards(): void
    {
        $this->keyserverKnows(charge: 248);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bezahlen")
            ->assertOk()
            ->assertSee(trans("checkout.page.summary.after"))
            ->assertSeeText("1.248");
    }

    /**
     * Ein unerreichbarer Keyserver darf die Kurzfassung nicht in ein „Guthaben
     * danach: 1.000" verwandeln — dann fällt genau diese Spalte weg, nicht die
     * ganze Kurzfassung.
     */
    public function testNoBalanceIsPromisedWhenTheKeyserverDoesNotAnswerTheCharge(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bezahlen")
            ->assertOk()
            ->assertDontSee(trans("checkout.page.summary.after"));
    }

    /**
     * „Sie werden zu X weitergeleitet" stand als letzter Absatz unter dem
     * Knopf — also hinter der Entscheidung, obwohl es die wichtigste Auskunft
     * dieser Seite ist.
     */
    public function testTheRedirectNoticeStandsAboveTheButtonNotBelowIt(): void
    {
        $this->keyserverKnows();

        $html = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bezahlen")
            ->assertOk()
            ->getContent();

        $this->assertLessThan(
            strpos($html, trans("checkout.suma.submit")),
            strpos($html, "checkout-notice"),
            "Der Weiterleitungshinweis muss vor dem Absenden-Knopf stehen"
        );
    }
}
