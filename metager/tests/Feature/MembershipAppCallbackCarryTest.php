<?php

namespace Tests\Feature;

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
        config(["metager.metager.crm.url" => "https://crm.example.com", "metager.metager.crm.token" => "test-token"]);
        // Angemeldet unterwegs: die Seiten fragen den Keyserver nach dem Konto,
        // zu dem das Cookie gehört (Guthaben, Zustand). Hier ist der Schlüssel
        // Kulisse — geprüft wird der Weg, den die Marker nehmen.
        //
        // suma-crm eröffnet im letzten Schritt die Checkout-Sitzung. Die Fälschung
        // gibt als `checkout_url` den `return_url` zurück, den sie bekommen hat —
        // so, als wäre der Besucher bei suma-payments sofort fertig geworden. Genau
        // dieser URL ist die Stelle, an der die Marker die Zahlungsstrecke
        // überqueren müssen; trägt er sie nicht, endet der Weg ohne sie.
        Http::fake(function ($request) {
            if (str($request->url())->contains("/api/membership-checkouts")) {
                return Http::response([
                    "payment_reference" => "M-TESTREFERENCE",
                    "checkout_url" => $request["return_url"],
                ], 201);
            }

            return Http::response(["key" => self::A_KEY, "charge" => 0]);
        });
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

        // Der Schritt „Zahlungsart“ führt nicht mehr auf das Formular zurück,
        // sondern zur gehosteten Checkout-Seite — und von dort auf den
        // `return_url`, also die Erfolgsseite. Die Fälschung oben kürzt beides
        // zu einer Weiterleitung ab, weil suma-payments hier nicht läuft.
        return $this->step($url, ["payment-method" => "banktransfer"])->headers->get("Location");
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

        // Not looked up via MembershipApplication::orderBy(...)->first():
        // this application is a non-reduced, non-company, non-update one
        // (banktransfer, per walkTheForm()), so by the time this line runs
        // it has already been pushed into suma-crm's own review queue and
        // deleted locally (see MembershipController::maybePushToSumaCrm(),
        // docs/civicrm-replacement.md "Membership application review moves
        // to suma-crm") — there is nothing left in this table to look up.
        // The success URL itself is the thing under test: it must carry an
        // application_id segment, not the bare key a stale redirect used to.
        $this->assertMatchesRegularExpression('#/membership/success/[0-9a-f-]{36}(\?|$)#', $success);

        $this->withUnencryptedCookies(["key" => self::A_KEY])
            ->get($success)
            ->assertOk()
            ->assertSeeText(__("membership.next.app"));
    }
}
