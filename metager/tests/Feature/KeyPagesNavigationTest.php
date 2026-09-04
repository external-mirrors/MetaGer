<?php

namespace Tests\Feature;

use App\Authentication\KeyUser;
use Tests\TestCase;

/**
 * Die umgezogenen Seiten sind auch auffindbar.
 *
 * Das ist der Teil des Umzugs, der lautlos schiefgeht: die Seiten antworten,
 * die alten Pfade leiten weiter, und trotzdem kommt niemand mehr hin, weil die
 * Navigation des Keymanagers — in der „Preis“ und „Hilfe“ ganz oben standen —
 * mit der Landingpage verschwunden ist und MetaGers Seitenleiste nie einen
 * Eintrag dafür hatte.
 */
class KeyPagesNavigationTest extends TestCase
{
    public function testTheMenuLinksThePriceAndTheTerms(): void
    {
        $response = $this->get("/")->assertOk();

        $response->assertSee('href="' . url("/preise") . '"', false);
        $response->assertSee('href="' . url("/agb") . '"', false);
        $response->assertSeeText(__("sidebar.navPrice"));
        $response->assertSeeText(__("sidebar.navAgb"));
    }

    /** Und zwar sprachrichtig — die Seitenleiste steht auf jeder Seite. */
    public function testTheMenuLinksAreLocalePrefixed(): void
    {
        $response = $this->get("/ca-ES/")->assertOk();

        // Nicht url(): unter einem ca-ES-Request setzt URL::formatPathUsing das
        // Präfix noch einmal davor. Der Pfad ist die Aussage.
        $response->assertSee('/ca-ES/preise"', false);
        $response->assertSee('/ca-ES/agb"', false);
    }

    public function testTheHelpIndexLeadsToAllThreeKeyPages(): void
    {
        $response = $this->get("/hilfe")->assertOk();

        $response->assertSee('href="' . url("/hilfe/schluessel") . '"', false);
        $response->assertSee('href="' . url("/hilfe/anonyme-token") . '"', false);
        $response->assertSee('href="' . url("/preise") . '"', false);
        $response->assertSeeText(__("help/help.tableofcontents.5.0"));
    }

    /**
     * Der Anker bleibt, der Stoff nicht.
     *
     * #h-keyexplain hat die vier Einrichtungswege wortgleich so erklärt wie die
     * FAQ des Keymanagers. Jetzt steht dort ein Einstieg mit Verweis — und der
     * Anker selbst muss bleiben, weil der Hilfe-Index und mehrere Sprachdateien
     * ihn verlinken.
     */
    public function testTheOldKeySectionKeepsItsAnchorAndDefersToTheNewPage(): void
    {
        $response = $this->get("/hilfe/funktionen")->assertOk();

        $response->assertSee('id="h-keyexplain"', false);
        $response->assertSee('href="' . url("/hilfe/schluessel") . '"', false);
        $response->assertSeeText(__("help/help-functions.key.more"));
    }

    /**
     * Die Schlüsselfarben sind weg.
     *
     * Sie beschrieben grau/rot/grün/gelb für ein Schlüsselsymbol in der
     * Suchleiste, das die Kontopille ersetzt hat — in allen zwölf Sprachen eine
     * Oberfläche, die es nicht mehr gibt.
     */
    public function testTheStaleKeyColoursAreGone(): void
    {
        $this->get("/hilfe/funktionen")
            ->assertOk()
            ->assertDontSeeText("Farbiger MetaGer Schlüssel");

        $this->assertSame(
            "help/help-functions.key.colors.title",
            trans("help/help-functions.key.colors.title"),
            "key.colors ist wieder da — der Abschnitt beschreibt eine Oberfläche, die es nicht mehr gibt."
        );
    }

    /**
     * Nichts im Interface zeigt mehr auf die umgezogenen Pfade.
     *
     * Sie funktionieren über die Weiterleitung weiter, aber ein eigener Link
     * auf einen Umweg ist keiner, den wir behalten wollen — und
     * /keys/help/anonymous-token stand zusätzlich in der Willkommensmail, die
     * ab jetzt die neue Route nennt.
     */
    public function testNoPageStillLinksToTheOldKeysPaths(): void
    {
        foreach (["/", "/hilfe", "/hilfe/funktionen", "/preise", "/agb", "/hilfe/schluessel"] as $path) {
            $content = $this->get($path)->assertOk()->getContent();

            foreach (["/keys/cost", "/keys/agb", "/keys/help/"] as $moved) {
                $this->assertStringNotContainsString(
                    $moved,
                    $content,
                    "$path verlinkt noch $moved"
                );
            }
        }
    }

    /**
     * Jedes Bild auf den umgezogenen Seiten liegt auch wirklich da.
     *
     * Der Grund ist eine Falle, in die dieser Umzug schon getappt ist: nginx
     * leitet `^(/.*)?/keys` an den Keymanager weiter, und diese Regex trifft
     * *jeden* Pfad mit einem Segment `/keys` darin. Die Symbole der Preisseite
     * lagen zuerst unter /img/keys/ und kamen deshalb als 404 zurück — vom
     * Keymanager, nicht von MetaGer. Ein leerer Platz im Layout, sonst nichts.
     *
     * Gilt für alles unter public/: kein Verzeichnis dort darf „keys“ heißen.
     */
    public function testEveryImageOnTheMovedPagesResolves(): void
    {
        $missing = [];

        foreach (["/preise", "/agb", "/hilfe/schluessel", "/hilfe/anonyme-token"] as $path) {
            $content = $this->get($path)->assertOk()->getContent();

            preg_match_all('~<img[^>]+src="(/[^"]+)"~', $content, $matches);
            foreach (array_unique($matches[1]) as $src) {
                if (str_starts_with($src, "/build/")) {
                    continue; // gehashte Bauausgabe, deckt AssetPipelineTest ab
                }
                if (preg_match("~(^|/)keys(/|$)~", $src)) {
                    $missing[] = "$path: $src liegt unter einem /keys-Pfad und wird an den Keymanager weitergeleitet";
                    continue;
                }
                if (!is_file(public_path(ltrim($src, "/")))) {
                    $missing[] = "$path: $src gibt es in public/ nicht";
                }
            }
        }

        $this->assertSame([], $missing);
    }

