<?php

namespace Tests\Unit\Assoc;

use App\Assoc\DebitCreator;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Membership;
use App\Models\Assoc\RecurContribution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * Ported from de.suma-ev.donation-debit's Membership.CreateDebits and
 * RecurContribution.CreateDebits — see DebitCreator's docblock for the two
 * derived "is a debit already in flight" guards this uses instead of the
 * foreign keys the legacy schema had.
 */
class DebitCreatorTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        Carbon::setTestNow(Carbon::parse("2026-02-01"));
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

    private function pastDebit(Contact $contact, array $overrides = []): Debit
    {
        return Debit::create(array_merge([
            "contact_id" => $contact->id,
            "source" => "membership",
            "iban" => "DE02120300000000202051",
            "bic" => "GENODEF1S02",
            "account_holder" => "Familie Lovelace",
            "amount" => "5.00",
            "mandate" => "M1",
            "mandate_date" => "2020-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => "2026-01-01",
        ], $overrides));
    }

    private function membership(Contact $contact, array $overrides = []): Membership
    {
        return Membership::create(array_merge([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "5.00",
            "payment_method" => "directdebit",
            "payment_reference" => "M1",
            "join_date" => "2020-01-01",
            "standing" => "active",
            "end_date" => "2026-02-10",
        ], $overrides));
    }

    public function testCreatesADebitForADueDirectDebitMembership(): void
    {
        $contact = $this->contact();
        $this->pastDebit($contact);
        $membership = $this->membership($contact);

        $created = (new DebitCreator())->createForDueMemberships();

        $this->assertCount(1, $created);
        $debit = $created->first();
        $this->assertSame("membership", $debit->source);
        $this->assertSame($contact->id, $debit->contact_id);
        $this->assertSame("DE02120300000000202051", $debit->iban);
        $this->assertSame("GENODEF1S02", $debit->bic);
        $this->assertSame("Familie Lovelace", $debit->account_holder);
        $this->assertSame("5.00", $debit->amount);
        $this->assertSame("M1", $debit->mandate);
        $this->assertSame("pending", $debit->status);
        $this->assertSame("2026-02-10", $debit->due_date->format("Y-m-d"));
        $this->assertSame("Mitgliedsbeitrag Feb 2026", $debit->reference);
        $this->assertStringStartsWith("P", $debit->end_to_end_reference);
    }

    public function testBuildsASpanningReferenceForAQuarterlyMembership(): void
    {
        $contact = $this->contact();
        $this->pastDebit($contact);
        $this->membership($contact, ["interval" => "quarterly"]);

        $created = (new DebitCreator())->createForDueMemberships();

        $this->assertSame("Mitgliedsbeitrag Feb 2026 - Apr 2026", $created->first()->reference);
    }

    public function testSkipsAMembershipNotYetDue(): void
    {
        $contact = $this->contact();
        $this->pastDebit($contact);
        $this->membership($contact, ["end_date" => "2026-06-01"]);

        $created = (new DebitCreator())->createForDueMemberships();

        $this->assertCount(0, $created);
    }

    public function testSkipsAMembershipThatAlreadyHasAPendingDebit(): void
    {
        $contact = $this->contact();
        $this->pastDebit($contact, ["status" => "pending"]);
        $this->membership($contact);

        $created = (new DebitCreator())->createForDueMemberships();

        $this->assertCount(0, $created);
        $this->assertSame(1, Debit::count());
    }

    public function testSkipsANonDirectDebitMembership(): void
    {
        $contact = $this->contact();
        $this->membership($contact, ["payment_method" => "banktransfer", "payment_reference" => null]);

        $created = (new DebitCreator())->createForDueMemberships();

        $this->assertCount(0, $created);
    }

    public function testSkipsATerminatedMembership(): void
    {
        $contact = $this->contact();
        $this->pastDebit($contact);
        $this->membership($contact, ["standing" => "terminated"]);

        $created = (new DebitCreator())->createForDueMemberships();

        $this->assertCount(0, $created);
    }

    public function testSkipsADueMembershipWithNoDebitHistoryToSnapshotBankDetailsFrom(): void
    {
        $contact = $this->contact();
        $this->membership($contact);

        $created = (new DebitCreator())->createForDueMemberships();

        $this->assertCount(0, $created);
        $this->assertSame(0, Debit::count());
    }

    public function testCreatesADebitForADueRecurContribution(): void
    {
        $contact = $this->contact();
        $recur = RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "bic" => "GENODEF1S02",
            "account_holder" => "Ada Lovelace",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2020-01-01",
            "frequency" => "monthly",
            "active" => true,
            "next_due_date" => "2026-02-10",
        ]);

        $created = (new DebitCreator())->createForDueRecurContributions();

        $this->assertCount(1, $created);
        $debit = $created->first();
        $this->assertSame("donation", $debit->source);
        $this->assertSame("10.00", $debit->amount);
        $this->assertSame("S1", $debit->mandate);
        $this->assertSame("2026-02-10", $debit->due_date->format("Y-m-d"));
        $this->assertSame("Vielen Dank für Ihre Spende Feb 2026", $debit->reference);
        $this->assertSame("2026-03-10", $recur->fresh()->next_due_date->format("Y-m-d"));
    }

    public function testFillsInAMissingNextDueDateBeforeCreating(): void
    {
        Carbon::setTestNow(Carbon::parse("2026-02-05"));
        $contact = $this->contact();
        $recur = RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Ada Lovelace",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2020-01-01",
            "frequency" => "monthly",
            "active" => true,
            "next_due_date" => null,
        ]);

        $created = (new DebitCreator())->createForDueRecurContributions();

        $this->assertCount(1, $created);
        $this->assertSame("2026-02-03", $created->first()->due_date->format("Y-m-d"));
        $this->assertSame("2026-03-03", $recur->fresh()->next_due_date->format("Y-m-d"));
    }

    public function testFallsBackToTheContactNameWhenARecurContributionHasNoAccountHolderOfItsOwn(): void
    {
        $contact = $this->contact();
        RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => null,
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2020-01-01",
            "frequency" => "monthly",
            "active" => true,
            "next_due_date" => "2026-02-10",
        ]);

        $created = (new DebitCreator())->createForDueRecurContributions();

        $this->assertSame("Ada Lovelace", $created->first()->account_holder);
    }

    public function testDoesNotDuplicateADebitForARecurContributionAlreadyPending(): void
    {
        $contact = $this->contact();
        $recur = RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2020-01-01",
            "frequency" => "monthly",
            "active" => true,
            "next_due_date" => "2026-02-10",
        ]);
        Debit::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Ada Lovelace",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2020-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-existing",
            "due_date" => "2026-02-10",
        ]);

        $created = (new DebitCreator())->createForDueRecurContributions();

        $this->assertCount(0, $created);
        $this->assertSame(1, Debit::count());
        // The due date still advances even though nothing new was created —
        // ported as-is from RecurContribution::CreateDebits, see the
        // class docblock.
        $this->assertSame("2026-03-10", $recur->fresh()->next_due_date->format("Y-m-d"));
    }

    public function testSkipsAnInactiveRecurContribution(): void
    {
        $contact = $this->contact();
        RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2020-01-01",
            "frequency" => "monthly",
            "active" => false,
            "next_due_date" => "2026-02-10",
        ]);

        $created = (new DebitCreator())->createForDueRecurContributions();

        $this->assertCount(0, $created);
    }
}
