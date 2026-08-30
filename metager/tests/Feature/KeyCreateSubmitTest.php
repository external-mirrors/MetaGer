<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * „Ja, den nehme ich“ — POST /schluessel-erstellen.
 *
 * Der Schritt, für den der Umzug gemacht wurde. Vorher schickte das Formular an
 * den Keymanager, der leitete auf das Konto weiter, und *dort* wurde das Cookie
 * gesetzt; bis dahin musste der Schlüssel als `?key=` durch die Adresszeile
 * reisen. Die alte Seite tat es sogar zweimal — sie schrieb ihn zusätzlich per
 * `history.replaceState` selbst in den Verlauf.
 *
 * Hier wird das Cookie gesetzt, und deshalb steht der Schlüssel in keinem URL
 * mehr, den ein Besucher hinterlässt. Der eine URL, in dem er weiterhin steht,
 * ist das Ziel — das Konto beim Keymanager, das nach ihm benannt ist.
 */
class KeyCreateSubmitTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Http::fake([
            // KeyAuthGuard liest `key` auch aus dem Body: die Anfrage, die
            // gerade einen Schlüssel annimmt, ist für sich genommen schon
            // angemeldet, und was danach gerendert wird, fragt nach ihr.
            "*" => Http::response(["key" => self::A_KEY, "charge" => 0]),
        ]);
    }

    /** Ein Formular, so wie der Browser es abschickt. */
    private function submit(array $fields = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge(["Origin" => config("app.url")], $headers))
            ->post("/de-DE/schluessel-erstellen", $fields);
    }

    /** Der neue Schlüssel führt auf das Konto, und zwar zum Aufladen. */
    public function testTheKeyLeadsToItsAccount(): void
    {
        $this->submit(["key" => self::A_KEY])
            ->assertRedirectContains("/de-DE/keys/key/" . self::A_KEY);
    }

    /**
     * Und das Cookie wird hier gesetzt. Das ist der ganze Umzug: vorher tat es
     * das Konto, und der Schlüssel musste dafür bis dorthin durch die
     * Adresszeile reisen.
     */
    public function testTheCookieIsSetOnTheWayOut(): void
    {
        // Unverschlüsselt: `key` steht in EncryptCookies::$except, weil der
        // Keymanager unter demselben Host es lesen können muss.
        $this->submit(["key" => self::A_KEY])
            ->assertCookie("key", self::A_KEY, false);
    }

    /**
     * Angekommen wird im Abschnitt zum Aufladen.
     *
     * Ein neuer Schlüssel hat kein Guthaben, und ohne Guthaben findet er nichts
     * — das Konto oben, ohne den Anker, wäre eine Seite mit einer großen Null
     * und ohne nächsten Schritt.
     */
    public function testItLandsOnTheChargingStep(): void
    {
        $this->submit(["key" => self::A_KEY])
            ->assertRedirectContains("#charge");
    }

    /**
     * Die Callback-Marker der MetaGer-App reisen mit. Das Konto ist die Stelle,
     * an der ein Custom Tab den Schlüssel an die App zurückgibt; ohne die
     * Marker geschieht das nicht, und niemand sieht, warum.
     */
    public function testTheAppCallbackRidesAlong(): void
    {
        $this->submit([
            "key" => self::A_KEY,
            "keystore" => "development",
            "variant" => "fdroid",
        ])->assertRedirectContains("keystore=development");
    }

    /**
     * Ein fremdes Formular meldet niemanden an.
     *
     * Webrouten laufen ohne Session, es gibt also kein CSRF-Token. Ohne diese
     * Prüfung wäre das hier eine Möglichkeit, einem Besucher einen Schlüssel
     * unterzuschieben, den jemand anderes kennt — und von da an liest und zahlt
     * der mit.
     */
    public function testAForeignFormSetsNoCookie(): void
    {
        $this->submit(["key" => self::A_KEY], ["Origin" => "https://evil.example"])
            ->assertForbidden();
    }

    public function testACrossSiteFormIsRefusedEvenWithoutAnOrigin(): void
    {
        $this->withHeaders(["Sec-Fetch-Site" => "cross-site"])
            ->post("/de-DE/schluessel-erstellen", ["key" => self::A_KEY])
            ->assertForbidden();
    }

    /**
     * Was kein Schlüssel ist, wird keiner.
     *
     * Das versteckte Feld ist Eingabe wie jede andere, und was von hier ins
     * Cookie geht, ist von da an das Konto des Besuchers. Der Keyserver faltet
     * jede Nicht-UUID per MD5 in eine gültige — ohne diese Prüfung wäre jede
     * Zeichenfolge eine erfolgreiche Anmeldung an einem leeren Phantomkonto.
     */
    public function testSomethingThatIsNotAKeyComesBackToThePage(): void
    {
        $response = $this->submit(["key" => "nonsense"]);

        $response->assertRedirectContains("key_error=no_key");
        $response->assertCookieMissing("key");
    }

    public function testAnEmptyFormComesBackToThePage(): void
    {
        $this->submit()->assertRedirectContains("key_error=no_key");
    }

    /**
     * Auch der Rückweg verliert die Marker der App nicht — sonst verlöre genau
     * der Besuch sie, dem das versteckte Feld abhandengekommen ist.
     */
    public function testTheWayBackKeepsTheAppCallback(): void
    {
        $this->submit(["keystore" => "development", "variant" => "fdroid"])
            ->assertRedirectContains("keystore=development");
    }

    /** In der Antwort steht ein Schlüssel. Sie gehört in keinen Cache. */
    public function testTheAnswerIsNeverStored(): void
    {
        $this->submit(["key" => self::A_KEY])
            ->assertHeader("Cache-Control", "no-store, private");
    }
}