    /**
     * /preise ist keine Sackgasse.
     *
     * Die Seite wird von der Seitenleiste, den Landing-Abschnitten, dem
     * Hilfe-Index und dem Konto aus verlinkt, und wer sie zu Ende gelesen hat,
     * stand bis zu diesem Test ohne nächsten Schritt da. Für einen Besucher
     * ohne Schlüssel ist der nächste Schritt der Einstieg in den
     * Schlüsselvorgang — dieselben zwei Wege wie auf der Startseite.
     */
    public function testThePricePageLeadsIntoTheKeyFlowForAVisitorWithoutAKey(): void
    {
        $response = $this->get("/preise")->assertOk();

        $response->assertSee('href="' . url("/schluessel-erstellen") . '"', false);
        // /anmelden trägt ein ?redirect_success wie in parts/landing/how-it-works,
        // deshalb der Pfad ohne schließendes Anführungszeichen.
        $response->assertSee('href="' . url("/anmelden") . '?', false);
        $response->assertSeeText(__("index.landing.howitworks.start"));
        $response->assertSeeText(__("index.landing.howitworks.login"));
    }

    /**
     * Und für einen angemeldeten Besucher der Rückweg ins Konto.
     *
     * Der Fall, der die Beschwerde ausgelöst hat: jemand kommt aus /konto auf
     * /preise, liest, und findet keinen Weg zurück. „Angemeldet" ist hier genau
     * die Menge, die aus dem Konto gekommen sein kann — die Seite ist
     * anmeldepflichtig. Die Weberweiterung (temporärer Nutzer) zählt nicht
     * dazu: sie hat hier kein Konto.
     */
    public function testThePricePageLeadsBackToTheAccountForASignedInVisitor(): void
    {
        // false: `key` steht in EncryptCookies::$except — derselbe Aufbau wie
        // AccountPageTest::signedIn().
        $response = $this->withUnencryptedCookie("key", "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15")
            ->get("/preise")
            ->assertOk();

        $response->assertSee('href="' . url("/konto") . '"', false);
        $response->assertSeeText(__("account.page.heading"));
    }

    /**
     * Die Weberweiterung ist keine Anmeldung wert, die es nicht gibt.
     *
     * Ein temporärer Besucher (die Weberweiterung meldet über ein anonymes
     * Token an, nicht über einen echten Schlüssel — App\Authentication\
     * KeyAuthGuard::user()) fiel bis hierher in denselben Zweig wie ein
     * Besucher ganz ohne Schlüssel und bekam auch "Ich habe bereits einen
     * Schlüssel" angeboten. Das ist eine Sackgasse: /anmelden erkennt das
     * anonyme Token nicht (es prüft nur Cookie/Header/Query "key") und zeigt
     * ein echtes Formular für eine Eingabe, die dieser Besucher gar nicht
     * hat — App\Http\Controllers\AccountController::show() schickt ihn von
     * dort ohnehin nur weiter zur Erklärseite. "Start" bleibt der einzige
     * Weg, der für ihn tatsächlich funktioniert: /schluessel-erstellen fragt
     * nie nach dem Token und stellt einen neuen, echten Schlüssel aus.
     */
    public function testThePricePageOffersOnlyKeyCreationToAWebextensionVisitor(): void
    {
        $user = new KeyUser("aaaaaaaa-bbbb-cccc-dddd-eeeeee999999");
        $user->temporary = true;
        $this->be($user, "key");

        $response = $this->get("/preise")->assertOk();

        $response->assertSee('href="' . url("/schluessel-erstellen") . '"', false);
        $response->assertSeeText(__("index.landing.howitworks.start"));
        $response->assertDontSee('href="' . url("/anmelden") . '?', false);
        $response->assertDontSeeText(__("index.landing.howitworks.login"));
    }

    /**
     * Der Schlüsselvorgang ist jetzt vollständig MetaGers eigener — die FAQ
     * verlinkt beide Wege dorthin, nicht mehr auf den Keymanager.
     */
    public function testTheKeyFaqLinksTheOwnKeyFlow(): void
    {
        $response = $this->get("/hilfe/schluessel")->assertOk();

        // /anmelden und /c sind seit dem vierten Umzugsschritt beide
        // MetaGer-Routen; /keys/c ist nur noch eine dauerhafte Weiterleitung
        // (App\Landing\KeymanagerLinks::voucher()).
        $response->assertSee("/anmelden", false);
        // Der ganze Pfad, nicht nur „/c" irgendwo: das schließende
        // Anführungszeichen bindet die Prüfung an ein href, das genau auf /c
        // endet — die Einlöseseite.
        $response->assertSee('/c"', false);
        $response->assertDontSee("/keys/c", false);
    }
}
