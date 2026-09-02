<?php

namespace Tests\Feature;

use App\Authentication\KeyResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Der Anmeldevorgang, POST /anmelden.
 *
 * Er lag im Keymanager und ist mitgezogen. Was dort blieb, ist eine einzige
 * Frage über die API — was eine Eingabe ist —, und die wird hier gefälscht:
 * geprüft wird, was diese Seite mit der Antwort *macht*. Wie der Keyserver zu
 * seiner Antwort kommt, steht in dessen eigener Suite
 * (pass/test/key_resolve.test.js).
 *
 * Der Grund für den Umzug ist der Punkt, den mehrere dieser Tests festhalten:
 * solange der Keymanager das Cookie setzte, musste er den Besucher
 * zurückreichen, und der Schlüssel reiste dafür als `?key=` durch die
 * Adresszeile — in den Verlauf, in jeden Referer der nächsten Seite, und ohne
 * Javascript blieb er dort stehen. Das Cookie hier zu setzen macht das für
 * jeden überflüssig, dessen Browser es behält — und für einen Besucher, dessen
 * Browser es nicht tut, reist der Schlüssel absichtlich weiterhin mit, siehe
 * App\Authentication\CookieSupport.
 */
class LoginSubmitTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        // Sonst zählt der vorige Test in den nächsten hinein: die Bremse zählt
        // pro Adresse, und in Tests ist das immer dieselbe.
        RateLimiter::clear("login:127.0.0.1");
    }

    /** Der Keyserver antwortet so, wie der Test es braucht. */
    private function keyserverAnswers(array $answer, int $status = 200): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/resolve*" => Http::response($answer, $status),
            // KeyAuthGuard liest `key` auch aus dem Body, also ist die Anfrage,
            // die gerade anmeldet, für sich genommen schon angemeldet — und
            // alles, was danach gerendert wird, fragt den Keyserver nach ihr.
            "*" => Http::response(["key" => self::A_KEY, "charge" => 0]),
        ]);
    }

    /** Ein Formular, so wie der Browser es abschickt. */
    private function submit(array $fields = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge(["Origin" => config("app.url")], $headers))
            ->post("/de-DE/anmelden", $fields);
    }

    public function testAKeyIsAccepted(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $this->submit(["key" => self::A_KEY])
            ->assertRedirectContains("/de-DE/konto");
    }

    /**
     * Und das Cookie wird hier gesetzt. Das ist der ganze Umzug: vorher tat es
     * der Keymanager, und deshalb musste er den Besucher anschließend
     * zurückreichen.
     */
    public function testTheCookieIsSetOnTheWayOut(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $this->submit(["key" => self::A_KEY])
            // Unverschlüsselt: `key` steht in EncryptCookies::$except, weil der
            // Keymanager unter demselben Host es lesen können muss.
            ->assertCookie("key", self::A_KEY, false);
    }

    /**
     * Der Schlüssel und der Marker reisen auf genau diesem einen Sprung mit.
     *
     * Vorher hängte der Keymanager den Schlüssel als `?key=` an das
     * Rückkehrziel; das Cookie in derselben Antwort machte das überflüssig,
     * *solange der Browser es behielt*. Für einen Besucher, dessen Browser
     * das nicht tut, ist dieser eine Sprung die einzige Gelegenheit, ihn
     * trotzdem angemeldet auf die nächste Seite zu schicken — ohne ihn hier
     * wäre die nächste Anfrage weder per Cookie noch per Query angemeldet.
     * `key_check` reist mit, damit genau diese Seite (und nur sie) erkennt,
     * ob das Cookie hielt, und den Hinweis zeigt, wenn nicht — siehe
     * App\Authentication\CookieSupport.
     */
    public function testTheKeyAndTheOneTimeMarkerRideOnThisOneRedirect(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $response = $this->submit([
            "key" => self::A_KEY,
            "redirect_success" => config("app.url") . "/de-DE/",
        ]);

        $location = $response->headers->get("Location");
        $this->assertStringStartsWith(config("app.url") . "/de-DE/?", $location);
        $this->assertStringContainsString("key=" . self::A_KEY, $location);
        $this->assertStringContainsString("key_check=1", $location);
    }

    /** Ein fremdes Rückkehrziel wird nicht angenommen. */
    public function testAForeignReturnTargetIsIgnored(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $this->submit([
            "key" => self::A_KEY,
            "redirect_success" => "https://boese.example/",
        ])->assertRedirectContains("/de-DE/konto");
    }

    /**
     * Mit den Callback-Markern der App führt der Weg über das Konto im
     * Keymanager: dort steht die Weiche, die aus einer angemeldeten Sitzung
     * einen App-Link macht, und dort steht die Ladung, an der sie entscheidet,
     * ob die App gleich zum Aufladen weiterschickt
     * (docs/10-open-decisions.md#d52 und #d55 in app-en).
     */
    public function testTheAppCallbackGoesThroughTheDashboard(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $this->submit([
            "key" => self::A_KEY,
            "keystore" => "release",
            "variant" => "playstore",
            // Selbst wenn eines dabeisteht: die App hat Vorrang, sonst käme der
            // Schlüssel nie bei ihr an.
            "redirect_success" => config("app.url") . "/de-DE/",
        ])->assertRedirectContains("keystore=release&variant=playstore");
    }

    public function testARejectedKeyComesBackToTheForm(): void
    {
        $this->keyserverAnswers(["result" => "error", "error" => "invalid_key"]);

        $response = $this->submit(["key" => "nicht-echt"]);

        $response->assertStatus(303);
        $response->assertRedirectContains("key_error=invalid_key");
        // Damit ein Tippfehler nicht neu abgetippt werden muss.
        $response->assertRedirectContains("invalid_key=nicht-echt");
    }

    /** Und behält dabei, was der zweite Versuch braucht. */
    public function testARejectedKeyKeepsWhatASecondAttemptNeeds(): void
    {
        $this->keyserverAnswers(["result" => "error", "error" => "invalid_key"]);

        $this->submit([
            "key" => "nicht-echt",
            "keystore" => "release",
            "variant" => "playstore",
            "redirect_success" => config("app.url") . "/de-DE/",
        ])
            ->assertRedirectContains("keystore=release")
            ->assertRedirectContains("variant=playstore")
            ->assertRedirectContains("redirect_success=");
    }

    public function testNothingAtAllIsItsOwnAnswer(): void
    {
        Http::preventStrayRequests();

        $this->submit([])->assertRedirectContains("key_error=no_input");
    }

    /**
     * Ein Gutscheincode geht auf /c (App\Http\Controllers\VoucherController).
     * Die kennt die Kampagne, ihr Budget und ihre eigene Bremse; hier ist nur
     * bekannt, dass die Eingabe einer war.
     */
    public function testAVoucherGoesToTheVoucherPage(): void
    {
        $this->keyserverAnswers(["result" => "voucher", "code" => "ABCDEFGH1J"]);

        $this->submit(["key" => "abcd-efgh-ij"])
            ->assertRedirectContains("/de-DE/c/ABCDEFGH1J");
    }

    /**
     * Ein stummer Keyserver ist kein Urteil über den Schlüssel, und wird auch
     * nicht als eines ausgegeben.
     */
    public function testAnUnreachableKeyserverSaysSoRatherThanBlamingTheKey(): void
    {
        $this->keyserverAnswers(["error" => "boom"], 502);

        $this->submit(["key" => self::A_KEY])
            ->assertRedirectContains("key_error=keyserver_unreachable");
    }

    /** Und setzt vor allem kein Cookie. */
    public function testAnUnreachableKeyserverSignsNobodyIn(): void
    {
        $this->keyserverAnswers(["error" => "boom"], 502);

        $this->submit(["key" => self::A_KEY])->assertCookieMissing("key");
    }

    /**
     * Eine Antwort, die diese Seite nicht kennt, ist keine Antwort.
     *
     * Sie würde sonst als `key_error` in die Query wandern und dort zu einem
     * Übersetzungsschlüssel — die Aufzählung in LoginController fängt das ein
     * zweites Mal ab, aber hier ist die Stelle, an der es gar nicht erst
     * entsteht.
     */
    public function testAnAnswerThisSideDoesNotKnowIsNotAnAnswer(): void
    {
        $this->keyserverAnswers(["result" => "irgendwas"]);

        $this->submit(["key" => self::A_KEY])
            ->assertRedirectContains("key_error=keyserver_unreachable");
    }

    /**
     * Die Sicherungsdatei geht als Rohbytes an den Keyserver — Jimp liest den
     * QR-Code, PHP hat dafür nichts, dem man einen Schlüssel anvertrauen will.
     */
    public function testABackupFileIsSentOnToBeRead(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/resolve-image" => Http::response([
                "result" => "key",
                "key" => self::A_KEY,
            ]),
        ]);

        $this->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/anmelden", [
                // Mit Inhalt und nicht nur mit Größe: geprüft wird, dass genau
                // diese Bytes weitergereicht werden.
                "file" => UploadedFile::fake()->createWithContent("sicherung.png", "PNG-Bytes"),
            ])
            ->assertCookie("key", self::A_KEY, false);

        Http::assertSent(fn ($request) => str_contains($request->url(), "/key/resolve-image")
            && $request->body() === "PNG-Bytes");
    }

    /**
     * Ein fremdes Formular meldet niemanden an.
     *
     * Webrouten laufen ohne Session, es gibt also kein CSRF-Token — und für ein
     * Anmeldeformular ist das nicht gleichgültig: wer den Besucher an *seinem*
     * Schlüssel anmeldet, zahlt dessen Suchen und sieht sie im Konto. Der
     * Keymanager hatte diese Prüfung nicht.
     */
    public function testAForeignFormSignsNobodyIn(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $this->withHeaders(["Origin" => "https://boese.example"])
            ->post("/de-DE/anmelden", ["key" => self::A_KEY])
            ->assertForbidden();
    }

    /** Ohne Origin entscheidet Sec-Fetch-Site. */
    public function testACrossSiteFormSignsNobodyInEvenWithoutAnOrigin(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $this->withHeaders(["Sec-Fetch-Site" => "cross-site"])
            ->post("/de-DE/anmelden", ["key" => self::A_KEY])
            ->assertForbidden();
    }

    /**
     * Geraten wird nicht beliebig oft.
     *
     * Ein Anmeldecode ist sechs Ziffern und zehn Sekunden gültig; zu jedem
     * Zeitpunkt sind einige davon echt. POST /key/enter im Keymanager war
     * ungebremst, und die API dahinter ist es weiterhin — dort ist jeder Aufruf
     * derselbe Aufrufer. Wessen Browser fragt, weiß nur diese Seite.
     *
     * Die Versuche werden direkt gezählt und nicht abgeschickt: in einer
     * Testmethode trifft nur die *erste* Anfrage die Routen-Tabelle, weil
     * ResolveLocale das Sprachpräfix aus der Anfrage schneidet. Zwanzig echte
     * Versuche wären neunzehn 404er und ein grüner Test, der nichts geprüft hat.
     */
    public function testGuessingIsSlowedDown(): void
    {
        $this->keyserverAnswers(["result" => "error", "error" => "invalid_login_code"]);

        for ($i = 0; $i < 20; $i++) {
            RateLimiter::hit("login:127.0.0.1", 300);
        }

        $this->submit(["key" => "123456"])
            ->assertRedirectContains("key_error=too_many_attempts");
    }

    /** Und dann wird der Keyserver auch nicht mehr gefragt. */
    public function testANoLongerAcceptedAttemptIsNotEvenAsked(): void
    {
        $this->keyserverAnswers(["result" => "error", "error" => "invalid_login_code"]);

        for ($i = 0; $i < 20; $i++) {
            RateLimiter::hit("login:127.0.0.1", 300);
        }

        $this->submit(["key" => "123456"]);

        Http::assertNotSent(fn ($request) => str_contains($request->url(), "/key/resolve"));
    }

    /** Der Vorgang gehört so wenig in einen Cache wie die Seite. */
    public function testTheAnswerIsNeverStored(): void
    {
        $this->keyserverAnswers(["result" => "key", "key" => self::A_KEY]);

        $this->submit(["key" => self::A_KEY])
            ->assertHeader("Cache-Control", "no-store, private");
    }
}
