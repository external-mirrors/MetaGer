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

class LedgerEntryTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    private function membership(): Membership
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        return Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "60.00",
            "payment_method" => "directdebit",
        ]);
    }

    public function testALedgerEntryBelongsToAMembership(): void
    {
        $membership = $this->membership();
        $entry = LedgerEntry::create(["membership_id" => $membership->id, "kind" => "charge", "amount" => "60.00"]);

        $this->assertTrue($entry->membership->is($membership));
        $this->assertTrue($membership->ledgerEntries->first()->is($entry));
    }

    public function testALedgerEntryCanBeTiedToTheDebitItCameFrom(): void
    {
        $membership = $this->membership();
        $debit = Debit::create([
            "contact_id" => $membership->contact_id,
            "source" => "membership",
            "iban" => "DE89370400440532013000",
            "account_holder" => "Ada Lovelace",
            "amount" => "60.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E1",
            "due_date" => "2026-01-01",
        ]);

        $entry = LedgerEntry::create([
            "membership_id" => $membership->id,
            "debit_id" => $debit->id,
            "kind" => "payment",
            "amount" => "60.00",
            "channel" => "directdebit",
        ]);

        $this->assertTrue($entry->debit->is($debit));
        $this->assertNull($entry->bankStatementLine);
    }

    public function testAnUnknownKindIsRejectedByTheDatabase(): void
    {
        $membership = $this->membership();

        $this->expectException(QueryException::class);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "not_a_real_kind", "amount" => "1.00"]);
    }

    public function testAnUnknownChannelIsRejectedByTheDatabase(): void
    {
        $membership = $this->membership();

        $this->expectException(QueryException::class);
        LedgerEntry::create([
            "membership_id" => $membership->id,
            "kind" => "payment",
            "amount" => "1.00",
            "channel" => "not_a_real_channel",
        ]);
    }

    /**
     * Every kind used by Membership::ledgerBalance() must have a sign, or a
     * new kind would silently derive a wrong balance instead of failing.
     */
    public function testEveryKindHasABalanceSign(): void
    {
        foreach (["charge", "payment", "chargeback_fee", "waiver", "refund"] as $kind) {
            $this->assertArrayHasKey($kind, LedgerEntry::BALANCE_SIGN);
        }
    }
}
