<?php

namespace Tests\Feature;

use App\Http\Controllers\MembershipController;
use App\Models\Membership\MembershipApplication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Der erste Schritt des Aufnahmeantrags nimmt eine Mailadresse an, die
 * niemand bestätigt hat, und schickt später eine Mail an sie. Ein Skript, das
 * ihn abschickt, lässt uns damit an Fremde schreiben (Mailbombing).
 *
 * Was hier gepinnt ist, sind die Abweisungen im ersten Schritt: Honeypot,
 * Automationsflagge, Mindestzeit und ein Limit je IP. Jede weist ab, *bevor*
 * ein Antrag entsteht oder der Keyserver gefragt wird. Die Mailbudgets selbst
 * stehen in {@see MembershipNotifyUnfinishedThrottleTest}.
 */
class MembershipFirstStepAbuseTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Http::preventStrayRequests();
        Http::fake(["*/api/json/key/new" => Http::response(["key" => "a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d"])]);
    }

    /** Erster Schritt, mit einem Token, das vor `$issuedSecondsAgo` Sekunden ausgegeben wurde. */
    private function submit(array $extra = [], int $issuedSecondsAgo = 30, string $ip = "203.0.113.7"): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(["Origin" => config("app.url")])
            ->withServerVariables(["REMOTE_ADDR" => $ip])
            ->post("/de-DE/membership", array_merge([
                "_token" => Crypt::encrypt(now()->addHour()->subSeconds($issuedSecondsAgo)),
                "type" => "person",
                "title" => "Neutral",
                "firstname" => "Test",
                "lastname" => "Person",
                "email" => "test@example.com",
            ], $extra));
    }

    private function applicationCount(): int
    {
        return MembershipApplication::count();
    }

    public function testAPersonFillingTheFormGetsAnApplication(): void
    {
        $before = $this->applicationCount();

        $this->submit()->assertRedirect();

        $this->assertSame($before + 1, $this->applicationCount());
    }

    public function testAFilledHoneypotCreatesNothing(): void
    {
        $before = $this->applicationCount();

        $response = $this->submit([MembershipController::HONEYPOT_FIELD => "http://spam.example"]);

        $response->assertRedirect(route("membership_form"));
        $this->assertSame($before, $this->applicationCount());
        Http::assertNothingSent();
    }

    public function testAnAutomatedBrowserCreatesNothing(): void
    {
        $before = $this->applicationCount();

        // Gesetzt von membership.js, wenn navigator.webdriver true ist.
        $response = $this->submit([MembershipController::AUTOMATION_FIELD => "1"]);

        $response->assertRedirect(route("membership_form"));
        $this->assertSame($before, $this->applicationCount());
        Http::assertNothingSent();
    }

    public function testAnEmptyAutomationFieldIsWhatEveryVisitorWithoutJsSends(): void
    {
        $before = $this->applicationCount();

        $this->submit([MembershipController::AUTOMATION_FIELD => "", MembershipController::HONEYPOT_FIELD => ""]);

        $this->assertSame($before + 1, $this->applicationCount());
    }

    public function testAFormPostedTheSecondItWasIssuedCreatesNothing(): void
    {
        $before = $this->applicationCount();

        $response = $this->submit(issuedSecondsAgo: 0);

        $response->assertRedirect(route("membership_form"));
        $this->assertSame($before, $this->applicationCount());
        Http::assertNothingSent();
    }

    public function testAnEmptyTokenAndNoTokenAreStillRefusedByValidation(): void
    {
        $before = $this->applicationCount();

        $this->submit(["_token" => "garbage"]);

        $this->assertSame($before, $this->applicationCount());
    }

    /** Fünf erste Schritte dieser IP in der letzten Stunde, ohne fünf Anfragen abzuschicken. */
    private function fiveFirstStepsFrom(string $ip): void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit("membership:first-step:ip:" . $ip, 3600);
        }
    }

    public function testTheSixthFirstStepFromOneIpInAnHourIsRefused(): void
    {
        $this->fiveFirstStepsFrom("203.0.113.7");
        $before = $this->applicationCount();

        $this->submit()->assertStatus(429);

        $this->assertSame($before, $this->applicationCount());
        Http::assertNothingSent();
    }

    public function testTheFifthFirstStepFromOneIpStillGoesThrough(): void
    {
        for ($i = 0; $i < 4; $i++) {
            RateLimiter::hit("membership:first-step:ip:203.0.113.7", 3600);
        }

        $this->submit()->assertRedirect();
    }

    public function testAnotherIpIsNotAffectedByTheLimit(): void
    {
        $this->fiveFirstStepsFrom("203.0.113.7");

        $this->submit(ip: "198.51.100.9")->assertRedirect();
    }

    /**
     * Die späteren Schritte geben keine Adresse mehr an, das Limit gilt
     * deshalb nur dem ersten.
     */
    public function testLaterStepsAreNotLimited(): void
    {
        $this->fiveFirstStepsFrom("203.0.113.7");

        $application = MembershipApplication::create(["locale" => "de-DE"]);
        $application->contact()->create([
            "title" => "Neutral", "first_name" => "Test", "last_name" => "Person",
            "email" => "test@example.com", "application_id" => $application->id,
        ]);
        $this->submitLater($application, ["amount" => "10.00"], 0)->assertRedirect();
    }

    private function submitLater(MembershipApplication $application, array $fields, int $issuedSecondsAgo): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(["Origin" => config("app.url")])
            ->withServerVariables(["REMOTE_ADDR" => "203.0.113.7"])
            ->post("/de-DE/membership/" . $application->id, array_merge([
                "_token" => Crypt::encrypt(now()->addHour()->subSeconds($issuedSecondsAgo)),
            ], $fields));
    }

    /**
     * Ohne `application_id` leitet /membership inzwischen zu suma-crms eigenem
     * Antragsformular weiter (siehe MembershipController::contactData()) —
     * das Formular hier rendert nur noch für einen Antrag, der schon existiert.
     * Ein Antrag ohne Kontakt ist genau der Zustand, in dem die Abweisungen
     * des ersten Schritts greifen.
     */
    public function testTheFormCarriesBothTrapFieldsEmptyAndNotIntoItsAction(): void
    {
        $application = MembershipApplication::create(["locale" => "de-DE"]);

        $html = $this->get("/de-DE/membership/" . $application->id . "?" . MembershipController::HONEYPOT_FIELD . "=x&" . MembershipController::AUTOMATION_FIELD . "=1")
            ->assertOk()->getContent();

        $this->assertStringContainsString('name="' . MembershipController::HONEYPOT_FIELD . '"', $html);
        $this->assertStringContainsString('name="' . MembershipController::AUTOMATION_FIELD . '"', $html);
        $this->assertDoesNotMatchRegularExpression('/<form[^>]+action="[^"]*(' . MembershipController::HONEYPOT_FIELD . '|' . MembershipController::AUTOMATION_FIELD . ')/', $html);
    }
}
