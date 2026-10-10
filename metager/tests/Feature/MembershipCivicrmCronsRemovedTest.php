<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * The two membership crons that read CiviCRM are gone, and must stay gone.
 *
 * Reminders, chargeback handling and recurring PayPal charges belong to
 * suma-payments since the CiviCRM cutover (suma-crm's docs/cutover-plan.md,
 * runbook step 1: "stop metager's MembershipPaymentReminder and
 * MembershipPayPalPayments"). That step was missed. CiviCRM was kept running
 * read-only as a reference (runbook step 8), so its API kept answering — with
 * data that stopped receiving payments at the cutover. Every bank-transfer
 * member therefore looked more overdue each day, and on 2026-10-10
 * `membership:payment-reminder` sent a real "membership expired" mail to a
 * member off that frozen data.
 *
 * `membership:paypal-payments` had the same problem with money instead of
 * mail: it charged PayPal vaults for memberships CiviCRM still considered due,
 * in parallel with suma-payments' own `payments:process-due-collections`.
 */
class MembershipCivicrmCronsRemovedTest extends TestCase
{
    private const REMOVED = [
        "membership:payment-reminder",
        "membership:paypal-payments",
    ];

    public function testTheCommandsAreNotScheduled(): void
    {
        $scheduled = collect(app(Schedule::class)->events())
            ->map(fn($event) => $event->command ?? "")
            ->implode("\n");

        foreach (self::REMOVED as $command) {
            $this->assertStringNotContainsString($command, $scheduled);
        }
    }

    public function testTheCommandsAreNotRegistered(): void
    {
        $registered = array_keys(Artisan::all());

        foreach (self::REMOVED as $command) {
            $this->assertNotContains($command, $registered);
        }
    }
}
