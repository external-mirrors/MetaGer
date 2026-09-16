<?php

namespace Tests\Feature;

use App\Models\Membership\MembershipApplication;
use App\Models\Membership\MembershipContact;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Die Mitgliedschaft entsteht in suma-crm, nicht mehr in CiviCRM.
 *
 * Zwei Übergaben, an zwei verschiedenen Zeitpunkten, und die Referenz
 * dazwischen ist das, was sie verbindet:
 *
 *  1. Beim Absenden des Formulars eröffnet suma-crm die Checkout-Sitzung
 *     ({@see \App\Membership\MembershipCheckoutIssuer}) — dort ist der
 *     Antragsteller noch da und kann das Mandat erteilen. Die Referenz, die
 *     dabei herauskommt, bleibt am Antrag stehen.
 *  2. Nimmt die Verwaltung den Antrag an, legt suma-crm Contact und
 *     Membership an ({@see \App\Membership\MembershipIssuer}) — mit genau
 *     dieser Referenz, damit das bereits erteilte Mandat übernommen und
 *     nicht ein zweites eröffnet wird.
 *
 * Genau das ist der Grund, warum es zwei Aufrufe sind und nicht einer: beim
 * Annehmen ist niemand mehr da, der eine IBAN eintippen könnte.
 */
class MembershipCrmHandoverTest extends TestCase
{
    use DatabaseTransactions;

    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    private const REFERENCE = "M-TESTREFERENCE";

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config(["metager.metager.crm.url" => "https://crm.example.com", "metager.metager.crm.token" => "test-token"]);
        Mail::fake();
    }

    /**
     * Die einzige Stelle, an der in einem Test gefälscht wird — bewusst nicht
     * in setUp(). Ein zweiter `Http::fake()`-Aufruf ersetzt den ersten nicht,
     * sondern kommt dahinter: der zuerst registrierte Handler antwortet. Ein
     * Test, der einen Fehlerfall braucht, müsste also gegen einen Erfolgsfall
     * aus setUp() anlaufen und bekäme still den Erfolg.
     */
    private function fakeCrm(int $checkoutStatus = 201, int $membershipStatus = 201, int $voidStatus = 200): void
    {
        Http::fake(function ($request) use ($checkoutStatus, $membershipStatus, $voidStatus) {
            if (str($request->url())->contains("/void")) {
                return Http::response(["status" => "voided"], $voidStatus);
            }

            if (str($request->url())->contains("/api/membership-checkouts")) {
                return Http::response([
                    "payment_reference" => self::REFERENCE,
                    "checkout_url" => "https://payments.example.com/checkout/abc",
                ], $checkoutStatus);
            }

            if (str($request->url())->contains("/api/memberships")) {
                return Http::response(["membership_id" => "42", "checkout_url" => null], $membershipStatus);
            }

            return Http::response(["key" => self::A_KEY, "charge" => 0]);
        });
    }

    private function step(string $url, array $fields): \Illuminate\Testing\TestResponse
    {
        $this->travel(1)->second();

        return $this->withHeaders(["Origin" => config("app.url")])
            ->withUnencryptedCookies(["key" => self::A_KEY])
            ->post(strtok($url, "#"), array_merge(["_token" => Crypt::encrypt(now()->addHour())], $fields));
    }

    /**
     * Bis einschließlich Zahlungsart, also bis zu der Weiterleitung, die den
     * Antragsteller zur gehosteten Checkout-Seite schickt.
     */
    private function applyThrough(string $paymentMethod): \Illuminate\Testing\TestResponse
    {
        $url = $this->step("/de-DE/membership", [
            "type" => "person",
            "title" => "Neutral",
            "firstname" => "Test",
            "lastname" => "Person",
            "email" => "test@example.com",
        ])->headers->get("Location");

        $url = $this->step($url, ["amount" => "10.00"])->headers->get("Location");
        $url = $this->step($url, ["interval" => "monthly"])->headers->get("Location");

        return $this->step($url, ["payment-method" => $paymentMethod]);
    }

    public function testTheFormRedirectsToTheHostedCheckoutAndKeepsTheReference(): void
    {
        $this->fakeCrm();

        $this->applyThrough("directdebit")
            ->assertRedirect("https://payments.example.com/checkout/abc");

        $application = MembershipApplication::orderBy("created_at", "desc")->first();

        $this->assertSame(self::REFERENCE, $application->payment_reference);
        $this->assertSame("directdebit", $application->payment_method);
    }

    public function testTheCheckoutCallSendsTheMonthlyFeeAndTheApplicantsOwnDetails(): void
    {
        $this->fakeCrm();

        $this->applyThrough("directdebit");

        Http::assertSent(function ($request) {
            if (!str($request->url())->contains("/api/membership-checkouts")) {
                return false;
            }

            // Der Monatsbeitrag, nicht der je Intervall — suma-crm rechnet um
            // (gap #32). Bei „monthly“ sind beide gleich; die Zusicherung
            // steht hier trotzdem, weil sie die Richtung festhält.
            return $request["amount"] == 10.00
                && $request["interval"] === "monthly"
                && $request["payment_method"] === "directdebit"
                && $request["email"] === "test@example.com"
                && $request["name"] === "Test Person";
        });
    }

    /**
     * Die IBAN erhebt das Formular nicht mehr — sie nimmt die gehostete
     * Checkout-Seite entgegen. Nichts an diesem Schritt darf sie noch
     * verlangen, sonst kommt kein Antragsteller mehr an der Zahlungsart
     * vorbei.
     */
    public function testTheFormNoLongerAsksForAnIban(): void
    {
        $this->fakeCrm();

        $this->applyThrough("directdebit")->assertRedirect("https://payments.example.com/checkout/abc");

        $application = MembershipApplication::orderBy("created_at", "desc")->first();

        $this->assertNull($application->directdebit);
    }

    /**
     * Antwortet suma-crm nicht, bleibt der Antrag ohne Zahlungsart stehen —
     * und nicht mit einer, hinter der kein Mandat steht.
     */
    public function testAnUnreachableCrmLeavesTheApplicationWithoutAPaymentMethod(): void
    {
        $this->fakeCrm(checkoutStatus: 500);

        $this->applyThrough("directdebit");

        $application = MembershipApplication::orderBy("created_at", "desc")->first();

        $this->assertNull($application->payment_method);
        $this->assertNull($application->payment_reference);
    }

    public function testAcceptingAnApplicationCreatesTheMembershipInTheCrm(): void
    {
        $this->fakeCrm();

        $application = MembershipApplication::create([
            "locale" => "de-DE",
            "amount" => 10.00,
            "interval" => "monthly",
            "payment_method" => "directdebit",
            "payment_reference" => self::REFERENCE,
            "key" => self::A_KEY,
        ]);
        MembershipContact::create([
            "title" => "Neutral",
            "first_name" => "Test",
            "last_name" => "Person",
            "email" => "test@example.com",
            "application_id" => $application->id,
        ]);

        $this->post(route("membership_admin_accept"), ["id" => $application->id])
            ->assertRedirect();

        Http::assertSent(function ($request) {
            if (!str($request->url())->contains("/api/memberships")) {
                return false;
            }

            // Die entscheidende Zusicherung: die am Formular vergebene
            // Referenz reist mit. Ohne sie eröffnet suma-crm ein zweites
            // Mandat, und der Beitrag würde doppelt eingezogen.
            return $request["payment_reference"] === self::REFERENCE
                && $request["membership_type"] === "person"
                && $request["first_name"] === "Test"
                && $request["last_name"] === "Person"
                && $request["email"] === "test@example.com"
                && $request["payment_method"] === "directdebit"
                && $request["key"] === self::A_KEY;
        });

        // Angenommen heißt erledigt: der Antrag ist weg.
        $this->assertNull(MembershipApplication::find($application->id));
    }

    /**
     * Lehnt suma-crm ab oder ist nicht erreichbar, bleibt der Antrag offen —
     * ein zweiter Anlauf der Verwaltung ist dann gefahrlos.
     */
    public function testAFailedHandoverLeavesTheApplicationPending(): void
    {
        $this->fakeCrm(membershipStatus: 422);

        $application = MembershipApplication::create([
            "locale" => "de-DE",
            "amount" => 10.00,
            "interval" => "monthly",
            "payment_method" => "directdebit",
            "payment_reference" => self::REFERENCE,
            "key" => self::A_KEY,
        ]);
        MembershipContact::create([
            "title" => "Neutral",
            "first_name" => "Test",
            "last_name" => "Person",
            "email" => "test@example.com",
            "application_id" => $application->id,
        ]);

        $this->post(route("membership_admin_accept"), ["id" => $application->id]);

        $this->assertNotNull(MembershipApplication::find($application->id));
    }

    /**
     * Ein Antrag, der Schritt 1 (§1.4) schon durchlaufen hat, trägt ein
     * `held`-Mandat bei suma-payments — lehnt die Verwaltung ihn ab, statt
     * ihn anzunehmen, muss genau dieses Mandat aktiv geschlossen werden,
     * nicht nur der Antrag gelöscht.
     */
    public function testDenyingAnApplicationVoidsItsHeldMandate(): void
    {
        $this->fakeCrm();

        $application = MembershipApplication::create([
            "locale" => "de-DE",
            "amount" => 10.00,
            "interval" => "monthly",
            "payment_method" => "directdebit",
            "payment_reference" => self::REFERENCE,
            "key" => self::A_KEY,
        ]);
        MembershipContact::create([
            "title" => "Neutral",
            "first_name" => "Test",
            "last_name" => "Person",
            "email" => "test@example.com",
            "application_id" => $application->id,
        ]);

        $this->post(route("membership_admin_deny"), ["id" => $application->id])
            ->assertRedirect();

        Http::assertSent(fn ($request) => str($request->url())->contains("/api/membership-checkouts/" . self::REFERENCE . "/void"));
        $this->assertNull(MembershipApplication::find($application->id));
    }

    /**
     * Ohne Referenz wurde nie ein Mandat eröffnet (Schritt 1 nie abgeschlossen,
     * oder Zahlungsart `exempt`) — es gibt bei suma-payments nichts zu
     * schließen.
     */
    public function testDenyingAnApplicationWithNoPaymentReferenceCallsNoVoid(): void
    {
        $this->fakeCrm();

        $application = MembershipApplication::create([
            "locale" => "de-DE",
            "key" => self::A_KEY,
        ]);
        MembershipContact::create([
            "title" => "Neutral",
            "first_name" => "Test",
            "last_name" => "Person",
            "email" => "test@example.com",
            "application_id" => $application->id,
        ]);

        $this->post(route("membership_admin_deny"), ["id" => $application->id, "type" => "unfinished"]);

        Http::assertNotSent(fn ($request) => str($request->url())->contains("/void"));
    }
}
