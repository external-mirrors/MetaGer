<?php

namespace Tests\Feature;

use App\Models\Membership\MembershipApplication;
use App\Models\Membership\MembershipContact;
use App\Models\Membership\MembershipPaymentDirectdebit;
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
        config([
            "metager.metager.crm.url" => "https://crm.example.com",
            "metager.metager.crm.internal_url" => "https://crm.example.com",
            "metager.metager.crm.token" => "test-token",
        ]);
        Mail::fake();
    }

    /**
     * Die einzige Stelle, an der in einem Test gefälscht wird — bewusst nicht
     * in setUp(). Ein zweiter `Http::fake()`-Aufruf ersetzt den ersten nicht,
     * sondern kommt dahinter: der zuerst registrierte Handler antwortet. Ein
     * Test, der einen Fehlerfall braucht, müsste also gegen einen Erfolgsfall
     * aus setUp() anlaufen und bekäme still den Erfolg.
     */
    private function fakeCrm(int $checkoutStatus = 201, int $membershipStatus = 201, int $voidStatus = 200, int $applicationPushStatus = 201): void
    {
        Http::fake(function ($request) use ($checkoutStatus, $membershipStatus, $voidStatus, $applicationPushStatus) {
            if (str($request->url())->contains("/void")) {
                return Http::response(["status" => "voided"], $voidStatus);
            }

            // Checked before the plain /api/membership-checkouts branch
            // below — that URL is a prefix of this one.
            if (str($request->url())->contains("/api/membership-applications")) {
                return Http::response(["id" => "app-uuid"], $applicationPushStatus);
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
     * Bis einschließlich der Zahlungsstufe, also bis zu der Weiterleitung,
     * die den Antragsteller zur gehosteten Checkout-Seite schickt. Diese
     * Stufe verlangt kein eigenes Feld mehr — die Zahlungsart wird dort
     * gewählt, nicht hier.
     */
    private function applyThrough(): \Illuminate\Testing\TestResponse
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

        return $this->step($url, []);
    }

    /**
     * Simuliert die Rückkehr von suma-payments über suma-crms Rückgabe-Hop —
     * der Antragsteller landet hier mit der tatsächlich gewählten
     * Zahlungsart im Query-String, die success() nachträgt (siehe dort).
     */
    private function returnFromCheckout(string $applicationId, string $paymentMethod): \Illuminate\Testing\TestResponse
    {
        return $this->withUnencryptedCookies(["key" => self::A_KEY])
            ->get(route("membership_success", ["application_id" => $applicationId, "payment_method" => $paymentMethod]));
    }

    /**
     * The reference minted at step 4 survives the handoff into suma-crm's
     * own review queue (docs/civicrm-replacement.md, "Membership
     * application review moves to suma-crm") — this application is
     * non-reduced, so nothing blocks that push from firing immediately;
     * see testANonReducedApplicationPushesToSumaCrmAndIsRemovedLocallyAfterCheckout()
     * for the queue-push assertions themselves.
     */
    public function testTheFormRedirectsToTheHostedCheckoutAndKeepsTheReference(): void
    {
        $this->fakeCrm();

        $this->applyThrough()
            ->assertRedirect("https://payments.example.com/checkout/abc");

        // The push into suma-crm's own review queue no longer fires right
        // here — the payment method isn't known yet at this point (chosen
        // on suma-payments' own picker page, not this form); see
        // testANonReducedApplicationPushesToSumaCrmAndIsMarkedPushedAfterCheckout()
        // for what happens once the applicant returns.
        Http::assertNotSent(fn ($request) => str($request->url())->contains("/api/membership-applications"));

        $application = MembershipApplication::where("payment_reference", self::REFERENCE)->sole();
        $this->assertNull($application->payment_method);
    }

    public function testTheCheckoutCallSendsTheMonthlyFeeAndTheApplicantsOwnDetails(): void
    {
        $this->fakeCrm();

        $this->applyThrough();

        Http::assertSent(function ($request) {
            if (!str($request->url())->contains("/api/membership-checkouts")) {
                return false;
            }

            // Der Monatsbeitrag, nicht der je Intervall — suma-crm rechnet um
            // (gap #32). Bei „monthly“ sind beide gleich; die Zusicherung
            // steht hier trotzdem, weil sie die Richtung festhält. Keine
            // `payment_method` mehr im Payload — die Wahl trifft der
            // Antragsteller erst bei suma-payments.
            return $request["amount"] == 10.00
                && $request["interval"] === "monthly"
                && !isset($request["payment_method"])
                && $request["email"] === "test@example.com"
                && $request["name"] === "Test Person"
                && isset($request["cancel_url"]);
        });
    }

    /**
     * The abort link on every suma-payments checkout page needs somewhere
     * real to send an aborting payer back to — the existing
     * `membership_abort` action, which already deletes the in-progress
     * application (see `abortApplication()`).
     */
    public function testTheCancelUrlPointsAtTheExistingAbortAction(): void
    {
        $this->fakeCrm();

        $this->applyThrough();

        $application = MembershipApplication::where("payment_reference", self::REFERENCE)->sole();

        Http::assertSent(fn ($request) => str($request->url())->contains("/api/membership-checkouts")
            && $request["cancel_url"] === route("membership_abort", ["application_id" => $application->id]));
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

        $this->applyThrough()->assertRedirect("https://payments.example.com/checkout/abc");

        // Not read off the just-created application: for a non-reduced
        // application it's already gone (pushed to suma-crm's own queue,
        // see maybePushToSumaCrm()) by the time this line runs — the real
        // assertion is that the flow never created a directdebit row at all.
        $this->assertSame(0, MembershipPaymentDirectdebit::count());
    }

    /**
     * Antwortet suma-crm nicht, bleibt der Antrag ohne Zahlungsart stehen —
     * und nicht mit einer, hinter der kein Mandat steht.
     */
    public function testAnUnreachableCrmLeavesTheApplicationWithoutAPaymentMethod(): void
    {
        $this->fakeCrm(checkoutStatus: 500);

        $this->applyThrough();

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

    /**
     * docs/civicrm-replacement.md, "Membership application review moves to
     * suma-crm" — once step 4 (§1.4) completes for a non-reduced, non-
     * company, non-update application, it moves straight into suma-crm's
     * own review queue rather than waiting on adminAccept()/adminDeny()
     * here. The local row stays (not deleted): success() still resolves it
     * by id for the confirmation page the applicant is redirected to right
     * after this — only adminIndex()'s own list stops offering it.
     */
    public function testANonReducedApplicationPushesToSumaCrmAndIsMarkedPushedAfterCheckout(): void
    {
        $this->fakeCrm();

        $this->applyThrough()->assertRedirect("https://payments.example.com/checkout/abc");

        // Not orderBy(desc)->first(): this app's test database is a real,
        // persistent one shared with manual testing, not a throwaway
        // in-memory one — filtered by this test's own unique reference
        // instead of trusting "the newest row" to be the one it just made.
        $application = MembershipApplication::where("payment_reference", self::REFERENCE)->sole();

        // Nothing pushes yet — the payment method isn't known until the
        // applicant returns from suma-payments' own picker.
        Http::assertNotSent(fn ($request) => str($request->url())->contains("/api/membership-applications"));

        $this->returnFromCheckout($application->id, "directdebit")->assertOk();

        Http::assertSent(function ($request) {
            if (!str($request->url())->contains("/api/membership-applications")) {
                return false;
            }

            return $request["first_name"] === "Test"
                && $request["last_name"] === "Person"
                && $request["email"] === "test@example.com"
                && $request["payment_reference"] === self::REFERENCE
                && $request["payment_method"] === "directdebit"
                && $request["reduced"] === false;
        });

        $application->refresh();
        $this->assertSame("directdebit", $application->payment_method);
        $this->assertNotNull($application->pushed_to_crm_at);

        $this->get(route("membership_admin_overview"))->assertDontSee("test@example.com");
    }

    /**
     * Reduction review runs entirely in MetaGer (out of scope for this
     * migration) — an application must not reach suma-crm's queue while its
     * reduced-fee proof is still pending, even though step 4 has otherwise
     * completed.
     */
    public function testAReducedApplicationWaitsForReductionApprovalBeforePushing(): void
    {
        $this->fakeCrm();

        $application = MembershipApplication::create([
            "locale" => "de-DE",
            "amount" => 3.00,
            "interval" => "monthly",
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
        $reduction = $application->reduction()->create([
            "file_path" => storage_path("metager/does-not-exist.pdf"),
            "file_mimetype" => "application/pdf",
        ]);

        // Simulates the return trip from suma-payments — the reduction is
        // still pending, so this must not push even though payment_method
        // just became known.
        $this->returnFromCheckout($application->id, "directdebit");

        Http::assertNotSent(fn ($request) => str($request->url())->contains("/api/membership-applications"));
        $application->refresh();
        $this->assertSame("directdebit", $application->payment_method);
        $this->assertNull($application->pushed_to_crm_at);

        $this->post(route("membership_admin_reduction_accept"), [
            "id" => $reduction->id,
            "reduction_until" => now()->addYear()->toDateString(),
        ]);

        Http::assertSent(fn ($request) => str($request->url())->contains("/api/membership-applications")
            && $request["reduced"] === true);
        $this->assertNotNull(MembershipApplication::find($application->id)->pushed_to_crm_at);
    }

    /**
     * A failed push must not lose the application — it falls back to the
     * legacy adminAccept()/adminDeny() review path here, exactly as before
     * this queue existed.
     */
    public function testAFailedPushLeavesTheApplicationInPlace(): void
    {
        $this->fakeCrm(applicationPushStatus: 500);

        $this->applyThrough()->assertRedirect("https://payments.example.com/checkout/abc");

        $application = MembershipApplication::where("payment_reference", self::REFERENCE)->sole();
        $this->returnFromCheckout($application->id, "directdebit");

        Http::assertSent(fn ($request) => str($request->url())->contains("/api/membership-applications"));
        $this->assertNull($application->fresh()->pushed_to_crm_at);
    }

    /**
     * suma-crm's own intake still rejects a company application outright —
     * no contact-person fields are collected here to send (§ adminAccept()'s
     * own guard) — so it must never be pushed, and stays fully on the
     * legacy review path.
     */
    public function testACompanyApplicationIsNeverPushed(): void
    {
        $this->fakeCrm();

        $url = $this->step("/de-DE/membership", [
            "type" => "company",
            "company" => "Acme GmbH",
            "employees" => "1-19",
            "email" => "info@acme.example",
        ])->headers->get("Location");
        $url = $this->step($url, ["amount" => "20.00"])->headers->get("Location");
        $url = $this->step($url, ["interval" => "monthly"])->headers->get("Location");
        $this->step($url, []);

        $application = MembershipApplication::where("payment_reference", self::REFERENCE)->sole();
        $this->returnFromCheckout($application->id, "directdebit");

        Http::assertNotSent(fn ($request) => str($request->url())->contains("/api/membership-applications"));
        $this->assertNull($application->fresh()->pushed_to_crm_at);
    }

    /**
     * A revisited or refreshed success page must not notify the admin or
     * push a second time — `payment_method` already being set is what
     * guards the trigger block in success() from running twice.
     */
    public function testRevisitingTheSuccessPageDoesNotPushOrNotifyTwice(): void
    {
        $this->fakeCrm();

        $this->applyThrough()->assertRedirect("https://payments.example.com/checkout/abc");
        $application = MembershipApplication::where("payment_reference", self::REFERENCE)->sole();

        $pushCount = fn () => Http::recorded(fn ($request) => str($request->url())->contains("/api/membership-applications"))->count();

        $this->returnFromCheckout($application->id, "directdebit")->assertOk();
        $this->assertSame(1, $pushCount());

        $this->returnFromCheckout($application->id, "directdebit")->assertOk();
        $this->assertSame(1, $pushCount());
    }
}
