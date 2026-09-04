<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\Membership;
use App\Models\Assoc\RecurContribution;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testAContactCanBeCreatedAndReloaded(): void
    {
        $contact = Contact::create([
            "first_name" => "Ada",
            "last_name" => "Lovelace",
            "email" => "ada@example.com",
        ]);

        $reloaded = Contact::findOrFail($contact->id);
        $this->assertSame("Ada", $reloaded->first_name);
        $this->assertSame("Lovelace", $reloaded->last_name);
        $this->assertSame("ada@example.com", $reloaded->email);
        $this->assertNull($reloaded->street);
    }

    public function testAContactHasAMembershipRelation(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $this->assertNull($contact->membership);

        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);

        $this->assertTrue($contact->membership()->first()->is($membership));
    }

    /**
     * assoc:import-civicrm re-runs need this to upsert rather than duplicate.
     */
    public function testCivicrmIdMustBeUniqueWhenPresent(): void
    {
        Contact::create(["civicrm_id" => 42, "first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        $this->expectException(QueryException::class);
        Contact::create(["civicrm_id" => 42, "first_name" => "Grace", "last_name" => "Hopper", "email" => "grace@example.com"]);
    }

    public function testAContactCanBeACompanysContactPerson(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $company = Company::create(["name" => "Analytical Engines Ltd", "contact_person_id" => $contact->id]);

        $this->assertTrue($company->contactPerson->is($contact));
    }

    public function testAContactsDebitsAndRecurContributionsAreScoped(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $otherContact = Contact::create(["first_name" => "Grace", "last_name" => "Hopper", "email" => "grace@example.com"]);

        Debit::create([
            "contact_id" => $contact->id,
            "source" => "membership",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Ada Lovelace",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2026-01-01",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-02-01",
        ]);
        Debit::create([
            "contact_id" => $otherContact->id,
            "source" => "membership",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Grace Hopper",
            "amount" => "10.00",
            "mandate" => "S2",
            "mandate_date" => "2026-01-01",
            "end_to_end_reference" => "E2E-2",
            "due_date" => "2026-02-01",
        ]);
        RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "amount" => "5.00",
            "mandate" => "S3",
            "mandate_date" => "2026-01-01",
            "frequency" => "monthly",
        ]);

        $this->assertCount(1, $contact->debits);
        $this->assertCount(1, $contact->recurContributions);
        $this->assertSame("S1", $contact->debits->first()->mandate);
    }
}
