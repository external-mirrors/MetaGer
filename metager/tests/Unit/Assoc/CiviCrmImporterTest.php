<?php

namespace Tests\Unit\Assoc;

use App\Assoc\CiviCrmImporter;
use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\RecurContribution;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * The fixture schema below is not this app's own — it's the shape of
 * de.suma-ev.donation-debit's civicrm_debit/civicrm_recur_contribution
 * (verified against its xml/schema/CRM/DonationDebit/*.xml) plus CiviCRM
 * core's civicrm_contact/civicrm_email/civicrm_address (column names verified
 * against App\Models\Membership\CiviCrm's API4 select lists and
 * de.suma-ev.bescheinigungen's address_primary.* usage). Building it here,
 * rather than waiting for a real dump, is what lets the importer's mapping
 * logic get a regression test before it ever touches production data — see
 * CiviCrmImporter::importMemberships for the piece that genuinely does need
 * a dump (custom-value column names aren't discoverable any other way).
 */
class CiviCrmImporterTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        $this->setUpCiviCrmFixture();
    }

    private function setUpCiviCrmFixture(): void
    {
        config(['database.connections.civicrm.driver' => 'sqlite']);
        config(['database.connections.civicrm.database' => ':memory:']);
        config(['database.connections.civicrm.foreign_key_constraints' => false]);
        DB::purge('civicrm');

        Schema::connection('civicrm')->create('civicrm_contact', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contact_type');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('household_name')->nullable();
            $table->boolean('is_deleted')->default(false);
        });
        Schema::connection('civicrm')->create('civicrm_email', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->string('email');
            $table->boolean('is_primary')->default(false);
        });
        Schema::connection('civicrm')->create('civicrm_address', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->string('street_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_primary')->default(false);
        });
        Schema::connection('civicrm')->create('civicrm_debit', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->unsignedInteger('recur_contribution_id')->nullable();
            $table->unsignedInteger('membership_id')->nullable();
            $table->text('account_holder');
            $table->string('iban');
            $table->string('bic')->nullable();
            $table->float('amount');
            $table->string('mandate');
            $table->date('mandate_date');
            $table->string('end_to_end_reference');
            $table->text('reference');
            $table->date('due_date');
            $table->string('status');
        });
        Schema::connection('civicrm')->create('civicrm_recur_contribution', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->text('account_holder')->nullable();
            $table->string('iban');
            $table->string('bic')->nullable();
            $table->float('amount');
            $table->string('mandate')->nullable();
            $table->date('mandate_date');
            $table->string('frequency');
            $table->date('next_debit')->nullable();
        });
    }

    private function civicrm(string $table): \Illuminate\Database\Query\Builder
    {
        return DB::connection('civicrm')->table($table);
    }

    public function testImportsAnIndividualContactWithAddressAndEmail(): void
    {
        $id = $this->civicrm('civicrm_contact')->insertGetId([
            'contact_type' => 'Individual',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $this->civicrm('civicrm_email')->insert(['contact_id' => $id, 'email' => 'ada@example.com', 'is_primary' => true]);
        $this->civicrm('civicrm_address')->insert(['contact_id' => $id, 'street_address' => 'Main St 1', 'postal_code' => '12345', 'city' => 'Berlin', 'is_primary' => true]);

        (new CiviCrmImporter())->import();

        $contact = Contact::where('civicrm_id', $id)->firstOrFail();
        $this->assertSame('Ada', $contact->first_name);
        $this->assertSame('Lovelace', $contact->last_name);
        $this->assertSame('ada@example.com', $contact->email);
        $this->assertSame('Main St 1', $contact->street);
        $this->assertSame('12345', $contact->postal_code);
        $this->assertSame('Berlin', $contact->city);
    }

    public function testImportsAnOrganizationAndAHousehold(): void
    {
        $orgId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Organization', 'organization_name' => 'Analytical Engines Ltd']);
        $householdId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Household', 'household_name' => 'Familie Lovelace']);

        (new CiviCrmImporter())->import();

        $this->assertSame('Analytical Engines Ltd', Company::where('civicrm_id', $orgId)->firstOrFail()->name);
        $this->assertSame('Familie Lovelace', Household::where('civicrm_id', $householdId)->firstOrFail()->household_name);
    }

    public function testDeletedContactsAreSkipped(): void
    {
        $this->civicrm('civicrm_contact')->insert(['contact_type' => 'Individual', 'first_name' => 'Ghost', 'last_name' => 'Contact', 'is_deleted' => true]);

        (new CiviCrmImporter())->import();

        $this->assertSame(0, Contact::count());
    }

    public function testADebitWithNoMembershipIdIsImportedAsDonation(): void
    {
        $householdId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Household', 'household_name' => 'Familie Lovelace']);
        $debitId = $this->civicrm('civicrm_debit')->insertGetId([
            'contact_id' => $householdId,
            'account_holder' => 'Familie Lovelace',
            'iban' => 'DE02120300000000202051',
            'bic' => 'GENODEF1S01',
            'amount' => 25.0,
            'mandate' => 'S20260101120000',
            'mandate_date' => '2026-01-01',
            'end_to_end_reference' => 'E2E-1',
            'reference' => 'Spende',
            'due_date' => '2026-02-01',
            'status' => 'pending',
        ]);

        (new CiviCrmImporter())->import();

        $debit = Debit::where('civicrm_id', $debitId)->firstOrFail();
        $this->assertSame('donation', $debit->source);
        $this->assertTrue($debit->household->is(Household::where('civicrm_id', $householdId)->firstOrFail()));
        $this->assertSame('GENODEF1S01', $debit->bic);
        $this->assertSame('25.00', $debit->amount);
    }

    public function testADebitWithAMembershipIdIsImportedAsMembership(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        $debitId = $this->civicrm('civicrm_debit')->insertGetId([
            'contact_id' => $contactId,
            'membership_id' => 99,
            'account_holder' => 'Ada Lovelace',
            'iban' => 'DE02120300000000202051',
            'amount' => 17.0,
            'mandate' => 'M20260101120000',
            'mandate_date' => '2026-01-01',
            'end_to_end_reference' => 'E2E-2',
            'reference' => 'Mitgliedsbeitrag',
            'due_date' => '2026-02-01',
            'status' => 'pending',
        ]);

        (new CiviCrmImporter())->import();

        $this->assertSame('membership', Debit::where('civicrm_id', $debitId)->firstOrFail()->source);
    }

    public function testADebitForAnUnimportedContactIsSkipped(): void
    {
        $this->civicrm('civicrm_debit')->insert([
            'contact_id' => 12345,
            'account_holder' => 'Nobody',
            'iban' => 'DE02120300000000202051',
            'amount' => 10.0,
            'mandate' => 'S1',
            'mandate_date' => '2026-01-01',
            'end_to_end_reference' => 'E2E-3',
            'reference' => 'x',
            'due_date' => '2026-02-01',
            'status' => 'pending',
        ]);

        (new CiviCrmImporter())->import();

        $this->assertSame(0, Debit::count());
    }

    public function testARecurContributionIsAlwaysImportedAsDonation(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        $recurId = $this->civicrm('civicrm_recur_contribution')->insertGetId([
            'contact_id' => $contactId,
            'account_holder' => 'Ada Lovelace',
            'iban' => 'DE02120300000000202051',
            'amount' => 5.0,
            'mandate' => 'S20260101120000',
            'mandate_date' => '2026-01-01',
            'frequency' => 'monthly',
            'next_debit' => '2026-02-01',
        ]);

        (new CiviCrmImporter())->import();

        $recur = RecurContribution::where('civicrm_id', $recurId)->firstOrFail();
        $this->assertSame('donation', $recur->source);
        $this->assertTrue($recur->contact->is(Contact::where('civicrm_id', $contactId)->firstOrFail()));
        $this->assertSame('2026-02-01', $recur->next_due_date->toDateString());
    }

    public function testReRunningTheImportUpdatesRatherThanDuplicates(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);

        (new CiviCrmImporter())->import();
        $this->civicrm('civicrm_contact')->where('id', $contactId)->update(['last_name' => 'Byron']);
        (new CiviCrmImporter())->import();

        $this->assertSame(1, Contact::count());
        $this->assertSame('Byron', Contact::where('civicrm_id', $contactId)->firstOrFail()->last_name);
    }
}
