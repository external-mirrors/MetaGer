<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Membership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testACompanyCanBeCreatedWithoutAContactPerson(): void
    {
        $company = Company::create(["name" => "Analytical Engines Ltd"]);

        $reloaded = Company::findOrFail($company->id);
        $this->assertSame("Analytical Engines Ltd", $reloaded->name);
        $this->assertNull($reloaded->contact_person_id);
    }

    public function testACompanyHasAMembershipRelation(): void
    {
        $company = Company::create(["name" => "Analytical Engines Ltd"]);
        $membership = Membership::create([
            "company_id" => $company->id,
            "membership_type" => "company",
            "interval" => "annual",
            "amount" => "16.00",
            "payment_method" => "banktransfer",
            "status" => "okay",
        ]);

        $this->assertTrue($company->membership()->first()->is($membership));
    }

    public function testCivicrmIdMustBeUniqueWhenPresent(): void
    {
        Company::create(["civicrm_id" => 42, "name" => "Analytical Engines Ltd"]);

        $this->expectException(QueryException::class);
        Company::create(["civicrm_id" => 42, "name" => "Babbage Foundries"]);
    }

    public function testDeletingTheContactPersonDoesNotDeleteTheCompany(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $company = Company::create(["name" => "Analytical Engines Ltd", "contact_person_id" => $contact->id]);

        $contact->delete();

        $this->assertNotNull(Company::find($company->id));
    }
}
