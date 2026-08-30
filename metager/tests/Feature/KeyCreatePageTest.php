<?php

namespace Tests\Feature;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Die Seite zum Erstellen eines Schlüssels, /schluessel-erstellen.
 *
 * Sie lag als /keys/key/create im Keymanager und ist nach der Anmeldung die
 * zweite Seite des Schlüsselvorgangs, die hierher gezogen ist. Was hier
 * schiefgehen kann, sieht nicht nach einem Fehler aus:
 *
 *   - **Der Schlüssel selbst.** Er kommt vom Keyserver, weil nur der weiß, ob
 *     eine gewürfelte UUID schon jemandem gehört. Antwortet er nicht, darf die
 *     Seite keinen erfinden — ein Schlüssel, den niemand kennt, ist ein Konto,
 *     in das später niemand mehr hineinkommt.
 *   - **Wer sie gar nicht sehen soll.** Mit Schlüssel ist das Ziel das Konto.
 *     Ein zweiter Schlüssel bekommt ein eigenes, getrenntes Guthaben, und
 *     genau davon lebt der häufigste Supportfall.
 *   - **Der QR-Code.** Er trägt nur den Schlüssel und nicht die Einstellungen
 *     dieses Browsers. Wächst er mit ihnen, wird er irgendwann so dicht, dass
 *     kein Telefon ihn mehr vom Bildschirm liest — und das merkt niemand hier,
 *     sondern jemand ein Jahr später beim Wiederanmelden.
 *
 * Und dass sie nirgends zwischengespeichert wird: auf ihr steht ein Schlüssel.
 */
class KeyCreatePageTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        // Die Bremse zählt pro Adresse, und in Tests ist das immer dieselbe.
        RateLimiter::clear("key-create:127.0.0.1");
    }

    /** Der Keyserver gibt einen Schlüssel heraus. */
    private function keyserverIssues(string $key = self::A_KEY): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/new" => Http::response(["key" => $key]),
            // KeyAuthGuard fragt nach jedem Schlüssel, den es an der Anfrage
            // findet — auf dieser Seite nur, wenn ein Test einen mitschickt.
            "*" => Http::response(["key" => $key, "charge" => 0]),
        ]);
    }

    public function testThePageRenders(): void
    {
        $this->keyserverIssues();

        $this->get("/de-DE/schluessel-erstellen")
            ->assertOk()
            ->assertSeeText(__("key-create.heading"))
            ->assertSee(self::A_KEY, false);
    }

    /** Das Formular schickt auf die Seite zurück, auf der es steht. */
    public function testTheFormPostsToItself(): void
    {
        $this->keyserverIssues();

        $response = $this->get("/de-DE/schluessel-erstellen")->assertOk();

        // Nicht url(): unter einem de-DE-Request setzt URL::formatPathUsing das
        // Präfix noch einmal davor. Der Pfad ist die Aussage.
        $response->assertSee('action="/de-DE/schluessel-erstellen"', false);
        $response->assertSee('<input type="hidden" name="key" value="' . self::A_KEY . '">', false);
    }

    /**
     * Der Schlüssel kommt vom Keyserver und wird nicht hier gewürfelt.
     *
     * `Str::uuid()` gäbe dieselben Bits und wäre eine Zeile statt eines
     * Netzaufrufs. Was es nicht gäbe, ist die Prüfung, ob die UUID schon
     * jemandem gehört: der Keyserver faltet jeden alten Schlüssel per MD5 in
     * denselben Raum, in dem hier gewürfelt wird.
     */
    public function testTheKeyComesFromTheKeyserver(): void
    {
        $this->keyserverIssues();

        $this->get("/de-DE/schluessel-erstellen")->assertOk();

        Http::assertSent(fn($request) => str_ends_with($request->url(), "/api/json/key/new")
            && $request->method() === "POST");
    }

    /**
     * Antwortet er nicht, wird keiner erfunden.
     *
     * Ein hier gewürfelter Schlüssel sähe genauso aus und ginge genauso gut ins
     * Cookie — und wäre ein Konto, das der Keyserver vielleicht schon vergeben
     * hat.
     */
    public function testAnUnreachableKeyserverYieldsNoKey(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response([], 502)]);

        $response = $this->get("/de-DE/schluessel-erstellen")->assertOk();

        $response->assertSeeText(__("key-create.errors.keyserver_unreachable"));
        // Kein Formular, das nichts abzuschicken hätte.
        $response->assertDontSee('name="key"', false);
    }

    /** Wer schon einen Schlüssel hat, landet in seinem Konto. */
    public function testAVisitorWithAKeyIsSentToTheirAccount(): void
    {
        $this->keyserverIssues();

        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/schluessel-erstellen")
            ->assertRedirectContains("/keys/key/enter");
    }

    /**
     * Die Callback-Marker der MetaGer-App. Sie kommen als Query an und müssen
     * als verstecktes Feld weiter — ohne sie kommt der Schlüssel nie in der App
     * an, und niemand sieht, warum (docs/10-open-decisions.md#d52 in app-en).
     */
    public function testTheAppCallbackMarkersSurviveTheForm(): void
    {
        $this->keyserverIssues();

        $response = $this
            ->get("/de-DE/schluessel-erstellen?keystore=development&variant=fdroid")
            ->assertOk();

        $response->assertSee('<input type="hidden" name="keystore" value="development">', false);
        $response->assertSee('<input type="hidden" name="variant" value="fdroid">', false);
        // Und im Weg zur Anmeldung, denn wer hier abbiegt, ist derselbe Besuch.
        $response->assertSee("keystore=development", false);
    }

    public function testAPageWithNothingToPassThroughCarriesNothing(): void
    {
        $this->keyserverIssues();

        $response = $this->get("/de-DE/schluessel-erstellen")->assertOk();

        $response->assertDontSee('name="keystore"', false);
        $response->assertDontSee('name="variant"', false);
    }

    /**
     * Das Lesezeichen trägt den Schlüssel *und* die Einstellungen dieses
     * Browsers — es richtet ihn andernorts wieder so ein, wie er hier ist.
     */
    public function testTheBookmarkCarriesTheSettingsOfThisBrowser(): void
    {
        $this->keyserverIssues();

        $response = $this
            ->withUnencryptedCookie("dark_mode", "2")
            ->get("/de-DE/schluessel-erstellen")
            ->assertOk();

        $response->assertSee("load-settings?key=" . self::A_KEY, false);
        $response->assertSee("dark_mode=2", false);
    }

    /**
     * Der QR-Code trägt nur den Schlüssel.
     *
     * Geprüft wird das Bild selbst, weil es die einzige Stelle ist, an der es
     * sichtbar wird: ein QR-Code, der die Einstellungen mitträgt, ist ein
     * gültiger QR-Code — nur einer, der mit jeder abgewählten Suchmaschine
     * dichter wird, bis ihn keine Handykamera mehr vom Bildschirm liest.
     * Niemand bemerkt das hier; jemand bemerkt es ein Jahr später.
     */
    public function testTheQrCodeCarriesOnlyTheKey(): void
    {
        $this->keyserverIssues();

        $expected = Builder::create()
            ->data(config("app.url") . "/de-DE/meta/settings/load-settings?key=" . self::A_KEY)
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->build()
            ->getDataUri();

        $this
            // Eine Einstellung, die im Lesezeichen steht und im Bild nicht
            // stehen darf.
            ->withUnencryptedCookie("dark_mode", "2")
            ->get("/de-DE/schluessel-erstellen")
            ->assertOk()
            ->assertSee($expected, false);
    }

    /**
     * Das Bild wird auch zum Herunterladen angeboten.
     *
     * Die Anmeldeseite fragt nach „der Sicherungsdatei mit dem QR-Code, die Sie
     * beim Einrichten gespeichert haben“ — angeboten hat sie beim Einrichten
     * vorher nie jemand.
     */
    public function testTheQrCodeCanBeSaved(): void
    {
        $this->keyserverIssues();

        $this->get("/de-DE/schluessel-erstellen")
            ->assertOk()
            ->assertSee('download="metager-schluessel.png"', false);
    }

    /**
     * Der Weg zur Anmeldung steht auf der Seite.
     *
     * Der häufigste Supportfall: jemand verliert sein Cookie, landet hier,
     * erstellt einen zweiten Schlüssel und sucht dann sein Guthaben. Das alte
     * bleibt am alten Schlüssel, und niemand kann es hinüberbuchen.
     */
    public function testThePageOffersLoggingInInstead(): void
    {
        $this->keyserverIssues();

        $this->get("/de-DE/schluessel-erstellen")
            ->assertOk()
            ->assertSeeText(__("key-create.existing.action"))
            // Nur der Pfad: KeymanagerLinks::login() baut wie überall sonst
            // einen vollständigen URL, und dessen Rechnername ist in Tests
            // config("app.url") und nicht localhost.
            ->assertSee('/de-DE/anmelden"', false);
    }

    /** Jeder Aufruf fragt den Keyserver — und deshalb gibt es eine Bremse. */
    public function testTooManyRequestsFromOneAddressStopAskingTheKeyserver(): void
    {
        for ($i = 0; $i < 60; $i++) {
            RateLimiter::hit("key-create:127.0.0.1", 300);
        }

        // Kein Fake für key/new: wird er trotzdem gefragt, scheitert der Test
        // an der unerwarteten Anfrage statt an einer Zusicherung.
        Http::preventStrayRequests();

        $this->get("/de-DE/schluessel-erstellen")
            ->assertOk()
            ->assertSeeText(__("key-create.errors.too_many_attempts"));
    }

    /** Auf der Seite steht ein Schlüssel. Sie gehört in keinen Cache. */
    public function testThePageIsNeverStored(): void
    {
        $this->keyserverIssues();

        $this->get("/de-DE/schluessel-erstellen")
            ->assertOk()
            ->assertHeader("Cache-Control", "no-store, private");
    }
}
