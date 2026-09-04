<?php

namespace Tests\Feature\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\Membership;
use App\Models\Assoc\RecurContribution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * `/admin/assoc/*` is read-only over the tables CiviCrmImporter fills — for
 * verifying the CiviCRM migration. The admin routes carry no auth middleware
 * under APP_ENV=testing (see routes/session.php), so these hit them directly,
 * the same way tests/Feature/Search/StresstestRemovedTest does.
 */
class AssocAdminTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testTheMembersPageListsAContactWithItsMembership(): void
    {
        $contact = Contact::create([
            "first_name" => "Ada",
            "last_name" => "Lovelace",
            "email" => "ada@example.com",
            "city" => "London",
        ]);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => false,
            "interval" => "annual",
            "amount" => "60.00",
            "payment_method" => "banktransfer",
            "standing" => "active",
        ]);

        $response = $this->get("/admin/assoc/members");

        $response->assertOk();
        $response->assertSee("Ada Lovelace");
        $response->assertSee("Überweisung");
    }

    public function testTheMembersPageListsACompanyWithItsMembership(): void
    {
        $company = Company::create([
            "name" => "Analytical Engines Ltd",
            "city" => "London",
        ]);
        Membership::create([
            "company_id" => $company->id,
            "membership_type" => "company",
            "reduced" => false,
            "interval" => "monthly",
            "amount" => "10.00",
            "payment_method" => "directdebit",
            "standing" => "active",
        ]);

        $response = $this->get("/admin/assoc/members");

        $response->assertOk();
        $response->assertSee("Analytical Engines Ltd");
        $response->assertSee("Lastschrift");
    }

    public function testAnExemptMembershipIsLabelledBeitragsbefreit(): void
    {
        $contact = Contact::create([
            "first_name" => "Grace",
            "last_name" => "Hopper",
            "email" => "grace@example.com",
        ]);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => false,
            "interval" => "annual",
            "amount" => "0.00",
            "payment_method" => "exempt",
            "standing" => "active",
        ]);

        $response = $this->get("/admin/assoc/members");

        $response->assertOk();
        $response->assertSee("Beitragsbefreit");
    }

    /**
     * Membership::paymentMethodLabel()/intervalLabel()/standingLabel() are
     * deliberately hardcoded German, not @lang() lookups — this page must
     * not read differently depending on the visitor's negotiated locale
     * (App\Http\Middleware\ResolveLocale runs on every route, admin/*
     * included, and would otherwise resolve payment_methods.banktransfer to
     * "Bank transfer" for an English-negotiating visitor while every label
     * around it stayed German).
     */
    public function testMembershipLabelsStayGermanRegardlessOfNegotiatedLocale(): void
    {
        $contact = Contact::create([
            "first_name" => "Ada",
            "last_name" => "Lovelace",
            "email" => "ada@example.com",
        ]);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => false,
            "interval" => "annual",
            "amount" => "60.00",
            "payment_method" => "banktransfer",
            "standing" => "active",
        ]);

        $response = $this->withHeaders(["Accept-Language" => "en"])->get("/admin/assoc/members");

        $response->assertOk();
        $response->assertSee("Überweisung");
        $response->assertSee("jährlich");
    }

    public function testAContactWithNoMembershipIsListedAsSuch(): void
    {
        Contact::create([
            "first_name" => "No",
            "last_name" => "Membership",
            "email" => "none@example.com",
        ]);

        $response = $this->get("/admin/assoc/members");

        $response->assertOk();
        $response->assertSee("Keine Mitgliedschaft");
    }

    public function testTheMemberDetailPageShowsDebitsAndRecurContributions(): void
    {
        $contact = Contact::create([
            "first_name" => "Ada",
            "last_name" => "Lovelace",
            "email" => "ada@example.com",
            "street" => "Somewhere 1",
            "postal_code" => "12345",
            "city" => "London",
        ]);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => false,
            "interval" => "annual",
            "amount" => "60.00",
            "payment_method" => "banktransfer",
            "standing" => "active",
            "join_date" => "2020-01-01",
        ]);
        Debit::create([
            "contact_id" => $contact->id,
            "source" => "membership",
            "iban" => "DE89370400440532013000",
            "account_holder" => "Ada Lovelace",
            "amount" => "60.00",
            "mandate" => "M1",
            "mandate_date" => "2020-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E1",
            "due_date" => "2026-01-01",
            "reference" => "R1",
        ]);
        RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE89370400440532013000",
            "account_holder" => "Ada Lovelace",
            "amount" => "5.00",
            "frequency" => "monthly",
            "active" => true,
            "mandate_date" => "2020-01-01",
        ]);

        $response = $this->get("/admin/assoc/members/contact/{$contact->id}");

        $response->assertOk();
        $response->assertSee("Ada Lovelace");
        $response->assertSee("60,00");
        $response->assertSee("DE89 3704 0044 0532 0130 00");
        $response->assertSee("Daueraufträge");
    }

    /**
     * The one genuinely new pattern here — no other server-rendered page in
     * this app uses paginate() (see CLAUDE.md-adjacent research: no
     * ->links() call anywhere in resources/views). Follows a real
     * nextPageUrl() rather than asserting on the paginator object directly,
     * so it also exercises URL generation through
     * App\Http\Middleware\ResolveLocale's prefix-stripped request — a
     * mismatch there would send page 2 to a broken or wrongly-prefixed URL.
     */
    public function testTheMembersPagePaginatesContacts(): void
    {
        for ($i = 1; $i <= 51; $i++) {
            Contact::create([
                "first_name" => "Contact",
                "last_name" => sprintf("%02d", $i),
                "email" => "contact{$i}@example.com",
            ]);
        }

        $firstPage = $this->get("/admin/assoc/members");
        $firstPage->assertOk();
        $firstPage->assertDontSee("Contact 51");
        $firstPage->assertSee("Seite 1 von 2");

        $nextPageUrl = null;
        preg_match('/href="([^"]*contacts_page=2[^"]*)"/', $firstPage->getContent(), $matches);
        $nextPageUrl = $matches[1] ?? null;
        $this->assertNotNull($nextPageUrl, "Expected a link to page 2 of the contacts list.");

        $secondPage = $this->get($nextPageUrl);
        $secondPage->assertOk();
        $secondPage->assertSee("Contact 51");
    }

    public function testAnUnknownMemberTypeIs404(): void
    {
        $contact = Contact::create(["first_name" => "A", "last_name" => "B", "email" => "a@b.com"]);

        $this->get("/admin/assoc/members/household/{$contact->id}")->assertNotFound();
    }

    public function testAMissingMemberIs404(): void
    {
        $this->get("/admin/assoc/members/contact/00000000-0000-4000-8000-000000000000")->assertNotFound();
    }

    public function testTheHouseholdsPageListsAHousehold(): void
    {
        Household::create(["household_name" => "The Lovelace Household", "city" => "London"]);

        $response = $this->get("/admin/assoc/households");

        $response->assertOk();
        $response->assertSee("The Lovelace Household");
    }

    public function testTheHouseholdDetailPageShowsItsDonations(): void
    {
        $household = Household::create(["household_name" => "The Lovelace Household"]);
        RecurContribution::create([
            "household_id" => $household->id,
            "source" => "donation",
            "iban" => "DE89370400440532013000",
            "account_holder" => "The Lovelace Household",
            "amount" => "20.00",
            "frequency" => "monthly",
            "active" => true,
            "mandate_date" => "2020-01-01",
        ]);

        $response = $this->get("/admin/assoc/households/{$household->id}");

        $response->assertOk();
        $response->assertSee("The Lovelace Household");
        $response->assertSee("20,00");
    }

    public function testAMissingHouseholdIs404(): void
    {
        $this->get("/admin/assoc/households/00000000-0000-4000-8000-000000000000")->assertNotFound();
    }
}
