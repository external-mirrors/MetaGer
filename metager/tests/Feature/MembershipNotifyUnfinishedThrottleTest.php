<?php

namespace Tests\Feature;

use App\Console\Commands\MembershipNotifyUnfinished as Cmd;
use App\Mail\Membership\ApplicationUnfinished;
use App\Models\Membership\MembershipApplication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * `membership:notify-unfinished` mails the address typed into an abandoned
 * application. Nobody verified that address, so this mail is what a mail bomber
 * gets out of the form. It has a budget per hour and per day and sends at most
 * one mail per address; an application over budget is still deleted, only the
 * mail is skipped.
 */
class MembershipNotifyUnfinishedThrottleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        RateLimiter::clear(Cmd::HOURLY_LIMITER_KEY);
        RateLimiter::clear(Cmd::DAILY_LIMITER_KEY);
        Mail::fake();
    }

    private function abandoned(string $email): MembershipApplication
    {
        $application = MembershipApplication::create(["locale" => "de-DE"]);
        $application->contact()->create([
            "title" => "Neutral", "first_name" => "Test", "last_name" => "Person",
            "email" => $email, "application_id" => $application->id,
        ]);
        MembershipApplication::where("id", $application->id)->update(["updated_at" => now()->subHours(7)]);

        return $application;
    }

    private function run_command(): void
    {
        $this->artisan("membership:notify-unfinished")->assertSuccessful();
        // The command holds a five minute lock; a second run in one test needs it free.
        Cache::lock("console:commands:membership:notify-unfinished:lock")->forceRelease();
    }

    public function testAnAbandonedApplicationGetsOneReminderAndIsDeleted(): void
    {
        $application = $this->abandoned("real@example.com");

        $this->run_command();

        Mail::assertSent(ApplicationUnfinished::class, 1);
        $this->assertNull(MembershipApplication::find($application->id));
    }

    public function testOneAddressGetsOneReminderHoweverOftenItWasEntered(): void
    {
        $this->abandoned("victim@example.com");
        $this->abandoned("Victim@Example.com ");
        $this->abandoned("victim@example.com");

        $this->run_command();

        Mail::assertSent(ApplicationUnfinished::class, 1);
    }

    public function testAnAddressIsQuietOnLaterRunsToo(): void
    {
        $this->abandoned("victim@example.com");
        $this->run_command();
        $this->abandoned("victim@example.com");
        $this->run_command();

        Mail::assertSent(ApplicationUnfinished::class, 1);
    }

    public function testTheHourlyBudgetCapsTheMailsAndStillDeletesTheRest(): void
    {
        $ids = [];
        for ($i = 0; $i < Cmd::MAX_PER_HOUR + 5; $i++) {
            $ids[] = $this->abandoned("victim$i@example.com")->id;
        }

        $this->run_command();

        Mail::assertSent(ApplicationUnfinished::class, Cmd::MAX_PER_HOUR);
        $this->assertSame(0, MembershipApplication::whereIn("id", $ids)->count());
    }

    public function testTheDailyBudgetCapsTheMailsAcrossRuns(): void
    {
        for ($i = 0; $i < Cmd::MAX_PER_DAY + 5; $i++) {
            $this->abandoned("victim$i@example.com");
            // Every ten applications is one hourly run.
            if ($i % Cmd::MAX_PER_HOUR === Cmd::MAX_PER_HOUR - 1) {
                $this->run_command();
                RateLimiter::clear(Cmd::HOURLY_LIMITER_KEY);
            }
        }
        $this->run_command();

        Mail::assertSent(ApplicationUnfinished::class, Cmd::MAX_PER_DAY);
    }

    /**
     * A repeat of one victim's address must not use up the budget the real
     * applicants share.
     */
    public function testRepeatedAddressesDoNotEatTheBudget(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->abandoned("victim@example.com");
        }
        $this->abandoned("real@example.com");

        $this->run_command();

        Mail::assertSent(ApplicationUnfinished::class, 2);
    }
}
