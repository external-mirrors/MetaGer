<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Die Anmeldeseite, /anmelden.
 *
 * Sie ist die erste umgezogene Seite, die nicht nur Text ist, und damit die
 * erste, bei der ein Umzugsfehler nicht wie ein Textfehler aussieht, sondern
 * wie ein Konto, in das niemand mehr hineinkommt. Vier Dinge müssen stimmen,
 * und keines davon fällt beim Ansehen der Seite auf:
 *
 *   - **Wohin das Formular abschickt.** Der Anmeldecode steht in Redis des
 *     Keymanagers, ein Gutscheincode wird dort normalisiert, und der QR-Code in
 *     einer hochgeladenen Datei wird dort gelesen. Die Seite ist hier, der
 *     Vorgang ist es nicht.
 *   - **Was sie mitschickt.** `redirect_error` ist der Weg zurück auf diese
 *     Seite — ohne ihn landet ein Tippfehler auf einer Vorlage, die es im
 *     anderen Repository nicht mehr gibt. `redirect_success` und die beiden
 *     Callback-Marker der MetaGer-App müssen einen Fehlversuch überleben.
 *   - **Wer sie gar nicht sehen soll.** Mit Schlüssel ist das Ziel das Konto,
 *     nicht das Formular.
 *   - **Dass sie nirgends zwischengespeichert wird.** Im Fehlerfall steht in
 *     ihrer Query, was jemand eben eingetippt hat.
 */
class LoginPageTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    public function testThePageRenders(): void
    {
        $this->get("/de-DE/anmelden")
            ->assertOk()
            ->assertSeeText(__("login.heading"))
            ->assertSeeText(__("login.key.label"));
    }

    /**
     * Das Formular schickt an den Keymanager ab. Siehe
     * App\Landing\KeymanagerLinks::submitKey() für die drei Gründe.
     */
    public function testTheFormPostsToTheKeymanager(): void
    {
        $response = $this->get("/de-DE/anmelden")->assertOk();

        // Nicht url(): unter einem de-DE-Request setzt URL::formatPathUsing das
        // Präfix noch einmal davor. Der Pfad ist die Aussage.
        $response->assertSee('/de-DE/keys/key/enter"', false);
        $response->assertSee('method="post"', false);
        // Ohne das kommt die Sicherungsdatei nie an: ein Formular ohne
        // multipart schickt nur den Dateinamen mit.
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    /**
     * Der Rückweg. routes/key.js im Keymanager nimmt den Wert nur an, wenn
     * dessen Host der der Anfrage ist — ein Pfad allein wird dort verworfen,
     * und der Besucher landet auf der Rückfallseite statt auf der, von der er
     * kam.
     */
    public function testTheFormCarriesAnAbsoluteWayBackToItself(): void
    {
        $response = $this->get("/de-DE/anmelden")->assertOk();

        $response->assertSee(
            '<input type="hidden" name="redirect_error" value="'
            . config("app.url") . '/de-DE/anmelden">',
            false
        );
    }

    /** Und zwar in der Sprache, in der der Besucher steht. */
    public function testTheWayBackKeepsTheLocale(): void
    {
        $this->get("/ca-ES/anmelden")
            ->assertOk()
            ->assertSee(
                'name="redirect_error" value="' . config("app.url") . '/ca-ES/anmelden"',
                false
            );
    }

    /**
     * Die Callback-Marker der MetaGer-App. Sie kommen als Query an, gehen als
     * verstecktes Feld weiter — und stehen zusätzlich im Rückweg, damit sie
     * einen Fehlversuch überleben. Ohne sie kommt der Schlüssel nie in der App
     * an, und niemand sieht, warum (docs/10-open-decisions.md#d52 in app-en).
     */
    public function testTheAppCallbackMarkersSurviveTheForm(): void
    {
        $response = $this->get("/de-DE/anmelden?keystore=development&variant=fdroid")->assertOk();

        $response->assertSee('<input type="hidden" name="keystore" value="development">', false);
        $response->assertSee('<input type="hidden" name="variant" value="fdroid">', false);
        $response->assertSee("keystore=development", false);
        $response->assertSee("variant=fdroid", false);
    }

    /**
     * Wohin es nach dem Anmelden geht. Die Startseite verlinkt hierher mit
     * genau diesem Parameter (parts/landing/hero.blade.php), und der Vorgang,
     * der ihn einlöst, ist der Keymanager — diese Seite kann ihn nur
     * weiterreichen.
     */
    public function testTheSuccessTargetIsPassedThrough(): void
    {
        $target = config("app.url") . "/de-DE/";
        $response = $this->get("/de-DE/anmelden?redirect_success=" . urlencode($target))->assertOk();

        $response->assertSee(
            '<input type="hidden" name="redirect_success" value="' . $target . '">',
            false
        );
        // Auch im Rückweg, sonst verliert genau der Besucher sein Ziel, der
        // sich beim ersten Mal vertippt hat.
        $response->assertSee("redirect_success=", false);
    }

    public function testAFormWithNothingToPassThroughCarriesNothing(): void
    {
        $response = $this->get("/de-DE/anmelden")->assertOk();

        $response->assertDontSee('name="redirect_success"', false);
        $response->assertDontSee('name="keystore"', false);
        $response->assertDontSee('name="variant"', false);
    }

    /**
     * Wer schon angemeldet ist, will sein Konto und nicht das Formular.
     *
     * Über /keys/key/enter und nicht direkt auf /keys/key/<uuid>: im Cookie
     * kann ein alter, nicht-UUID-förmiger Schlüssel stehen, und nur der
     * Keymanager kann den auf das Konto abbilden, zu dem er gehört.
     */
    public function testAVisitorWithAKeyGoesToTheirAccount(): void
    {
        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/anmelden")
            ->assertRedirectContains("/de-DE/keys/key/enter");
    }

    /** Auch der Schlüssel aus der Query — ein gespeicherter Anmelde-URL. */
    public function testAKeyInTheQueryIsAlsoALogin(): void
    {
        $this->get("/de-DE/anmelden?key=" . self::A_KEY)
            ->assertRedirectContains("/de-DE/keys/key/enter");
    }

    /** Und die Callback-Marker gehen dabei nicht verloren. */
    public function testTheRedirectToTheAccountKeepsTheAppCallback(): void
    {
        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/anmelden?keystore=release&variant=playstore")
            ->assertRedirectContains("/de-DE/keys/key/enter?keystore=release&variant=playstore");
    }

    /**
     * Ein abgewiesener Versuch. Der Keymanager schickt einen Code zurück,
     * diese Seite benennt ihn — vorher standen diese Meldungen als englische
     * Zeichenketten in routes/key.js und waren in keiner Sprache übersetzt.
     */
    public function testARejectedAttemptIsNamed(): void
    {
        $this->get("/de-DE/anmelden?key_error=invalid_login_code")
            ->assertOk()
            ->assertSeeText(__("login.errors.invalid_login_code"));
    }

    /**
     * Und zwar in der Sprache des Besuchers — vorher standen diese Meldungen
     * als englische Zeichenketten im Router des Keymanagers.
     *
     * Eigene Methode und nicht zwei Anfragen in einer: ResolveLocale schneidet
     * das Sprachpräfix aus der Anfrage, und in einem Testlauf trifft nur die
     * erste Anfrage die Routen-Tabelle. Eine zweite kommt als 404 zurück, egal
     * welche Sprache.
     */
    public function testARejectedAttemptIsNamedInTheVisitorsLanguage(): void
    {
        $this->get("/ca-ES/anmelden?key_error=invalid_login_code")
            ->assertOk()
            ->assertSeeText(__("login.errors.invalid_login_code", [], "ca"));
    }

    /**
     * Der Code kommt aus der Query und wird zu einem Übersetzungsschlüssel.
     * Ohne Aufzählung wäre `?key_error=irgendwas` ein Weg, „login.errors.
     * irgendwas“ nachzuschlagen — Laravel gibt dann den Schlüssel selbst aus,
     * und auf der Seite stünde fremder Text.
     */
    public function testAnUnknownErrorCodeIsNotPutOnThePage(): void
    {
        // Als Text und nicht als Zeichenkette in der Ausgabe geprüft: die
        // Abmelden-Verknüpfung in der Seitenleiste trägt den aktuellen URL
        // mitsamt Query als Parameter, dort steht der Code also so oder so.
        $this->get("/de-DE/anmelden?key_error=login.errors")
            ->assertOk()
            ->assertDontSeeText("login.errors");
    }

    /**
     * Der Tippfehler steht wieder im Feld. Ihn nur in einer Meldung zu zeigen
     * hieße, dass ein Besucher 36 Zeichen neu abtippt, um ein Zeichen zu
     * ändern.
     */
    public function testTheRejectedInputComesBackIntoTheField(): void
    {
        $this->get("/de-DE/anmelden?key_error=invalid_key&invalid_key=5e9c1a2b-4f6d")
            ->assertOk()
            ->assertSee('value="5e9c1a2b-4f6d"', false);
    }

    /**
     * Aber nur so viel davon, wie eine Eingabe sein kann. Der Wert kommt aus
     * der Query und landet im Feld; ein Kilobyte davon ist keine Eingabe, die
     * jemand korrigieren will.
     */
    public function testTheRejectedInputIsCutToSomethingAPersonCouldHaveTyped(): void
    {
        $response = $this->get("/de-DE/anmelden?invalid_key=" . str_repeat("a", 500))->assertOk();

        // Am Feld gemessen und nicht an der Seite: die Abmelden-Verknüpfung in
        // der Seitenleiste trägt den aktuellen URL mitsamt Query, dort stehen
        // alle 500 Zeichen weiterhin — sie stehen nur nicht im Eingabefeld.
        preg_match('/id="login-key"[^>]*value="([^"]*)"/', $response->getContent(), $field);

        $this->assertNotEmpty($field, "Das Schlüsselfeld hat keinen value mehr.");
        $this->assertSame(str_repeat("a", 64), $field[1]);
    }

    /**
     * Nichts davon in einen Cache. Die Seite hängt am Schlüssel-Cookie, und im
     * Fehlerfall steht in ihrer Query, was jemand eben eingetippt hat.
     */
    public function testThePageIsNeverStored(): void
    {
        $this->get("/de-DE/anmelden")
            ->assertOk()
            ->assertHeader("Cache-Control", "no-store, private");
    }

    /**
     * Der Kamera-Scanner ist die einzige Bedienung, die ohne Javascript nicht
     * geht. Er steht deshalb `hidden` in der Auslieferung, und
     * resources/js/login.js deckt ihn auf — angeboten und dann tatenlos wäre
     * schlechter als nicht angeboten.
     */
    public function testTheCameraScannerIsNotOfferedBeforeJavascriptRuns(): void
    {
        $this->get("/de-DE/anmelden")
            ->assertOk()
            ->assertSee('id="login-qr" hidden', false);
    }

    /**
     * Der Erweiterungs-Kasten trägt die id, an der die Web-Erweiterung ihn
     * entfernt, wenn sie schon installiert ist
     * (build/js/contentScripts/removeUnusedContent.js im Erweiterungs-Repo).
     */
    public function testTheExtensionPanelKeepsTheHookTheExtensionRemovesItBy(): void
    {
        $this->get("/de-DE/anmelden")
            ->assertOk()
            ->assertSee('id="plugin-btn"', false);
    }

    /**
     * Der Knopf nennt den Browser, in dem die Seite steht. Die alte Seite bot
     * drei Knöpfe mit drei Markenlogos an, von denen für jeden Besucher
     * höchstens einer je funktioniert hat.
     */
    public function testTheExtensionButtonNamesTheBrowserItIsBeingReadIn(): void
    {
        $firefox = "Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0";

        $this->withHeader("User-Agent", $firefox)
            ->get("/de-DE/anmelden")
            ->assertOk()
            ->assertSeeText(__("login.extension.install", ["browser" => "Firefox"]))
            ->assertSee("addons.mozilla.org", false);
    }

    /**
     * Und führt auf MetaGers eigene Plugin-Seite, wenn es für diesen Browser
     * keine Erweiterung gibt. Ein Store-Link ins Leere wäre schlechter als
     * eine Seite, die die Frage für jeden Browser beantwortet.
     */
    public function testAnUnknownBrowserIsSentToThePluginPageInstead(): void
    {
        $safari = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 "
            . "(KHTML, like Gecko) Version/17.4 Safari/605.1.15";

        $this->withHeader("User-Agent", $safari)
            ->get("/de-DE/anmelden")
            ->assertOk()
            ->assertSeeText(__("login.extension.install_generic"))
            ->assertSee('/de-DE/plugin"', false);
    }

    /**
     * Die Seitenleiste führt einen abgemeldeten Besucher hierher und einen
     * angemeldeten in sein Konto. Das ist der Unterschied zwischen
     * KeymanagerLinks::login() und ::dashboard(), und er ist von außen nur an
     * diesen beiden Links zu sehen.
     */
    public function testTheMenuSendsASignedOutVisitorHere(): void
    {
        $this->get("/de-DE/")
            ->assertOk()
            ->assertSee('/de-DE/anmelden"', false);
    }
}
