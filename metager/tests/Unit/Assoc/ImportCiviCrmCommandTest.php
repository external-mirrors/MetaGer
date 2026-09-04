<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class ImportCiviCrmCommandTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();

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

        DB::connection('civicrm')->table('civicrm_contact')->insert([
            'contact_type' => 'Individual',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
    }

    public function testANormalRunPersistsTheImportedRows(): void
    {
        Artisan::call('assoc:import-civicrm');

        $this->assertSame(1, Contact::count());
    }

    public function testADryRunLeavesNoRowsBehind(): void
    {
        Artisan::call('assoc:import-civicrm', ['--dry-run' => true]);

        $this->assertSame(0, Contact::count());
    }

    public function testADryRunStillReportsWhatItWouldHaveImported(): void
    {
        Artisan::call('assoc:import-civicrm', ['--dry-run' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('contacts: 1', $output);
        $this->assertStringContainsString('Dry run', $output);
    }
}
