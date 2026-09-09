<?php

namespace Tests\Feature;

use App\Models\Membership\MembershipApplication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Der erste Schritt des Aufnahmeantrags ist auch der, der den Schlüssel gibt.
 *
 * Er ist das, was die Mitgliedschaft später auflädt, und er steht auf dem
 * Antrag, damit die Verwaltung weiß, welches Konto sie gutschreibt. Wer schon
 * angemeldet ist, behält seinen; wer nicht, bekommt einen und wird an ihm
 * angemeldet.
 *
 * Gepinnt, weil beide Hälften still versagen konnten. Der Schlüssel wurde über
 * die Fetch-Queue geholt — eine geratene UUID, per GET beim Keyserver
 * nachgefragt und für frei gehalten, wenn ihre Ladung 0 war —, und blieb bei
 * jedem Fehlschlag `null`. `route("loadSettings", ["key" => null, …])` lässt
 * einen leeren Parameter weg: der Besucher landete auf einer Weiterleitung ohne
 * Schlüssel, bekam kein Cookie, und auf dem Antrag stand keiner. Ohne
 * Fehlermeldung und ohne Logzeile.
 *
 * Jetzt fragt der Antrag dieselbe Naht wie alles andere hier,
 * {@see \App\Authentication\KeyIssuer} — `POST /api/json/key/new`, die Frage,
 * die der Keyserver tatsächlich beantwortet. Die alte Prüfung war auch für sich
 * falsch: ein echtes, leergesuchtes Konto hat ebenfalls Ladung 0 und wäre einem
 * Mitglied als „frei“ übergeben worden.
 */
class MembershipKeyTest extends TestCase
{
    use DatabaseTransactions;

    /** Der Schlüssel, an dem ein Besucher schon angemeldet ist. */
    private const EXISTING_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    /** Der, den der Keyserver auf `key/new` herausgibt. */
    private const FRESH_KEY = "a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d";

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    /**
     * Was der Keyserver auf `key/new` antwortet.
     *
     * Pro Test und nicht in setUp(): `Http::fake()` legt Stubs *dazu*, und der
     * erste passende gewinnt — ein zweiter Aufruf in einem Test käme also nie
     * zum Zug.
     */
    private function keyserverAnswers(\Illuminate\Http\Client\Response|\GuzzleHttp\Promise\PromiseInterface $response): void
    {
        Http::fake(["*/api/json/key/new" => $response]);
    }

