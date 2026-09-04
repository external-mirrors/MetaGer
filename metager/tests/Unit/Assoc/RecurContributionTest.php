<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\RecurContribution;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class RecurContributionTest extends TestCase
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
            "mandate_date" => "2026-01-01",
        ];
    }

    public function testActiveDefaultsToTrue(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $recur = RecurContribution::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
        ]));

        $this->assertTrue($recur->fresh()->active);
    }

    public function testBicAndAccountHolderAreNullableAndPreservedWhenPresent(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $recur = RecurContribution::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
            "bic" => "GENODEF1S01",
            "account_holder" => "Familie Lovelace",
        ]));

        $reloaded = $recur->fresh();
        $this->assertSame("GENODEF1S01", $reloaded->bic);
        $this->assertSame("Familie Lovelace", $reloaded->account_holder);
    }

    public function testAMandateMustBeUnique(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
        ]));

        $this->expectException(QueryException::class);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
        ]));
    }

    public function testCivicrmIdMustBeUniqueWhenPresent(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
            "civicrm_id" => 42,
        ]));

        $this->expectException(QueryException::class);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S2",
            "frequency" => "monthly",
            "civicrm_id" => 42,
        ]));
    }

    public function testAnUnknownFrequencyIsRejectedByTheDatabase(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        $this->expectException(QueryException::class);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "contact_id" => $contact->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "weekly",
        ]));
    }
}
