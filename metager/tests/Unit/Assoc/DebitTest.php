<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
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

    /**
     * SEPA debit amounts are the one place a float would quietly round —
     * this is the system of record for what goes into a pain.008 file.
     */
    public function testTheAmountRoundTripsExactlyAsADecimalString(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "19.99",
            "mandate" => "S1",
        ]));

        $this->assertSame("19.99", Debit::findOrFail($debit->id)->amount);
    }

    public function testTheEndToEndReferenceMustBeUnique(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        Debit::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));

        $this->expectException(QueryException::class);
        Debit::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
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
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        Debit::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "end_to_end_reference" => "E2E-1",
        ]));
        $second = Debit::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "end_to_end_reference" => "E2E-2",
        ]));

        $this->assertSame("S1", $second->fresh()->mandate);
    }

    public function testStatusDefaultsToPending(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        $debit = Debit::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
        ]));

        $this->assertSame("pending", $debit->fresh()->status);
    }

    public function testItBelongsToWhicheverPayerCreatedIt(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $company = Company::create(["name" => "Analytical Engines Ltd"]);
        $household = Household::create(["household_name" => "Familie Lovelace"]);

        $contactDebit = Debit::create(array_merge($this->baseAttributes(), ["contact_id" => $contact->id, "amount" => "10.00", "mandate" => "S1", "end_to_end_reference" => "E2E-1"]));
        $companyDebit = Debit::create(array_merge($this->baseAttributes(), ["company_id" => $company->id, "amount" => "10.00", "mandate" => "S2", "end_to_end_reference" => "E2E-2"]));
        $householdDebit = Debit::create(array_merge($this->baseAttributes(), ["household_id" => $household->id, "amount" => "10.00", "mandate" => "S3", "end_to_end_reference" => "E2E-3"]));

        $this->assertTrue($contactDebit->contact->is($contact));
        $this->assertTrue($companyDebit->company->is($company));
        $this->assertTrue($householdDebit->household->is($household));
    }
}
