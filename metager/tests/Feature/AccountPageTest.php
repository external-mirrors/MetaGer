<?php

namespace Tests\Feature;

use App\Authentication\KeyIdenticon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Das Konto, /konto.
 *
 * Lag als /keys/key/<uuid> im Keymanager und ist die dritte und dickste Seite
 * des Schlüsselvorgangs, die hierher gezogen ist. Was hier schiefgehen kann,
 * sieht nicht nach einem Fehler aus:
 *
 *   - **Der Schlüssel in der Adresse.** Der ganze Ertrag dieses Umzugs ist,
 *     dass diese Seite ihn nirgends trägt. Eine Weiterleitung, die ihn stehen
 *     lässt, macht sie zunichte, und niemand sieht es — die Seite sieht richtig
 *     aus.
 *   - **Die Zahlen.** Guthaben und Verfallsdatum stehen neben einem
 *     Bezahlvorgang. Eine erfundene Zahl ist hier keine Ungenauigkeit.
 *   - **Der Rückweg in die App.** Er ist ein Weiterleiten mit einer
 *     Zugangsberechtigung im Ziel; welcher Host sie bekommt, hängt an einer
 *     Aufzählung ({@see \App\Landing\AppCallback}).
 *
 * Und dass sie nirgends zwischengespeichert wird: auf ihr steht ein Guthaben.
 *
 * Der Rückweg in die App hat einen eigenen Test
 * ({@see AccountAppCallbackTest}), weil er als einziger etwas an eine andere
 * Anwendung übergibt.
 */
class AccountPageTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear("account-logincode:" . self::A_KEY);
    }

    /**
     * Der Keyserver kennt diesen Schlüssel.
     *
     * `charge_orders` und `key_config` bekommt nur ein authentifizierter
     * Aufrufer, und das ist diese Anwendung immer — sie schickt den Bearer-Token
     * mit. Deshalb stehen sie hier auch in der Antwort.
     *
     * @param list<array{amount: float|int, expiration: string}> $orders
     */
    private function keyserverKnows(
        float $charge = 248,
        string $expiration = "2027-03-14 00:00:00",
        ?array $orders = null,
        ?string $membershipEnd = null
    ): void {
        $orders ??= [["amount" => $charge, "expiration" => $expiration]];

        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*/logincode" => Http::response(["key" => self::A_KEY, "code" => "123456"]),
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => $charge,
                "expiration" => $expiration,
                "charge_orders" => $orders,
                "key_config" => ["membershipEndDate" => $membershipEnd],
            ]),
        ]);
    }

    /** Angemeldet, so wie ein Browser es ist: über das Cookie. */
    private function signedIn(): self
    {
        // false: `key` steht in EncryptCookies::$except, weil der Keymanager es
        // unter demselben Host lesen können muss.
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    public function testThePageRenders(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertSeeText(__("account.page.heading"))
            ->assertSeeText("248");
    }

    /**
     * Der Schlüssel steht nicht *im Text* der Seite.
     *
     * Die alte Seite zeigte ihn als große, anklickbare Zeichenfolge, gleich
     * unter dem QR-Code — auf einer Seite, die Menschen für Supportanfragen
     * fotografieren. Hier steht er in einem zugeklappten `<details>` und in
     * einem Feld, also in einem Attribut und nicht im gerenderten Text; der
     * Test unterscheidet das, indem er den sichtbaren Text prüft.
     *
     * Das Gegenstück ist {@see testTheKeyIsThereToBeCopied()}: er muss
     * erreichbar sein, sonst ist die Seite für jemanden ohne Kamera und ohne
     * Lesezeichen wertlos.
     */
    public function testTheKeyIsNotWrittenIntoThePageText(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertDontSeeText(self::A_KEY);
    }

    /**
     * Und er ist trotzdem da, zum Ablesen und Kopieren.
     *
     * Der Fall, der ihn unverzichtbar macht: das Anmeldeformular fragt in
     * erster Linie nach dem Schlüssel. Wer auf einem zweiten Gerät ohne Kamera
     * sitzt, kein Lesezeichen hat und den Anmeldecode nicht innerhalb seiner
     * zehn Sekunden abtippen will, hat sonst nichts, was er dort eingeben
     * könnte — und das Konto wäre die eine Seite, die ihm nicht hilft.
     *
     * In einem `<details>` und deshalb ohne Javascript erreichbar; `readonly`
     * und nicht `disabled`, weil ein deaktiviertes Feld sich weder markieren
     * noch vorlesen lässt.
     */
    public function testTheKeyIsThereToBeCopied(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()->get("/de-DE/konto")->assertOk();

        $response->assertSee('<details class="account-key">', false);
        $response->assertSee('id="account-key"', false);
        $response->assertSee('value="' . self::A_KEY . '"', false);
        $response->assertSee('readonly', false);
        $response->assertSeeText(__("account.page.save.key.summary"));
    }

    /** Und schon gar nicht in der Adresse dieser Seite. */
    public function testAKeyInTheQueryIsTakenOutOfTheAddress(): void
    {
        $this->keyserverKnows();

        $response = $this->get("/de-DE/konto?key=" . self::A_KEY);

        // Ins Cookie, und dann fort damit. Der Schlüssel stand hier nur, weil
        // eine alte Adresse ihn mitgebracht hat (/keys/key/<uuid>).
        // Nicht assertRedirect("/de-DE/konto"): das reicht den Pfad durch
        // url(), und unter einer de-DE-Anfrage setzt URL::formatPathUsing das
        // Präfix noch einmal davor.
        $response->assertRedirect(config("app.url") . "/de-DE/konto");
        $response->assertCookie("key", self::A_KEY, false);
    }

    /**
     * Wer nicht angemeldet ist, sieht kein fremdes Konto, sondern das
     * Anmeldeformular — und kommt danach hierher zurück.
     */
    public function testAVisitorWithoutAKeyIsSentToSignIn(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $this->get("/de-DE/konto")
            ->assertRedirectContains("/de-DE/anmelden")
            ->assertRedirectContains("redirect_success");
    }

    /**
     * Die Web-Erweiterung meldet mit einem anonymen Token an: wir erfahren den
     * Schlüssel nie, und das ist der Zweck der Abmachung. Es gibt hier also
     * kein Konto zu zeigen.
     */
    public function testAnAnonymouslySignedInVisitorIsSentToTheTokenPage(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $this->withHeader("anonymous-token-key", "irgendein-token")
            ->withHeader("tokenauthorization", "full")
            ->get("/de-DE/konto")
            ->assertRedirectContains("/de-DE/hilfe/anonyme-token");
    }

    /** Auf der Seite steht ein Guthaben. Sie gehört in keinen Zwischenspeicher. */
    public function testThePageIsNeverCached(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertHeader("Cache-Control", "no-store, private");
    }

    /**
     * Die Kennung des Kontos: die Marke und die letzten sechs Zeichen.
     *
     * Dieselben sechs, die die Kontokachel in der Ecke zeigt
     * ({@see \App\Authentication\KeyUser::getKeyFingerprint()}), und dieselbe
     * Marke — sie ist aus ihnen abgeleitet. Dass beide Seiten dieselbe zeigen,
     * ist der Grund, warum die Marke überhaupt etwas taugt.
     */
    public function testTheAccountShowsItsMarkAndFingerprint(): void
    {
        $this->keyserverKnows();

        $fingerprint = substr(self::A_KEY, -6);

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertSeeText(strtoupper($fingerprint))
            ->assertSee("account-mark--hue-" . KeyIdenticon::hue($fingerprint), false);
    }

    /**
     * Die Verfallsdaten der einzelnen Aufladungen.
     *
     * Der Keyserver liefert sie nur an einen authentifizierten Aufrufer, und
     * sie sind der Grund, warum das Konto mehr sagen kann als ein einzelnes
     * Datum: wer dreimal aufgeladen hat, hat drei Töpfe, die nacheinander
     * ablaufen. Die alte Seite hängte sie an ein Fragezeichen mit `:hover` —
     * auf einem Telefon also an nichts.
     */
    public function testEveryTopUpKeepsItsOwnExpiryDate(): void
    {
        $this->keyserverKnows(
            charge: 300,
            expiration: "2027-03-14 00:00:00",
            orders: [
                ["amount" => 100, "expiration" => "2026-11-02 00:00:00"],
                ["amount" => 200, "expiration" => "2027-03-14 00:00:00"],
            ]
        );

        $response = $this->signedIn()->get("/de-DE/konto")->assertOk();

        $response->assertSee("<details", false);
        $response->assertSeeText("02.11.2026");
        $response->assertSeeText("14.03.2027");
    }

    /**
     * Die Pakete kommen vom Keyserver und nicht aus einer zweiten Preisliste.
     *
     * {@see \App\Landing\KeyPrice} fragt danach, und der Grund steht dort: zwei
     * Repositories, die je einen Preis behaupten, sind neben einem laufenden
     * Bezahlvorgang ein Fehler, der Geld kostet.
     */
    public function testTheTopUpTiersComeFromTheKeyserver(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()->get("/de-DE/konto")->assertOk();

        $response->assertSeeText("5 €");
        $response->assertSeeText("10 €");
        $response->assertSeeText("20 €");
    }

    /**
     * Und sie führen auf die lokale Aufladeseite — kein Schlüssel im URL.
     *
     * Bis App\Http\Controllers\ChargeController hierher zog, war dies der
     * einzige Link auf dieser Seite, der noch einen Schlüssel trug: die
     * Kasse drüben kannte keine Sitzung und las ihn aus ihrem eigenen Pfad.
     * Für Bar und die Entwicklungs-Zahlungsart gilt das nicht mehr — beide
     * lesen den Schlüssel wie /konto selbst aus dem Cookie.
     */
    public function testATierLeadsIntoTheLocalChargePage(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertSee("/de-DE/konto/aufladen/1000", false);
    }

    /**
     * Drei Gründe, kein Paket anzubieten, und alle drei standen schon auf der
     * alten Seite.
     */
    public function testAProxySessionIsNotOfferedAPayment(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()
            ->withHeader("is-proxy", "true")
            ->get("/de-DE/konto")
            ->assertOk();

        $response->assertSeeText(__("account.page.charge.blocked.proxy"));
        $response->assertDontSee("/checkout/1000", false);
    }

    public function testAKeyWithThreeOpenTopUpsIsNotOfferedAFourth(): void
    {
        $this->keyserverKnows(
            charge: 300,
            orders: [
                ["amount" => 100, "expiration" => "2026-11-02 00:00:00"],
                ["amount" => 100, "expiration" => "2027-01-02 00:00:00"],
                ["amount" => 100, "expiration" => "2027-03-14 00:00:00"],
            ]
        );

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertSeeText(__("account.page.charge.blocked.full"));
    }

    public function testAMemberIsNotOfferedATokenPackage(): void
    {
        $this->keyserverKnows(membershipEnd: "2030-12-31T23:59:59.000Z");

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertSeeText(__("account.page.charge.blocked.member"));
    }

    /**
     * Antwortet der Keyserver nicht, wird keine Zahl erfunden.
     *
     * Die Seite bleibt trotzdem stehen: der Weg zum Schlüssel ist genau dann
     * wichtig, wenn sonst nichts geht.
     */
    public function testAnUnreachableKeyserverDoesNotInventABalance(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("", 500)]);

        $response = $this->signedIn()->get("/de-DE/konto")->assertOk();

        $response->assertSeeText(__("account.page.balance.unknown"));
        $response->assertDontSee('class="account-balance__number"', false);
    }

    /**
     * Der Anmeldecode für ein zweites Gerät.
     *
     * Welcher Schlüssel gemeint ist, steht im Cookie und in keinem Parameter —
     * der Keymanager hatte ihn im Pfad.
     */
    public function testTheLoginCodeIsFetchedForTheSignedInKey(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/anmeldecode")
            ->assertOk()
            ->assertExactJson(["code" => "123456"]);

        Http::assertSent(fn($request) => str_contains($request->url(), "/api/json/key/" . self::A_KEY . "/logincode")
            && $request->method() === "POST");
    }

    /** Ohne Anmeldung gibt es keinen Code — er ist selbst eine Anmeldung. */
    public function testTheLoginCodeNeedsAKey(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $this->get("/de-DE/konto/anmeldecode")
            ->assertStatus(401)
            ->assertExactJson(["code" => null]);
    }

    /**
     * Die Zahlen folgen der Sprache und nicht dem Deutschen.
     *
     * `2.480` heißt auf Englisch `2,480`, und diese Seite wird in zwölf
     * Sprachen ausgeliefert. Ein festes `number_format(..., ',', '.')` sieht in
     * der Sprache, in der es geschrieben wurde, richtig aus und ist überall
     * sonst falsch — genau die Sorte Fehler, die niemandem auffällt, der die
     * Seite baut.
     */
    public function testTheBalanceIsFormattedInTheVisitorsLanguage(): void
    {
        $this->keyserverKnows(charge: 2480);

        $this->signedIn()
            ->get("/de-DE/konto")
            ->assertOk()
            ->assertSeeText("2.480");
    }

    public function testTheEnglishBalanceUsesEnglishSeparators(): void
    {
        $this->keyserverKnows(charge: 2480);

        $this->signedIn()
            ->get("/en-GB/konto")
            ->assertOk()
            ->assertSeeText("2,480");
    }

}
