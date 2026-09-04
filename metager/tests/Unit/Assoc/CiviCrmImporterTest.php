<?php

namespace Tests\Unit\Assoc;

use App\Assoc\CiviCrmImporter;
use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\Membership;
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
 * (verified against its xml/schema/CRM/DonationDebit/*.xml), CiviCRM core's
 * civicrm_contact/civicrm_email/civicrm_address/civicrm_membership/
 * civicrm_membership_type, and the Beitrag/Mastodon/MetaGer_Key custom-value
 * tables (civicrm_value_beitrag_8/civicrm_value_mastodon_10/
 * civicrm_value_metager_key_14) — all verified column-for-column against a
 * real production dump (civicrm_custom_group/civicrm_custom_field/
 * civicrm_option_value), not guessed.
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
        Schema::connection('civicrm')->create('civicrm_membership_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('duration_unit');
            $table->unsignedInteger('duration_interval')->nullable();
        });
        Schema::connection('civicrm')->create('civicrm_membership', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->unsignedInteger('membership_type_id');
            $table->date('join_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
        Schema::connection('civicrm')->create('civicrm_value_beitrag_8', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->decimal('monatlicher_mitgliedsbeitrag_29', 20, 2)->nullable();
            $table->string('zahlungsweise_32')->nullable();
            $table->string('kontoinhaber_33')->nullable();
            $table->string('iban_34')->nullable();
            $table->string('bic_35')->nullable();
            $table->string('zahlungsreferenz_36')->nullable();
            $table->string('zahlungsstatus_37')->nullable();
            $table->dateTime('erm_igt_bis_49')->nullable();
            $table->string('paypal_vault_50')->nullable();
            $table->string('paypal_id_51')->nullable();
            $table->string('locale_52')->nullable();
        });
        Schema::connection('civicrm')->create('civicrm_value_mastodon_10', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->string('mastodon_id_42')->nullable();
        });
        Schema::connection('civicrm')->create('civicrm_value_metager_key_14', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->string('key_46', 36)->nullable();
            $table->dateTime('next_charge_47')->nullable();
        });
    }

    /**
     * @return array{type_id: int, membership_id: int}
     */
    private function insertMembership(int $contactId, array $attributes = []): array
    {
        $typeId = $this->civicrm('civicrm_membership_type')->insertGetId(array_merge([
            'name' => 'Personenmitgliedschaft (Jahr)',
            'duration_unit' => 'year',
            'duration_interval' => 1,
        ], $attributes['type'] ?? []));

        $membershipId = $this->civicrm('civicrm_membership')->insertGetId([
            'contact_id' => $contactId,
            'membership_type_id' => $typeId,
            'join_date' => $attributes['join_date'] ?? '2020-01-01',
            'start_date' => $attributes['start_date'] ?? '2026-01-01',
            'end_date' => $attributes['end_date'] ?? '2027-01-01',
        ]);

        $this->civicrm('civicrm_value_beitrag_8')->insert(array_merge([
            'entity_id' => $membershipId,
            'monatlicher_mitgliedsbeitrag_29' => 17.00,
            'zahlungsweise_32' => '2',
            'zahlungsreferenz_36' => 'M20260101120000',
            'zahlungsstatus_37' => '1',
            'locale_52' => 'de-DE',
        ], $attributes['beitrag'] ?? []));

        return ['type_id' => $typeId, 'membership_id' => $membershipId];
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

    public function testABanktransferMembershipIsImportedWithItsInterval(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId, [
            'type' => ['name' => 'Personenmitgliedschaft (Monat)', 'duration_unit' => 'month', 'duration_interval' => 1],
            'beitrag' => ['zahlungsweise_32' => '2', 'monatlicher_mitgliedsbeitrag_29' => 4.50],
        ]);

        (new CiviCrmImporter())->import();

        $membership = Membership::where('civicrm_id', $membershipId)->firstOrFail();
        $this->assertTrue($membership->contact->is(Contact::where('civicrm_id', $contactId)->firstOrFail()));
        $this->assertSame('person', $membership->membership_type);
        $this->assertSame('banktransfer', $membership->payment_method);
        $this->assertSame('monthly', $membership->interval);
        $this->assertSame('4.50', $membership->amount);
        $this->assertSame('active', $membership->standing);
    }

    public function testADirectdebitMembershipForACompanyIsImported(): void
    {
        $companyId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Organization', 'organization_name' => 'Analytical Engines Ltd']);
        ['membership_id' => $membershipId] = $this->insertMembership($companyId, [
            'type' => ['name' => 'Firmenmitgliedschaft (Quartal)', 'duration_unit' => 'month', 'duration_interval' => 3],
            'beitrag' => ['zahlungsweise_32' => '1'],
        ]);

        (new CiviCrmImporter())->import();

        $membership = Membership::where('civicrm_id', $membershipId)->firstOrFail();
        $this->assertTrue($membership->company->is(Company::where('civicrm_id', $companyId)->firstOrFail()));
        $this->assertSame('company', $membership->membership_type);
        $this->assertSame('directdebit', $membership->payment_method);
        $this->assertSame('quarterly', $membership->interval);
    }

    public function testAPaypalMembershipIsImported(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId, [
            'type' => ['name' => 'Personenmitgliedschaft (Halbjahr)', 'duration_unit' => 'month', 'duration_interval' => 6],
            'beitrag' => ['zahlungsweise_32' => 'paypal', 'paypal_vault_50' => 'vault-123'],
        ]);

        (new CiviCrmImporter())->import();

        $membership = Membership::where('civicrm_id', $membershipId)->firstOrFail();
        $this->assertSame('paypal', $membership->payment_method);
        $this->assertSame('six-monthly', $membership->interval);
        $this->assertSame('vault-123', $membership->paypal_vault_id);
    }

    public function testAReducedMembershipIsFlaggedReduced(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId, [
            'type' => ['name' => 'Personenmitgliedschaft ermäßigt (Jahr)', 'duration_unit' => 'year', 'duration_interval' => 1],
            'beitrag' => ['erm_igt_bis_49' => '2027-06-01 00:00:00'],
        ]);

        (new CiviCrmImporter())->import();

        $membership = Membership::where('civicrm_id', $membershipId)->firstOrFail();
        $this->assertTrue($membership->reduced);
        $this->assertSame('2027-06-01', $membership->reduced_until->toDateString());
    }

    /**
     * Ehrenmitglied/Gegenseitigkeit (production dump: membership_type_id 21/22,
     * duration_unit=lifetime) get payment_method=exempt regardless of whatever
     * Zahlungsweise happens to be recorded — in the dump it's blank/NULL for
     * all 9 such rows anyway, since no dues are ever collected from them.
     */
    public function testALifetimeMembershipTypeIsImportedAsExempt(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId, [
            'type' => ['name' => 'Ehrenmitglied', 'duration_unit' => 'lifetime', 'duration_interval' => 1],
            'beitrag' => ['zahlungsweise_32' => null, 'monatlicher_mitgliedsbeitrag_29' => 0.00],
        ]);

        (new CiviCrmImporter())->import();

        $membership = Membership::where('civicrm_id', $membershipId)->firstOrFail();
        $this->assertSame('exempt', $membership->payment_method);
        $this->assertSame('0.00', $membership->amount);
    }

    public function testAnAusgetretenZahlungsstatusBecomesTerminatedStanding(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId, ['beitrag' => ['zahlungsstatus_37' => '6']]);

        (new CiviCrmImporter())->import();

        $this->assertSame('terminated', Membership::where('civicrm_id', $membershipId)->firstOrFail()->standing);
    }

    public function testAVerstorbenZahlungsstatusBecomesDeceasedStanding(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId, ['beitrag' => ['zahlungsstatus_37' => '7']]);

        (new CiviCrmImporter())->import();

        $this->assertSame('deceased', Membership::where('civicrm_id', $membershipId)->firstOrFail()->standing);
    }

    /**
     * Warte_auf_Lastschrifteingang covers 575/2311 rows in the production dump —
     * a billing-progress state, not membership standing, so it collapses to
     * "active" like every other non-Ausgetreten/Verstorben value.
     */
    public function testAWarteAufLastschrifteingangZahlungsstatusStaysActive(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId, ['beitrag' => ['zahlungsstatus_37' => '2']]);

        (new CiviCrmImporter())->import();

        $this->assertSame('active', Membership::where('civicrm_id', $membershipId)->firstOrFail()->standing);
    }

    public function testMastodonIdAndKeyIdAreJoinedInFromTheirOwnCustomValueTables(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId);
        $this->civicrm('civicrm_value_mastodon_10')->insert(['entity_id' => $membershipId, 'mastodon_id_42' => '12345']);
        $this->civicrm('civicrm_value_metager_key_14')->insert(['entity_id' => $membershipId, 'key_46' => '11111111-1111-1111-1111-111111111111']);

        (new CiviCrmImporter())->import();

        $membership = Membership::where('civicrm_id', $membershipId)->firstOrFail();
        $this->assertSame('12345', $membership->mastodon_id);
        $this->assertSame('11111111-1111-1111-1111-111111111111', $membership->key_id);
    }

    /**
     * 38 rows in the production dump: memberships that lapsed before
     * Zahlungsweise was ever populated, with no reliable way to infer what
     * their payment method used to be.
     */
    public function testAMembershipWithNoZahlungsweiseAndNotLifetimeIsSkipped(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        $this->insertMembership($contactId, ['beitrag' => ['zahlungsweise_32' => null]]);

        (new CiviCrmImporter())->import();

        $this->assertSame(0, Membership::count());
    }

    public function testAHouseholdMembershipIsSkipped(): void
    {
        $householdId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Household', 'household_name' => 'Familie Lovelace']);
        $this->insertMembership($householdId);

        (new CiviCrmImporter())->import();

        $this->assertSame(0, Membership::count());
    }

    public function testReRunningTheImportUpdatesMembershipsRatherThanDuplicating(): void
    {
        $contactId = $this->civicrm('civicrm_contact')->insertGetId(['contact_type' => 'Individual', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);
        ['membership_id' => $membershipId] = $this->insertMembership($contactId);

        (new CiviCrmImporter())->import();
        $this->civicrm('civicrm_value_beitrag_8')->where('entity_id', $membershipId)->update(['zahlungsstatus_37' => '6']);
        (new CiviCrmImporter())->import();

        $this->assertSame(1, Membership::count());
        $this->assertSame('terminated', Membership::where('civicrm_id', $membershipId)->firstOrFail()->standing);
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
