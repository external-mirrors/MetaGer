<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class SendPaymentRemindersCommandTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        Carbon::setTestNow(Carbon::parse("2026-04-01"));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function testReportsHowManyRemindersItSent(): void
    {
        Mail::fake();
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "10.00",
            "payment_method" => "banktransfer",
            "end_date" => Carbon::today()->subWeeks(2),
        ]);
        $debit = Debit::create([
            "contact_id" => $contact->id,
            "membership_id" => $membership->id,
            "source" => "membership",
            "account_holder" => "Ada Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-1",
            "due_date" => Carbon::today()->subWeeks(2),
        ]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "charge", "amount" => "10.00"]);

        Artisan::call("assoc:send-payment-reminders");
        $output = Artisan::output();

        $this->assertStringContainsString("Erste Erinnerung versendet: 1", $output);
        $this->assertStringContainsString("Zweite Erinnerung versendet: 0", $output);
        $this->assertStringContainsString("Mitgliedschaften gekündigt: 0", $output);
        $this->assertStringContainsString("Übersprungen (keine E-Mail-Adresse): 0", $output);
    }
}
