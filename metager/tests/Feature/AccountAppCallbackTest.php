<?php

namespace Tests\Feature;

use App\Landing\AppCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Der Rückweg in die MetaGer-App (docs/10-open-decisions.md#d52 und #d55 im
 * Repository app-en).
 *
 * Lag in pass/routes/key.js, zusammen mit dem Konto, und ist mit ihm hierher
 * gezogen. Gepinnt, obwohl es wenige Zeilen sind: zwischen ihnen entscheidet
 * sich, welche Anwendung den Schlüssel eines Benutzers bekommt.
 *
 *   - `keystore` wählt den *Host*. Falsch gewählt geht der Schlüssel eines
 *     echten Benutzers an eine Domain, die ein Signaturzertifikat beglaubigt,
 *     dessen privater Schlüssel öffentlich im App-Repository liegt.
 *   - `variant` steht unmittelbar neben `?key=` im *Pfad*. Ungeprüft ist das
 *     eine offene Weiterleitung, die eine Zugangsberechtigung mitgibt.
 *
 * Der eine Fall, den nichts hiervon abdeckt, ist ein echtes Telefon: ob Android
 * den App Link überhaupt verifiziert, entscheidet die assetlinks.json (siehe
 * routes/web.php) und nicht diese Anwendung.
 */
class AccountAppCallbackTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    private function production(): string
    {
        return config("metager.metager.app.callback_url");
    }

    private function development(): string
    {
        return config("metager.metager.app.callback_dev_url");
    }

    // ── handbackUrl ──────────────────────────────────────────────────────────

    /**
     * Ein Debug-Build bekommt die Entwicklungsadresse, niemals die Produktion.
     *
     * `debug.keystore` liegt im offenen App-Repository; nur metager3.de darf
     * dieses Zertifikat beglaubigen.
     */
    public function testADebugBuildsKeyGoesToTheDevelopmentHost(): void
    {
        $this->assertSame(
            $this->development() . "/app/callback/playstore?key=the-key",
            AppCallback::handbackUrl("the-key", "development", "playstore", false)
        );
    }

    public function testAReleaseBuildsKeyGoesToProduction(): void
    {
        $this->assertSame(
            $this->production() . "/app/callback/fdroid?key=the-key",
            AppCallback::handbackUrl("the-key", "release", "fdroid", false)
        );
    }

    /**
     * Der Kanal steht im Pfad, damit ein Host mit mehreren installierten Apps
     * den Rückruf der richtigen zustellt.
     */
    public function testTheChannelIsNamedInThePath(): void
    {
        foreach (["playstore", "manual", "fdroid"] as $variant) {
            $this->assertStringContainsString(
                "/app/callback/" . $variant . "?",
                AppCallback::handbackUrl("the-key", "release", $variant, false)
            );
        }
    }

    /**
     * Ein unbekannter Kanal fällt auf den festen Präfix zurück, statt in den
     * Pfad zu geraten.
     *
     * Zurückgefallen und nicht abgewiesen: ein App-Build von vor der Einführung
     * von `variant` schickt keinen mit.
     */
    public function testAnUnknownChannelNeverReachesThePath(): void
    {
        foreach ([null, "", "../../evil.example", "playstore/../.."] as $variant) {
            $this->assertSame(
                $this->production() . "/app/callback?key=the-key",
                AppCallback::handbackUrl("the-key", "release", $variant, false)
            );
        }
    }

    public function testAKeyThatIsNotUrlSafeIsEncoded(): void
    {
        $this->assertSame(
            $this->production() . "/app/callback/manual?key=a+b%2Fc",
            AppCallback::handbackUrl("a b/c", "release", "manual", false)
        );
    }

    /**
     * `flow=charge` sagt der App, dass der Schlüssel noch nichts bezahlen kann.
     *
     * Sie setzt den Benutzer dann gleich wieder auf den Aufladen-Abschnitt,
     * statt den Custom Tab nur zu schließen — Anmelden ist bei einer frischen
     * Installation nur die halbe Miete.
     */
    public function testAnEmptyKeyTellsTheAppToOpenOnTopUp(): void
    {
        $this->assertStringEndsWith(
            "?key=the-key&flow=charge",
            AppCallback::handbackUrl("the-key", "release", "playstore", true)
        );
        $this->assertStringNotContainsString(
            "flow=",
            AppCallback::handbackUrl("the-key", "release", "playstore", false)
        );
    }

    // ── isHandback ───────────────────────────────────────────────────────────

    public function testOnlyARecognizedKeystoreTriggersTheHandback(): void
    {
        $this->assertTrue(AppCallback::isHandback(Request::create("/konto?keystore=release")));
        $this->assertTrue(AppCallback::isHandback(Request::create("/konto?keystore=development")));
        $this->assertFalse(AppCallback::isHandback(Request::create("/konto")));
        $this->assertFalse(AppCallback::isHandback(Request::create("/konto?keystore=not-a-keystore")));
    }

    // ── Die Seite ────────────────────────────────────────────────────────────

    /** Der Keyserver kennt diesen Schlüssel. */
    private function keyserverKnows(float $charge): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500],
            ]),
            "*" => Http::response([
                "key" => self::A_KEY,
                "charge" => $charge,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => $charge, "expiration" => "2027-03-14 00:00:00"]],
            ]),
        ]);
    }

    /**
     * Das Konto gibt den Schlüssel zurück, statt eine Seite zu rendern.
     *
     * Genau das tat vorher /keys/key/<uuid>: das Erreichen des angemeldeten
     * Kontos *ist* das Signal, dass die Anmeldung fertig ist.
     */
    public function testTheAccountHandsTheKeyBackInsteadOfRendering(): void
    {
        $this->keyserverKnows(248);

        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/konto?keystore=release&variant=playstore")
            ->assertRedirect($this->production() . "/app/callback/playstore?key=" . self::A_KEY);
    }

    /** Ein leerer Schlüssel schickt die App gleich weiter zum Aufladen. */
    public function testAnEmptyKeyIsHandedBackWithTheChargeFlow(): void
    {
        $this->keyserverKnows(0);

        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/konto?keystore=release")
            ->assertRedirect($this->production() . "/app/callback?key=" . self::A_KEY . "&flow=charge");
    }

    /** Ein gewöhnlicher Browserbesuch bekommt seine Seite. */
    public function testAnOrdinaryVisitRendersTheAccount(): void
    {
        $this->keyserverKnows(248);

        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/konto")
            ->assertOk();
    }

    /**
     * Die Marker überleben den Schritt, in dem das Cookie gesetzt wird — und
     * der Schlüssel bleibt jetzt absichtlich mit auf diesem einen Sprung.
     *
     * Der gefährlichste Punkt der ganzen Kette: /keys/key/enter leitet mit
     * `?key=` hierher, diese Seite setzt das Cookie und leitet auf sich selbst
     * weiter — und genau dort gingen die Marker verloren, wenn niemand sie
     * mitnimmt. Der Schlüssel käme dann nie in der App an, und niemand sähe,
     * warum.
     *
     * Früher nahm dieser Sprung den Schlüssel dabei auch aus der Adresse: das
     * Cookie in derselben Antwort machte das überflüssig, solange der Browser
     * es behielt. Für einen Besucher, dessen Browser das nicht tut, wäre genau
     * das der Moment gewesen, an dem die Anmeldung verlorenging — die zweite
     * Anfrage hätte weder Cookie noch Query gehabt. `key` und der einmalige
     * `key_check`-Marker reisen jetzt deshalb mit; siehe
     * App\Authentication\CookieSupport.
     */
    public function testTheMarkersAndKeyRideTogetherOnThisOneRedirect(): void
    {
        $this->keyserverKnows(248);

        $response = $this->get("/de-DE/konto?key=" . self::A_KEY . "&keystore=release&variant=fdroid");

        $response->assertRedirectContains("keystore=release");
        $response->assertRedirectContains("variant=fdroid");
        $response->assertRedirectContains("/de-DE/konto");
        $response->assertRedirectContains("key=" . self::A_KEY);
        $response->assertRedirectContains("key_check=1");
    }
}
