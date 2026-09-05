<?php

namespace Tests\Unit\Assoc;

use App\Assoc\PaymentReminderProcessor;
use App\Mail\Assoc\PaymentReminder;
use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * Design decision 2 of the payment-ledger design pass
 * (docs/civicrm-replacement.md) — see PaymentReminderProcessor's own
 * docblock for the confirmed legacy intervals this ports.
 */
class PaymentReminderProcessorTest extends TestCase
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

    private function contact(): Contact
    {
        return Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
    }

    /**
     * A membership with an unpaid charge due $weeksOverdue weeks before
     * "now" (Carbon::setTestNow() above) — a banktransfer default, since
     * that's the only payment method PaymentReminderProcessor ever reminds.
     */
    private function overdueMembership(Contact $contact, int $weeksOverdue, array $overrides = []): Membership
    {
        $membership = Membership::create(array_merge([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "10.00",
            "payment_method" => "banktransfer",
            "standing" => "active",
            "end_date" => Carbon::today()->subWeeks($weeksOverdue),
        ], $overrides));

        $debit = Debit::create([
            "contact_id" => $contact->id,
            "membership_id" => $membership->id,
            "source" => "membership",
            "account_holder" => $contact->name(),
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => Carbon::today()->subWeeks($weeksOverdue),
        ]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "charge", "amount" => "10.00"]);

        return $membership;
    }

    public function testSendsFirstReminderAtTwoWeeksOverdueAndSetsStage(): void
    {
        Mail::fake();
        $membership = $this->overdueMembership($this->contact(), 2);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(1, $result["sent"]["first"]);
        $this->assertSame("first", $membership->fresh()->reminder_stage);
        Mail::assertSent(PaymentReminder::class, fn (PaymentReminder $mail) => $mail->stage === PaymentReminder::STAGE_FIRST);
    }

    public function testDoesNotResendTheFirstReminderOnASubsequentRun(): void
    {
        Mail::fake();
        $membership = $this->overdueMembership($this->contact(), 2, ["reminder_stage" => "first"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(0, $result["sent"]["first"]);
        $this->assertSame(0, $result["sent"]["second"]);
        Mail::assertNothingSent();
        $this->assertSame("first", $membership->fresh()->reminder_stage);
    }

    public function testSendsSecondReminderAtFourWeeksOverdueAndDoesNotResendIt(): void
    {
        Mail::fake();
        $contact = $this->contact();
        $membership = $this->overdueMembership($contact, 4, ["reminder_stage" => "first"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(1, $result["sent"]["second"]);
        $this->assertSame("second", $membership->fresh()->reminder_stage);

        Mail::fake();
        $secondRun = (new PaymentReminderProcessor())->process();
        $this->assertSame(0, $secondRun["sent"]["second"]);
        Mail::assertNothingSent();
    }

    public function testTerminatesAtSixWeeksOverdueAndSendsTheTerminatedStage(): void
    {
        Mail::fake();
        $membership = $this->overdueMembership($this->contact(), 6, ["reminder_stage" => "second"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(1, $result["sent"]["terminated"]);
        $this->assertSame("terminated", $membership->fresh()->standing);
        $this->assertNull($membership->fresh()->reminder_stage);
        Mail::assertSent(PaymentReminder::class, fn (PaymentReminder $mail) => $mail->stage === PaymentReminder::STAGE_TERMINATED);
    }

    public function testDirectDebitMembershipIsNeverReminded(): void
    {
        Mail::fake();
        $this->overdueMembership($this->contact(), 6, ["payment_method" => "directdebit"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(["first" => 0, "second" => 0, "terminated" => 0], $result["sent"]);
        Mail::assertNothingSent();
    }

    /**
     * Escalating or terminating someone who was never actually notified
     * isn't defensible — a membership with no resolvable email is skipped
     * entirely, not just the send. Contact::email is NOT NULL, so the only
     * real way this happens is a company payer with no contact person set.
     */
    public function testAMembershipWithNoResolvableEmailIsSkippedEntirelyAndCounted(): void
    {
        Mail::fake();
        $company = Company::create(["name" => "ACME GmbH"]);
        $membership = Membership::create([
            "company_id" => $company->id,
            "membership_type" => "company",
            "interval" => "monthly",
            "amount" => "10.00",
            "payment_method" => "banktransfer",
            "standing" => "active",
            "reminder_stage" => "second",
            "end_date" => Carbon::today()->subWeeks(6),
        ]);
        $debit = Debit::create([
            "company_id" => $company->id,
            "membership_id" => $membership->id,
            "source" => "membership",
            "account_holder" => "ACME GmbH",
            "amount" => "10.00",
            "mandate" => "M3",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => Carbon::today()->subWeeks(6),
        ]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "charge", "amount" => "10.00"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(1, $result["skipped_no_email"]);
        $this->assertSame(["first" => 0, "second" => 0, "terminated" => 0], $result["sent"]);
        $this->assertSame("active", $membership->fresh()->standing);
        Mail::assertNothingSent();
    }

    public function testACompanyPayerIsRemindedViaItsContactPerson(): void
    {
        Mail::fake();
        $contactPerson = $this->contact();
        $company = Company::create(["name" => "ACME GmbH", "contact_person_id" => $contactPerson->id]);
        $membership = Membership::create([
            "company_id" => $company->id,
            "membership_type" => "company",
            "interval" => "monthly",
            "amount" => "10.00",
            "payment_method" => "banktransfer",
            "standing" => "active",
            "end_date" => Carbon::today()->subWeeks(2),
        ]);
        $debit = Debit::create([
            "company_id" => $company->id,
            "membership_id" => $membership->id,
            "source" => "membership",
            "account_holder" => "ACME GmbH",
            "amount" => "10.00",
            "mandate" => "M2",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => Carbon::today()->subWeeks(2),
        ]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "charge", "amount" => "10.00"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(1, $result["sent"]["first"]);
        Mail::assertSent(PaymentReminder::class, fn (PaymentReminder $mail) => $mail->hasTo($contactPerson->email));
    }

    public function testATerminatedMembershipIsNotACandidate(): void
    {
        Mail::fake();
        $this->overdueMembership($this->contact(), 6, ["standing" => "terminated"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(["first" => 0, "second" => 0, "terminated" => 0], $result["sent"]);
        Mail::assertNothingSent();
    }

    public function testBalanceClearingResetsTheReminderStage(): void
    {
        Mail::fake();
        $contact = $this->contact();
        $membership = $this->overdueMembership($contact, 2, ["reminder_stage" => "first"]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "payment", "amount" => "10.00", "channel" => "banktransfer"]);

        $result = (new PaymentReminderProcessor())->process();

        $this->assertSame(["first" => 0, "second" => 0, "terminated" => 0], $result["sent"]);
        $this->assertNull($membership->fresh()->reminder_stage);
        Mail::assertNothingSent();
    }
}
