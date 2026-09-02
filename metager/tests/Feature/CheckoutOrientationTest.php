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
 *  - **Wo bin ich?** Keine Seite sagte, wo im Ablauf man steht. Die Paketwahl
 *    hieß „Guthaben aufladen", die Zahlweisenwahl hieß genauso, und die Seite
 *    danach trug nur noch den Namen der Zahlweise
 *    (partials/checkout-steps.blade.php).
 *  - **Was habe ich danach?** Die Kurzfassung nannte Menge und Preis, aber
 *    nicht den Stand, um dessentwillen der Vorgang stattfindet
 *    (partials/checkout-summary.blade.php).
 *  - **Wann ist das Guthaben da?** Die Frage, die die Wahl der Zahlungsart
 *    entscheidet, beantwortete das Raster aus elf gleich aussehenden Kacheln
 *    nirgends (checkout/index.blade.php).
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
        // Die spezifischeren Muster zuerst: Http::fake merkt sich die
        // Reihenfolge, und */api/json/key/* würde sonst auch auf
        // .../key/<uuid>/checkout/paypal/<zahlweise> zutreffen.
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

    public function testTheMethodPageIsStepTwoOfThree(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertSee(trans("checkout.page.steps.method"))
            ->assertSee('checkout-steps__step--current', false)
            // Der erste Schritt ist erledigt und führt als Link zurück auf die
            // Paketwahl; „bezahlen" liegt noch vor einem.
            ->assertSee('checkout-steps__step--done', false);
    }

    public function testAPaymentMethodPageIsStepThree(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()->get("/de-DE/konto/aufladen/1000/bar")->assertOk();

        // Zwei erledigte Schritte, nicht einer: Menge *und* Zahlungsart liegen
        // hinter einem, wenn man auf einer einzelnen Zahlweise steht.
        $this->assertSame(
            2,
            substr_count($response->getContent(), "checkout-steps__step--done")
        );
    }

    /**
     * Die Zahl, wegen der der ganze Vorgang stattfindet. Der Preis allein
     * beantwortet sie nicht, sobald schon etwas auf dem Schlüssel liegt.
     */
    public function testTheSummarySaysWhatTheBalanceWillBeAfterwards(): void
    {
        $this->keyserverKnows(charge: 248);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertSee(trans("checkout.page.summary.after"))
            ->assertSeeText("1.248");
    }

    /**
     * Und sie steht auf *jeder* Seite des Vorgangs, nicht nur auf der ersten —
     * das war schon beim Preis das Feedback, das ihn auf die Zahlweisen-Seiten
     * gebracht hat.
     */
    public function testTheSummaryCarriesOnToThePaymentMethodPages(): void
    {
        $this->keyserverKnows(charge: 248);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/vrpayment")
            ->assertOk()
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
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertDontSee(trans("checkout.page.summary.after"));
    }

    /**
     * Jede Kachel sagt, wann das Guthaben da ist. Bargeld und Überweisung
     * dauern, alles andere ist sofort da — und all das sah vorher gleich aus.
     */
    public function testEveryPaymentMethodTileSaysWhenTheBalanceArrives(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk()
            ->assertSee(trans("checkout.page.speed.post"))
            ->assertSee(trans("checkout.page.speed.transfer"))
            ->assertSee(trans("checkout.page.speed.instant"));
    }

    /**
     * „Sie werden zu X weitergeleitet" stand als letzter Absatz unter dem
     * Knopf — also hinter der Entscheidung, obwohl es die wichtigste Auskunft
     * dieser Seiten ist.
     */
    public function testTheRedirectNoticeStandsAboveTheButtonNotBelowIt(): void
    {
        $this->keyserverKnows();

        $html = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/micropayment/prepay")
            ->assertOk()
            ->getContent();

        $this->assertLessThan(
            strpos($html, trans("checkout.micropayment.submit")),
            strpos($html, "checkout-notice"),
            "Der Weiterleitungshinweis muss vor dem Absenden-Knopf stehen"
        );
    }

    /**
     * Die Anschrift stand ausschließlich auf der Seite *nach* dem Anlegen des
     * Auftrags: man musste zustimmen und eine Zahlungs-ID erzeugen, um zu
     * erfahren, wohin der Brief überhaupt gehen soll.
     */
    public function testTheCashPageShowsThePostalAddressBeforeAnyCommitment(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/bar")
            ->assertOk()
            ->assertSeeText("Postfach 51 01 43")
            ->assertSee(trans("checkout.cash.address_label"));
    }

    /**
     * Die sieben PayPal-Zahlweisen führten alle auf eine Seite mit derselben
     * Überschrift „Zahlung durchführen" — die einzige Bestätigung, auf der
     * angeklickten Kachel gelandet zu sein, war das Widget, das das SDK erst
     * später zeichnet.
     */
    public function testThePayPalPageNamesTheChosenFundingSource(): void
    {
        $this->keyserverKnows(extra: [
            "*/api/json/key/*/checkout/paypal/blik" => Http::response([
                "client_id" => "test-client-id",
                "direct_card_enabled" => false,
                "client_token" => null,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/paypal/blik")
            ->assertOk()
            ->assertSee(trans("checkout.paypal.funding.blik"));
    }

    /**
     * Der Ladehinweis lag in #checkout-paypal, und das steht `hidden`, bis das
     * SDK geladen hat — „Zahlungsmethode wird geladen" war also genau so lange
     * unsichtbar, wie es etwas zu sagen hatte, und eine Seite ohne Inhalt blieb
     * zurück, wenn das SDK gar nicht antwortete. Jetzt steht er davor und
     * deckt sich selbst auf (resources/js/checkout-paypal.js) — ohne
     * Javascript bleibt er verborgen, damit er nicht neben dem
     * <noscript>-Hinweis steht, dass hier nichts laden wird.
     */
    public function testThePayPalLoadingNoticeIsVisibleWhileTheSdkLoads(): void
    {
        $this->keyserverKnows(extra: [
            "*/api/json/key/*/checkout/paypal/paypal" => Http::response([
                "client_id" => "test-client-id",
                "direct_card_enabled" => false,
                "client_token" => null,
            ]),
        ]);

        $html = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/paypal/paypal")
            ->assertOk()
            ->getContent();

        $this->assertLessThan(
            strpos($html, 'id="checkout-paypal"'),
            strpos($html, 'id="checkout-paypal-loading"'),
            "Der Ladehinweis darf nicht im verborgenen SDK-Container liegen"
        );
    }
}