    /**
     * Der erste Schritt, so wie der Browser ihn abschickt.
     *
     * Web-Routen laufen ohne Session, es gibt also kein Laravel-CSRF; `_token`
     * ist hier ein verschlüsselter Ablaufzeitpunkt, den der Controller einmal
     * einlöst ({@see \App\Http\Controllers\MembershipController::getToken()}).
     */
    private function submitContactData(array $extra = [], array $cookies = []): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(["Origin" => config("app.url")])
            ->withUnencryptedCookies($cookies)
            ->post("/de-DE/membership", array_merge([
                "_token" => Crypt::encrypt(now()->addHour()),
                "type" => "person",
                "title" => "Neutral",
                "firstname" => "Test",
                "lastname" => "Person",
                "email" => "test@example.com",
            ], $extra));
    }

    private function latestApplication(): ?MembershipApplication
    {
        return MembershipApplication::orderBy("created_at", "desc")->first();
    }

    // ── Wer schon angemeldet ist ─────────────────────────────────────────────

    /**
     * Der mitgebrachte Schlüssel wird übernommen — es gibt keinen zweiten.
     *
     * Ein neuer bekäme ein eigenes, getrenntes Guthaben, und das alte wäre
     * nicht weg, sondern nur nicht mehr erreichbar.
     */
    public function testALoggedInVisitorKeepsTheirKey(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        $this->submitContactData(cookies: ["key" => self::EXISTING_KEY]);

        $this->assertSame(self::EXISTING_KEY, $this->latestApplication()?->key);
    }

    /** Und der Keyserver wird gar nicht erst gefragt. */
    public function testALoggedInVisitorCostsNoKeyserverCall(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        $this->submitContactData(cookies: ["key" => self::EXISTING_KEY]);

        Http::assertNothingSent();
    }

    /**
     * Kein Umweg über load-settings: das Cookie ist schon da, und der Umweg ist
     * genau der, der es setzt.
     */
    public function testALoggedInVisitorGoesStraightToTheNextStep(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        $location = $this->submitContactData(cookies: ["key" => self::EXISTING_KEY])
            ->headers->get("Location");

        $this->assertStringContainsString("#membership-fee", $location);
        $this->assertStringNotContainsString("load-settings", $location);
    }

    // ── Wer noch keinen hat ──────────────────────────────────────────────────

    /** Der frische Schlüssel steht auf dem Antrag. */
    public function testAnAnonymousVisitorGetsAFreshKey(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        $this->submitContactData();

        $this->assertSame(self::FRESH_KEY, $this->latestApplication()?->key);
    }

    /**
     * Und er wird an ihm angemeldet: der Umweg über load-settings ist die
     * Stelle, die das Cookie setzt. Der Regressionstest zum Kern des Fehlers —
     * hier stand `key` gar nicht mehr im URL.
     */
    public function testAnAnonymousVisitorIsSignedInOnTheWayToTheNextStep(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        $location = $this->submitContactData()->headers->get("Location");

        $this->assertStringContainsString("load-settings", $location);
        $this->assertStringContainsString("key=" . self::FRESH_KEY, $location);
    }

    /** Der signierte Rückweg führt auf den nächsten Schritt desselben Antrags. */
    public function testTheSignedRedirectLeadsBackToTheForm(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        $location = $this->submitContactData()->headers->get("Location");

        parse_str(parse_url($location, PHP_URL_QUERY) ?: "", $query);

        $this->assertArrayHasKey("redirect_url", $query);
        $this->assertStringContainsString("#membership-fee", $query["redirect_url"]);
        $this->assertSame(
            hash_hmac("sha256", $query["redirect_url"] . $query["expires"], config("app.key")),
            $query["signature"]
        );
    }

    /** Gefragt wird `key/new` und nicht die Ladung einer geratenen UUID. */
    public function testTheKeyComesFromTheKeyserversOwnEndpoint(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        $this->submitContactData();

        Http::assertSent(fn($request) => str_ends_with($request->url(), "/api/json/key/new")
            && $request->method() === "POST");
    }

    // ── Und er bleibt nicht in der Adresszeile stehen ────────────────────────

    /**
     * Der Schlüssel reist nicht als `?key=` durch das restliche Formular.
     *
     * Er kommt dort an: die Weiterleitung von load-settings hängt ihn an das
     * Ziel, weil `CookieSupport::carryIntoUrl()` in jener Anfrage einen
     * Schlüssel in der Query und noch kein Cookie sieht — das Cookie liegt zu
     * dem Zeitpunkt erst in der Antwort. Das ist für einen geteilten Link
     * gewollt und hier das Gegenteil davon: das Formular baut den nächsten URL
     * aus allem, was ankam, also stünde der Schlüssel ab da in jedem Schritt,
     * im Referer und am Ende im URL der Erfolgsseite — genau der Umweg, den der
     * Umzug des Kontos abgeschafft hat.
     *
     * Aufgefallen erst, als das Erstellen wieder verlässlich funktionierte:
     * solange gar kein Schlüssel entstand, gab es auch keinen, der hätte
     * mitreisen können.
     */
    public function testTheKeyDoesNotRideAlongInTheFormsUrls(): void
    {
        $this->keyserverAnswers(Http::response(["key" => self::FRESH_KEY]));

        // Schritt zwei, so wie der Browser ihn nach dem Anmelde-Hop abschickt:
        // mit dem Schlüssel im URL und im Cookie.
        $application = MembershipApplication::create(["locale" => "de-DE"]);
        $application->contact()->create([
            "title" => "Neutral",
            "first_name" => "Test",
            "last_name" => "Person",
            "email" => "test@example.com",
            "application_id" => $application->id,
        ]);

        $location = $this->withHeaders(["Origin" => config("app.url")])
            ->withUnencryptedCookies(["key" => self::FRESH_KEY])
            ->post("/de-DE/membership/" . $application->id . "?key=" . self::FRESH_KEY, [
                "_token" => Crypt::encrypt(now()->addHour()),
                "amount" => "10.00",
            ])
            ->headers->get("Location");

        $this->assertStringNotContainsString(self::FRESH_KEY, $location);
        $this->assertStringContainsString("#membership-payment", $location);
    }

    /**
     * Auch das Formular selbst baut den Schlüssel nicht wieder ein.
     *
     * Das `action` entsteht im Blade aus `request()->except(...)` und nicht aus
     * dem, was der Controller weitergibt — die eine Stelle greift also nicht
     * für die andere. Genau daran ist der erste Anlauf gescheitert: die
     * Weiterleitung war sauber, die Seite, auf der sie endete, stellte den
     * Schlüssel aber sofort wieder in ihr eigenes Ziel.
     */
    public function testTheFormDoesNotPutTheKeyBackIntoItsAction(): void
    {
        // Angemeldet gerendert: die Seite fragt den Keyserver nach dem Konto,
        // zu dem das Cookie gehört. Hier ist das Kulisse.
        Http::fake(["*" => Http::response(["key" => self::FRESH_KEY, "charge" => 0])]);

        $application = MembershipApplication::create(["locale" => "de-DE"]);

        $response = $this->withUnencryptedCookies(["key" => self::FRESH_KEY])
            ->get("/de-DE/membership/" . $application->id . "?key=" . self::FRESH_KEY);

        preg_match('/<form id="membership-form"[^>]*action="([^"]*)"/', $response->getContent(), $action);

        $this->assertNotEmpty($action, "Das Formular hat kein action-Attribut.");
        $this->assertStringNotContainsString(self::FRESH_KEY, $action[1]);
    }

    // ── Wenn der Keyserver nicht antwortet ───────────────────────────────────

    /**
     * Dann wird der Antrag gar nicht erst angelegt.
     *
     * Ein Antrag ohne Schlüssel ist einer, den die Verwaltung von Hand
     * reparieren muss — und vorher ein Mitglied, das für ein Konto zahlt, das es
     * nicht gibt.
     */
    public function testAnUnreachableKeyserverCreatesNoApplication(): void
    {
        $this->keyserverAnswers(Http::response(status: 502));

        $before = MembershipApplication::count();
        $this->submitContactData();

        $this->assertSame($before, MembershipApplication::count());
    }

    /** Und der Besucher sieht, woran es liegt, statt einer stillen Weiterleitung. */
    public function testAnUnreachableKeyserverSaysSo(): void
    {
        $this->keyserverAnswers(Http::response(status: 502));

        $this->submitContactData()
            ->assertOk()
            ->assertSeeText(__("key-create.errors.keyserver_unreachable"));
    }
}
