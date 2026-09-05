<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testAMembershipCastsItsDatesAndBooleanReduced(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => true,
            "interval" => "monthly",
            "amount" => "4.00",
            "payment_method" => "banktransfer",
            "start_date" => "2026-01-01",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertTrue($reloaded->reduced);
        $this->assertInstanceOf(\Carbon\Carbon::class, $reloaded->start_date);
        $this->assertSame("2026-01-01", $reloaded->start_date->toDateString());
    }

    public function testStandingDefaultsToActive(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);

        $this->assertSame("active", $membership->fresh()->standing);
    }

    /**
     * "terminated"/"deceased" are a deliberate admin action, not a payment
     * state — the CiviCRM Zahlungsstatus values this replaces conflated the
     * two (Ausgetreten/Verstorben alongside Okay/Erste Zahlungserinnerung
     * etc.). Collection progress for banktransfer/directdebit members is
     * derived from end_date and assoc_debits, not stored here — see the
     * migration's own comment.
     */
    public function testEveryKnownStandingValueIsAccepted(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        foreach (["active", "terminated", "deceased"] as $standing) {
            $membership = Membership::create([
                "contact_id" => $contact->id,
                "membership_type" => "person",
                "interval" => "annual",
                "amount" => "17.00",
                "payment_method" => "banktransfer",
                "standing" => $standing,
            ]);
            $this->assertSame($standing, Membership::findOrFail($membership->id)->standing);
            $membership->delete();
        }
    }

    /**
     * "supporting" (Fördermitglied) is schema-only prep: suma-ev's Satzung
     * doesn't define that category yet (a Satzungsänderung is drafted, not
     * passed/registered), so nothing sets this value today and nothing reads
     * it — this only pins that the column exists, defaults every existing
     * membership to "full", and accepts "supporting" once it's needed.
     */
    public function testCategoryDefaultsToFullAndAcceptsSupporting(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);

        $this->assertSame("full", $membership->fresh()->category);

        $membership->update(["category" => "supporting"]);
        $this->assertSame("supporting", $membership->fresh()->category);
    }

    public function testPaymentMethodAndAmountRoundTrip(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "19.99",
            "payment_method" => "directdebit",
            "payment_reference" => "M20260101120000",
            "join_date" => "2026-01-01",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertSame("19.99", $reloaded->amount);
        $this->assertSame("directdebit", $reloaded->payment_method);
        $this->assertSame("M20260101120000", $reloaded->payment_reference);
        $this->assertNull($reloaded->paypal_vault_id);
        $this->assertSame("2026-01-01", $reloaded->join_date->toDateString());
    }

    /**
     * "exempt" replaces CiviCRM's two separate membership types for
     * honorary/reciprocity members (Ehrenmitglied/Gegenseitigkeit) — both mean
     * "no dues are ever collected," which is a billing fact, not a membership
     * type. amount stays 0.00 here; ChargeKeys' own 5€/month default for a
     * computed price of 0 is a key-charging concern, not something this
     * column needs to encode.
     */
    public function testExemptIsAValidPaymentMethodWithNoDuesCollected(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "0.00",
            "payment_method" => "exempt",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertSame("exempt", $reloaded->payment_method);
        $this->assertSame("0.00", $reloaded->amount);
        $this->assertNull($reloaded->payment_reference);
    }

    public function testReducedUntilLocaleAndMastodonIdRoundTrip(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => true,
            "interval" => "monthly",
            "amount" => "2.50",
            "payment_method" => "directdebit",
            "reduced_until" => "2027-01-01",
            "locale" => "de-DE",
            "mastodon_id" => "12345",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertSame("2027-01-01", $reloaded->reduced_until->toDateString());
        $this->assertSame("de-DE", $reloaded->locale);
        $this->assertSame("12345", $reloaded->mastodon_id);
    }

    public function testCivicrmIdMustBeUniqueWhenPresent(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $otherContact = Contact::create(["first_name" => "Grace", "last_name" => "Hopper", "email" => "grace@example.com"]);
        Membership::create([
            "civicrm_id" => 42,
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);

        $this->expectException(QueryException::class);
        Membership::create([
            "civicrm_id" => 42,
            "contact_id" => $otherContact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);
    }

    public function testAnUnknownStandingIsRejectedByTheDatabase(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        $this->expectException(QueryException::class);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
            "standing" => "not_a_real_standing",
        ]);
    }

    public function testLedgerBalanceIsZeroWithNoEntries(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);

        $this->assertSame("0.00", $membership->ledgerBalance());
    }

    /**
     * charge/chargeback_fee add to what's owed; payment/waiver reduce it —
     * see Membership::ledgerBalance()'s docblock for the sign convention.
     * A full payment plus a chargeback fee that's only partially covered
     * leaves the fee's shortfall as the balance.
     */
    public function testLedgerBalanceSumsChargesPaymentsAndFeesByTheirSign(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "60.00",
            "payment_method" => "directdebit",
        ]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "charge", "amount" => "60.00"]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "payment", "amount" => "60.00", "channel" => "directdebit"]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "chargeback_fee", "amount" => "8.50"]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "payment", "amount" => "5.00", "channel" => "banktransfer"]);

        $this->assertSame("3.50", $membership->ledgerBalance());
    }

    /**
     * A waiver writes off what's owed without a payment; a refund adds back
     * what a payment had reduced, since the money it paid down is no longer
     * with the association.
     */
    public function testWaiverAndRefundMoveTheBalanceOppositeDirections(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "60.00",
            "payment_method" => "banktransfer",
        ]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "charge", "amount" => "60.00"]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "waiver", "amount" => "60.00"]);

        $this->assertSame("0.00", $membership->ledgerBalance());

        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "payment", "amount" => "20.00", "channel" => "banktransfer"]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "refund", "amount" => "20.00", "channel" => "sepa_credit_transfer"]);

        $this->assertSame("0.00", $membership->fresh()->ledgerBalance());
    }

    private function debitDueOn(Membership $membership, string $dueDate): Debit
    {
        return Debit::create([
            "contact_id" => $membership->contact_id,
            "membership_id" => $membership->id,
            "source" => "membership",
            "account_holder" => "Ada Lovelace",
            "amount" => $membership->amount,
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => $dueDate,
        ]);
    }

    /**
     * PaymentReminderProcessor's whole reason to exist (design decision 2):
     * measuring a shortfall's age needs to know which charge is actually
     * unpaid, not just that the balance is positive.
     */
    public function testOldestUnpaidChargeDueDateIsNullWhenTheBalanceIsCovered(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "quarterly",
            "amount" => "60.00",
            "payment_method" => "banktransfer",
        ]);
        $debit = $this->debitDueOn($membership, "2026-03-01");
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "charge", "amount" => "60.00"]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "payment", "amount" => "60.00", "channel" => "banktransfer"]);

        $this->assertNull($membership->fresh()->oldestUnpaidChargeDueDate());
    }

    public function testOldestUnpaidChargeDueDateIsTheUnderpaidChargesDueDate(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "quarterly",
            "amount" => "60.00",
            "payment_method" => "banktransfer",
        ]);
        $debit = $this->debitDueOn($membership, "2026-03-01");
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "charge", "amount" => "60.00"]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "payment", "amount" => "40.00", "channel" => "banktransfer"]);

        $this->assertSame("2026-03-01", $membership->fresh()->oldestUnpaidChargeDueDate()->toDateString());
    }

    /**
     * A payment that fully covers the oldest charge but leaves a later one
     * untouched must skip over the covered one — a plain "is there any
     * unpaid entry" scan would wrongly report the covered charge.
     */
    public function testOldestUnpaidChargeDueDateSkipsFullyCoveredChargesInFifoOrder(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "30.00",
            "payment_method" => "banktransfer",
        ]);
        $first = $this->debitDueOn($membership, "2026-01-01");
        $second = $this->debitDueOn($membership, "2026-02-01");
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $first->id, "kind" => "charge", "amount" => "30.00"]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $second->id, "kind" => "charge", "amount" => "30.00"]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $first->id, "kind" => "payment", "amount" => "30.00", "channel" => "banktransfer"]);

        $this->assertSame("2026-02-01", $membership->fresh()->oldestUnpaidChargeDueDate()->toDateString());
    }

    /**
     * A chargeback fee is debt tied to the bounced debit itself (see
     * BankStatementMatcher::confirmChargeback()) — it must count toward
     * the shortfall the same as an unpaid charge would.
     */
    public function testOldestUnpaidChargeDueDateTreatsAChargebackFeeAsDebt(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "30.00",
            "payment_method" => "banktransfer",
        ]);
        $debit = $this->debitDueOn($membership, "2026-01-01");
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "charge", "amount" => "30.00"]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "payment", "amount" => "30.00", "channel" => "banktransfer"]);
        LedgerEntry::create(["membership_id" => $membership->id, "debit_id" => $debit->id, "kind" => "chargeback_fee", "amount" => "5.00"]);

        $this->assertSame("2026-01-01", $membership->fresh()->oldestUnpaidChargeDueDate()->toDateString());
    }

    /**
     * A standalone admin-recorded refund (LedgerEntryController) carries no
     * debit_id at all — falls back to the entry's own created_at rather
     * than crashing on a null due_date.
     */
    public function testOldestUnpaidChargeDueDateFallsBackToCreatedAtWithNoLinkedDebit(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "30.00",
            "payment_method" => "banktransfer",
        ]);
        $entry = LedgerEntry::create(["membership_id" => $membership->id, "kind" => "refund", "amount" => "20.00", "channel" => "sepa_credit_transfer"]);

        $dueDate = $membership->fresh()->oldestUnpaidChargeDueDate();
        $this->assertNotNull($dueDate);
        $this->assertSame($entry->created_at->toDateTimeString(), $dueDate->toDateTimeString());
    }
}
