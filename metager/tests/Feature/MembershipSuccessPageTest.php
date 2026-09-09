<?php

namespace Tests\Feature;

use App\Models\Membership\MembershipApplication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Die Seite nach dem abgeschickten Aufnahmeantrag.
 *
 * Sie ist für die meisten Mitglieder die *einzige* Gelegenheit, ihren Schlüssel
 * zu sehen. Er wird im ersten Schritt des Formulars still erstellt und per
 * Cookie gesetzt ({@see MembershipKeyTest}); genannt wird er sonst erst in der
 * Willkommensmail, und die geht raus, wenn der Antrag bearbeitet ist — Tage
 * später. Wessen Cookie in der Zwischenzeit verlorengeht, hat ohne diese Seite
 * nichts in der Hand.
 *
 * Vorher stand hier der Schlüssel in einem Feld und ein hartkodierter deutscher
 * Satz daneben. Jetzt derselbe Block wie auf /schluessel-erstellen
 * (resources/views/parts/key-backup.blade.php): QR-Code zum Abfotografieren und
 * Lesezeichen-URL, beides aus {@see \App\Authentication\KeyBackup}.
 */
class MembershipSuccessPageTest extends TestCase
{
    use DatabaseTransactions;

    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    /** Ein Antrag, wie er nach dem letzten Schritt dasteht. */
    private function finishedApplication(string $payment_method = "banktransfer"): MembershipApplication
    {
        $application = MembershipApplication::create([
            "locale" => "de-DE",
            "amount" => 5.0,
            "interval" => "monthly",
            "payment_method" => $payment_method,
            "key" => self::A_KEY,
        ]);
        $application->contact()->create([
            "title" => "Neutral",
            "first_name" => "Test",
            "last_name" => "Person",
            "email" => "test@example.com",
            "application_id" => $application->id,
        ]);

        return $application->fresh();
    }

    private function visit(MembershipApplication $application, array $query = []): \Illuminate\Testing\TestResponse
    {
        return $this->get("/de-DE/membership/success/" . $application->id . ($query === [] ? "" : "?" . http_build_query($query)));
    }

    // ── Der Schlüssel ────────────────────────────────────────────────────────

    public function testTheKeyIsShown(): void
    {
        $this->visit($this->finishedApplication())
            ->assertOk()
            ->assertSee(self::A_KEY, false);
    }

    /** Der QR-Code steht im Dokument selbst und lässt sich speichern. */
    public function testTheKeyCanBePhotographed(): void
    {
        $this->visit($this->finishedApplication())
            ->assertSee('src="data:image/png;base64,', false)
            ->assertSee('download="metager-schluessel.png"', false);
    }

    /** Und als Lesezeichen, das diesen Browser andernorts wieder einrichtet. */
    public function testTheKeyCanBeBookmarked(): void
    {
        $this->visit($this->finishedApplication())
            ->assertSee("load-settings?key=" . self::A_KEY, false);
    }

    /** Die Kennung, unter der das Konto von jetzt an wiedererkannt wird. */
    public function testTheAccountFingerprintIsShown(): void
    {
        $this->visit($this->finishedApplication())
            ->assertSeeText(strtoupper(substr(self::A_KEY, -6)));
    }

    /**
     * Ein Schlüssel gehört in keinen Cache — weder in einen gemeinsamen noch in
     * den des Browsers.
     */
    public function testThePageIsNotCached(): void
    {
        $this->visit($this->finishedApplication())
            ->assertHeader("Cache-Control", "no-store, private");
    }

    // ── Wie es weitergeht ────────────────────────────────────────────────────

    /**
     * Der Satz, den auch jemand mitnehmen muss, der die Seite gleich schließt:
     * der Antrag wird geprüft, und darauf folgt in jedem Fall eine Mail.
     */
    public function testThePageSaysTheApplicationWillBeReviewed(): void
    {
        $this->visit($this->finishedApplication())
            ->assertSeeText(__("membership.next.review"));
    }

    /**
     * Für die meisten ist der Aufnahmeantrag der erste Kontakt mit MetaGer, und
     * was sie als Nächstes wollen, ist suchen.
     */
    public function testAVisitorIsOfferedTheWayIntoTheSearch(): void
    {
        $this->visit($this->finishedApplication())
            ->assertSeeText(__("membership.next.search"));
    }

    /**
     * Aus dem Custom Tab der App heraus ist der Weg weiter ein anderer: der
     * verifizierte App Link, über den der Schlüssel zurückgeht. Ohne ihn hat die
     * App den Schlüssel nie gesehen — der Custom Tab bekommt das Cookie, die App
     * bekommt davon nichts mit.
     */
    public function testAnAppVisitorIsOfferedTheWayBackIntoTheApp(): void
    {
        $response = $this->visit($this->finishedApplication(), [
            "keystore" => "development",
            "variant" => "fdroid",
        ]);

        $response->assertSeeText(__("membership.next.app"));
        $response->assertSee(
            config("metager.metager.app.callback_dev_url") . "/app/callback/fdroid?key=" . self::A_KEY . "&amp;flow=charge",
            false
        );
    }

    /**
     * Ein Knopf und keine Weiterleitung: diese Seite ist die einzige, die den
     * Schlüssel zeigt, und der Hinweis auf die Prüfung steht ebenfalls nur hier.
     */
    public function testTheAppVisitorStillGetsToSeeThePage(): void
    {
        $this->visit($this->finishedApplication(), ["keystore" => "development"])
            ->assertOk()
            ->assertSee(self::A_KEY, false);
    }

    /**
     * `flow=charge` sagt der App, dass der Schlüssel noch nichts bezahlen kann.
     * Bei Überweisung und Lastschrift lädt erst die Bearbeitung des Antrags auf;
     * über PayPal ist bereits gezahlt.
     */
    public function testAPaidApplicationHandsBackAKeyThatCanAlreadyPay(): void
    {
        $this->visit($this->finishedApplication("paypal"), ["keystore" => "development"])
            ->assertDontSee("flow=charge", false);
    }

    /**
     * Ein unbekannter `keystore` ist kein App-Rückruf: `AppCallback::isHandback()`
     * zählt nur die Zertifikate auf, die es gibt. Sonst wäre jede fremde Seite,
     * die diesen URL baut, eine offene Weiterleitung mit einem Schlüssel darin.
     */
    public function testAnUnknownKeystoreIsNoHandback(): void
    {
        $this->visit($this->finishedApplication(), ["keystore" => "elsewhere"])
            ->assertSeeText(__("membership.next.search"));
    }
}
