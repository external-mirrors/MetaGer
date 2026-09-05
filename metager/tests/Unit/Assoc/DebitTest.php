<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class DebitTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    private function baseAttributes(): array
    {
        return [
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Ada Lovelace",
            "mandate_date" => "2026-01-01",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-02-01",
        ];
    }

    private function contact(): Contact
    {
        return Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
    }

    /**
     * SEPA debit amounts are the one place a float would quietly round —
     * this is the system of record for what goes into a pain.008 file.
     */
    public function testTheAmountRoundTripsExactlyAsADecimalString(): void
    {
        $contact = $this->contact();
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "19.99",
            "mandate" => "S1",
        ]));

        $this->assertSame("19.99", Debit::findOrFail($debit->id)->amount);
    }

    public function testBicIsNullableAndPreservedWhenPresent(): void
    {
        $contact = $this->contact();
        $withoutBic = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));
        $withBic = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S2",
            "end_to_end_reference" => "E2E-2",
            "bic" => "GENODEF1S01",
        ]));

        $this->assertNull($withoutBic->fresh()->bic);
        $this->assertSame("GENODEF1S01", $withBic->fresh()->bic);
    }

    public function testTheEndToEndReferenceMustBeUnique(): void
    {
        $contact = $this->contact();
        Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));

        $this->expectException(QueryException::class);
        Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));
    }

    /**
     * A mandate is reused for every collection made under it — a renewal or
     * a recurring instalment produces a new debit with the same mandate but
     * a distinct end-to-end reference. Only the latter may be unique.
     */
    public function testTheSameMandateCanBeUsedForMultipleDebits(): void
    {
        $contact = $this->contact();
        Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "end_to_end_reference" => "E2E-1",
        ]));
        $second = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "end_to_end_reference" => "E2E-2",
        ]));

        $this->assertSame("S1", $second->fresh()->mandate);
    }

    /**
     * assoc:import-civicrm re-runs need this to upsert rather than duplicate.
     */
    public function testCivicrmIdMustBeUniqueWhenPresent(): void
    {
        $contact = $this->contact();
        Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "civicrm_id" => 42,
        ]));

        $this->expectException(QueryException::class);
        Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S2",
            "end_to_end_reference" => "E2E-2",
            "civicrm_id" => 42,
        ]));
    }

    public function testStatusDefaultsToPending(): void
    {
        $contact = $this->contact();
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));

        $this->assertSame("pending", $debit->fresh()->status);
    }

    public function testItBelongsToWhicheverPayerCreatedIt(): void
    {
        $contact = $this->contact();
        $company = Company::create(["name" => "Analytical Engines Ltd"]);

        $contactDebit = Debit::create(array_merge($this->baseAttributes(), ["contact_id" => $contact->id, "amount" => "10.00", "mandate" => "S1", "end_to_end_reference" => "E2E-1"]));
        $companyDebit = Debit::create(array_merge($this->baseAttributes(), ["company_id" => $company->id, "amount" => "10.00", "mandate" => "S2", "end_to_end_reference" => "E2E-2"]));

        $this->assertTrue($contactDebit->contact->is($contact));
        $this->assertTrue($companyDebit->company->is($company));
    }

    /**
     * Only "membership"-source rows have one — see this column's migration
     * comment for why the mandate string alone isn't a reliable enough link.
     */
    public function testMembershipIdIsNullableAndLinksBackToTheDuesItCollects(): void
    {
        $contact = $this->contact();
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "10.00",
            "payment_method" => "directdebit",
        ]);

        $withoutMembership = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));
        $withMembership = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S2",
            "end_to_end_reference" => "E2E-2",
            "membership_id" => $membership->id,
            "source" => "membership",
        ]));

        $this->assertNull($withoutMembership->fresh()->membership);
        $this->assertTrue($withMembership->fresh()->membership->is($membership));
    }

    /**
     * netLedgerAmount() is what donation receipts actually sum (decision 4
     * of the payment-ledger design pass plus the refund/chargeback netting
     * rule — see docs/civicrm-replacement.md), and what gates the admin
     * "Erstellen" button.
     */
    public function testNetLedgerAmountFallsBackToTheDebitsOwnAmountWithNoLedgerEntries(): void
    {
        // CiviCrmImporter::importDebits() doesn't backfill the ledger for
        // historical debits — a known, explicitly deferred gap.
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $this->contact()->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));

        $this->assertSame("10.00", $debit->netLedgerAmount());
    }

    public function testNetLedgerAmountIsThePaymentEntrysAmount(): void
    {
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $this->contact()->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));
        LedgerEntry::create(["debit_id" => $debit->id, "kind" => "payment", "amount" => "10.00"]);

        $this->assertSame("10.00", $debit->netLedgerAmount());
    }

    public function testNetLedgerAmountSubtractsAPartialRefund(): void
    {
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $this->contact()->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));
        LedgerEntry::create(["debit_id" => $debit->id, "kind" => "payment", "amount" => "10.00"]);
        LedgerEntry::create(["debit_id" => $debit->id, "kind" => "refund", "amount" => "4.00"]);

        $this->assertSame("6.00", $debit->netLedgerAmount());
    }

    public function testNetLedgerAmountIsZeroWhenARefundFullyOffsetsThePayment(): void
    {
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $this->contact()->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));
        LedgerEntry::create(["debit_id" => $debit->id, "kind" => "payment", "amount" => "10.00"]);
        LedgerEntry::create(["debit_id" => $debit->id, "kind" => "refund", "amount" => "10.00"]);

        $this->assertSame("0.00", $debit->netLedgerAmount());
    }

    /**
     * A chargeback fee always attaches to the *bounced* debit itself, never
     * to the one currently being collected — but even if it somehow did,
     * this must never count toward what's receiptable.
     */
    public function testNetLedgerAmountNeverCountsAChargebackFee(): void
    {
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "contact_id" => $this->contact()->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));
        LedgerEntry::create(["debit_id" => $debit->id, "kind" => "payment", "amount" => "10.00"]);
        LedgerEntry::create(["debit_id" => $debit->id, "kind" => "chargeback_fee", "amount" => "2.50"]);

        $this->assertSame("10.00", $debit->netLedgerAmount());
    }
}
