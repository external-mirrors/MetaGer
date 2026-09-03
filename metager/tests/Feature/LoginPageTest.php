<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Die Anmeldeseite, /anmelden.
 *
 * Die einzige umgezogene Seite, die nicht nur Text ist, und damit die einzige,
 * bei der ein Umzugsfehler nicht wie ein Textfehler aussieht, sondern wie ein
 * Konto, in das niemand mehr hineinkommt. Drei Dinge müssen stimmen, und
 * keines davon fällt beim Ansehen der Seite auf:
 *
 *   - **Wohin das Formular abschickt.** Auf sich selbst, seit der Vorgang
 *     mitgezogen ist; nur die Frage, was eine Eingabe ist, geht noch an den
 *     Keyserver. Der Vorgang selbst steht in LoginSubmitTest.
 *   - **Was sie mitschickt.** `redirect_success` und die beiden Callback-Marker
 *     der MetaGer-App müssen einen Fehlversuch überleben — es gibt keine
 *     Session, in der sie sonst stehen könnten.
 *   - **Wer sie gar nicht sehen soll.** Mit Schlüssel ist das Ziel das Konto,
 *     nicht das Formular.
 *
 * Und dass sie nirgends zwischengespeichert wird: im Fehlerfall steht in ihrer
 * Query, was jemand eben eingetippt hat.
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

    /** Das Formular schickt auf die Seite zurück, auf der es steht. */
    public function testTheFormPostsToItself(): void
    {
        $response = $this->get("/de-DE/anmelden")->assertOk();

        // Nicht url(): unter einem de-DE-Request setzt URL::formatPathUsing das
        // Präfix noch einmal davor. Der Pfad ist die Aussage.
        $response->assertSee('action="/de-DE/anmelden"', false);
        $response->assertSee('method="post"', false);
        // Ohne das kommt die Sicherungsdatei nie an: ein Formular ohne
        // multipart schickt nur den Dateinamen mit.
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    /** Und zwar in der Sprache, in der der Besucher steht. */
    public function testTheFormPostsToItselfInTheVisitorsLanguage(): void
    {
        $this->get("/ca-ES/anmelden")
            ->assertOk()
            ->assertSee('action="/ca-ES/anmelden"', false);
    }

    /**
     * Ein `redirect_error` steht nicht mehr im Formular.
     *
     * Er war die Naht zum Keymanager: der brauchte einen absoluten URL, um zu
     * wissen, wohin ein Fehlversuch zurückgehört, und prüfte ihn gegen den Host
     * der Anfrage, weil er sonst eine offene Weiterleitung mit dem eingegebenen
     * Schlüssel in der Query gewesen wäre. Beides entfällt, wenn das Ziel eines
     * Fehlversuchs die Seite selbst ist — und es soll nicht als totes
     * verstecktes Feld liegen bleiben, das noch aussieht, als würde es gelesen.
     */
    public function testTheFormNoLongerCarriesAWayBack(): void
    {
        $this->get("/de-DE/anmelden")
            ->assertOk()
            ->assertDontSee("redirect_error", false);
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
     * Auf /konto, das den Schlüssel aus dem Cookie liest. Steht er statt dessen
     * in der Query, reist er als Parameter mit — sonst fände das Konto ihn
     * nicht und schickte den Besucher hierher zurück
     * ({@see \App\Landing\KeymanagerLinks::accountForVisitor()}).
     */
    public function testAVisitorWithAKeyGoesToTheirAccount(): void
    {
        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/anmelden")
            ->assertRedirectContains("/de-DE/konto");
    }

    /** Auch der Schlüssel aus der Query — ein gespeicherter Anmelde-URL. */
    public function testAKeyInTheQueryIsAlsoALogin(): void
    {
        $this->get("/de-DE/anmelden?key=" . self::A_KEY)
            ->assertRedirectContains("/de-DE/konto");
    }

    /** Und die Callback-Marker gehen dabei nicht verloren. */
    public function testTheRedirectToTheAccountKeepsTheAppCallback(): void
    {
        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/anmelden?keystore=release&variant=playstore")
            ->assertRedirectContains("/de-DE/konto?keystore=release&variant=playstore");
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
     * Das Kürzel neben dem Guthaben ist kein Schlüssel, und wer es abtippt,
     * bekommt das gesagt.
     *
     * Der Zusammenfall ist vollständig: getKeyFingerprint() sind die letzten
     * sechs Zeichen des Schlüssels, also immer [0-9a-f]{6}, und sechs Zeichen
     * nahm das Anmeldeformular als alten Schlüssel an. Der Keymanager weist das
     * jetzt ab (resolve_legacy_short_key in pass/routes/key.js), statt daraus
     * per MD5 ein leeres Phantomkonto zu falten — hier steht nur, dass diese
     * Seite den Code auch benennen kann.
     */
    public function testTheKeyMarkIsNotAKeyAndSaysSo(): void
    {
        $this->get("/de-DE/anmelden?key_error=key_mark&invalid_key=3f9a1c")
            ->assertOk()
            ->assertSeeText(__("login.errors.key_mark"));
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
     * Das Schlüsselfeld lädt einen Passwortspeicher ein, den Schlüssel zu
     * merken.
     *
     * Das alte Feld im Keymanager war type="password", also bot jeder
     * Browser und jeder Passwortspeicher von sich aus an, den Schlüssel
     * abzulegen und wieder einzusetzen. Der Neubau kam als type="text" mit
     * autocomplete="off" — und ein Nutzer meldete genau das als Verlust:
     * „wo speichere ich den Schlüssel wieder einmalig ab“. autocomplete=
     * "current-password" ist der Token, der das zurückholt; dass ein Browser
     * daraufhin auch tatsächlich fragt, ist Browserverhalten und hier nicht
     * prüfbar — diese Zusicherung hält nur die Zeile fest, die es ermöglicht,
     * gegen ein erneutes autocomplete="off".
     */
    public function testTheKeyFieldInvitesAPasswordManager(): void
    {
        $response = $this->get("/de-DE/anmelden")->assertOk();

        preg_match('/<input[^>]*id="login-key"[^>]*>/', $response->getContent(), $field);
        $this->assertNotEmpty($field, "Das Schlüsselfeld steht nicht mehr auf der Seite.");

        // type="text" bleibt: ohne Javascript sieht ein Besucher nur so, was er
        // tippt. resources/js/login/maskKeyField.js macht daraus type="password".
        $this->assertStringContainsString('type="text"', $field[0]);
        $this->assertStringContainsString('autocomplete="current-password"', $field[0]);
        $this->assertStringNotContainsString('autocomplete="off"', $field[0]);
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
