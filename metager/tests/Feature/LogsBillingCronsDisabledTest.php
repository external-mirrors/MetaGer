<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * The Logs feature's billing crons are not scheduled.
 *
 * The Logs API has no customers, so `logs:create-order` and
 * `logs:create-invoice` had nothing to bill — but they still ran daily, and
 * `logs:create-invoice` failed every run on a missing `invoice_id` column
 * (GlitchTip, project MetaGer). The commands are kept, unscheduled, so the
 * feature can be built on again; this pins both halves of that.
 *
 * `logs:gather` and `logs:truncate` share the prefix but not the feature:
 * they drain the search query log from Redis into Postgres and must keep
 * running.
 */
class LogsBillingCronsDisabledTest extends TestCase
{
    private const DISABLED = [
        "logs:create-order",
        "logs:create-invoice",
    ];

    private function scheduled(): string
    {
        return collect(app(Schedule::class)->events())
            ->map(fn($event) => $event->command ?? "")
            ->implode("\n");
    }

    public function testTheBillingCommandsAreNotScheduled(): void
    {
        foreach (self::DISABLED as $command) {
            $this->assertStringNotContainsString($command, $this->scheduled());
        }
    }

    public function testTheBillingCommandsAreStillRegistered(): void
    {
        $registered = array_keys(Artisan::all());

        foreach (self::DISABLED as $command) {
            $this->assertContains($command, $registered);
        }
    }

    public function testTheQueryLogCommandsAreStillScheduled(): void
    {
        $this->assertStringContainsString("logs:gather", $this->scheduled());
        $this->assertStringContainsString("logs:truncate", $this->scheduled());
    }
}
