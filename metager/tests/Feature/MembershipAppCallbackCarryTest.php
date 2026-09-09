<?php

namespace Tests\Feature;

use App\Models\Membership\MembershipApplication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Die Marker der App überleben das vierstufige Formular.
 *
 * Der Aufnahmeantrag kann aus dem Custom Tab der App heraus abgeschickt werden —
 * aus dem Ablauf „anmelden, Schlüssel erstellen, aufladen“ heraus. Damit die
 * Erfolgsseite den Schlüssel am Ende über den verifizierten App Link
 * zurückgeben kann ({@see \App\Landing\AppCallback}), müssen `keystore` und
 * `variant` vier Schritte und fünf Weiterleitungen weit mitkommen. Fällt einer
 * unterwegs weg, endet der Antrag auf einer Seite mit einem Knopf in die Suche,
 * und die App hat den Schlüssel nie gesehen — ohne dass irgendwo etwas
 * fehlschlägt.
 *
 * Getragen werden sie vom URL: `$request->except([...])` schaufelt alles, was
 * ankam, in den nächsten `route("membership_form", …)`, und das Formular
 * schickt an genau diesen URL zurück. Die drei Stellen, die *nicht* so gebaut
 * werden, sind die Weiterleitungen auf die Erfolgsseite; sie hängen die Marker
 * ausdrücklich an.
 */
class MembershipAppCallbackCarryTest extends TestCase
{
    use DatabaseTransactions;

    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    private const MARKERS = ["keystore" => "development", "variant" => "fdroid"];

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        // Angemeldet unterwegs: die Seiten fragen den Keyserver nach dem Konto,
        // zu dem das Cookie gehört (Guthaben, Zustand). Hier ist der Schlüssel
        // Kulisse — geprüft wird der Weg, den die Marker nehmen.
        Http::fake(["*" => Http::response(["key" => self::A_KEY, "charge" => 0])]);
        // Der letzte Schritt benachrichtigt die Verwaltung.
        Mail::fake();
    }

    /**
     * Ein Schritt, so wie der Browser ihn abschickt: an den URL, der ankam.
     *
     * Ohne das Fragment — die Weiterleitungen des Formulars enden auf
     * `#membership-fee` und dergleichen, damit der Browser zum richtigen
     * Abschnitt springt, und schicken tut er es nie mit. Der Testclient tut es
     * schon, und dann passt keine Route mehr.
     */
    private function step(string $url, array $fields): \Illuminate\Testing\TestResponse
    {
        $url = strtok($url, "#");

        // Eine Sekunde zwischen den Schritten. `_token` ist ein verschlüsselter
        // Ablaufzeitpunkt, und eingelöst wird er unter dem Cache-Schlüssel
        // `membership_<unix>` — sekundengenau. Vier Schritte in derselben
        // Sekunde sind für den Controller derselbe Token, zum zweiten Mal
        // vorgelegt. Ein Mensch am Formular ist nie so schnell.
        $this->travel(1)->second();

        return $this->withHeaders(["Origin" => config("app.url")])
            ->withUnencryptedCookies(["key" => self::A_KEY])
            ->post($url, array_merge(["_token" => Crypt::encrypt(now()->addHour())], $fields));
    }

    /**
     * Der ganze Weg, vom leeren Formular bis zur Erfolgsseite.
     *
     * Angemeldet unterwegs, damit der Umweg über load-settings entfällt — der
     * ist in {@see MembershipKeyTest} gepinnt und hätte hier nur mehr Hops.
     *
     * @return string der letzte URL, auf den weitergeleitet wurde
     */
    private function walkTheForm(): string
    {
        $url = "/de-DE/membership?" . http_build_query(self::MARKERS);

        $url = $this->step($url, [
            "type" => "person",
            "title" => "Neutral",
            "firstname" => "Test",
            "lastname" => "Person",
            "email" => "test@example.com",
        ])->headers->get("Location");

        $url = $this->step($url, ["amount" => "10.00"])->headers->get("Location");
        $url = $this->step($url, ["interval" => "monthly"])->headers->get("Location");
        $url = $this->step($url, ["payment-method" => "banktransfer"])->headers->get("Location");

        // Das Formular schickt einen fertigen Antrag selbst auf die Erfolgsseite.
        return $this->withUnencryptedCookies(["key" => self::A_KEY])
            ->get(strtok($url, "#"))
            ->headers->get("Location");
    }

    public function testTheMarkersSurviveEveryStep(): void
    {
        $success = $this->walkTheForm();

        $this->assertStringContainsString("keystore=development", $success);
        $this->assertStringContainsString("variant=fdroid", $success);
    }

    /**
     * Und am Ende steht die Erfolgsseite mit dem Rückweg in die App.
     *
     * Hierher führte vorher gar nichts: die Weiterleitung nannte den Schlüssel
     * als `key` statt den Antrag als `application_id`, also fehlte der Seite die
     * Kennung, die sie auflösen muss — sie schickte zurück auf das Formular, mit
     * dem Schlüssel in der Adresszeile.
     */
    public function testTheWayEndsOnTheSuccessPage(): void
    {
        $success = $this->walkTheForm();
        $application = MembershipApplication::orderBy("created_at", "desc")->first();

        $this->assertStringContainsString("/membership/success/" . $application->id, $success);

        $this->withUnencryptedCookies(["key" => self::A_KEY])
            ->get($success)
            ->assertOk()
            ->assertSeeText(__("membership.next.app"));
    }
}
