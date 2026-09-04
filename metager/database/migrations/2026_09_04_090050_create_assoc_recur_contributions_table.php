<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Recurring SEPA direct debits. Shared by membership dues and recurring
        // donations, same as CiviCRM's existing RecurContribution entity.
        Schema::create('assoc_recur_contributions', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // Set only for rows brought in by assoc:import-civicrm — the
            // civicrm_recur_contribution.id from de.suma-ev.donation-debit, so
            // re-running the importer upserts instead of duplicating.
            $table->unsignedInteger("civicrm_id")->nullable()->unique();
            // Exactly one of these three is set.
            $table->uuid("contact_id")->nullable()->references("id")->on("assoc_contacts");
            $table->uuid("company_id")->nullable()->references("id")->on("assoc_companies");
            $table->uuid("household_id")->nullable()->references("id")->on("assoc_households");
            $table->enum("source", ["membership", "donation"]);
            $table->string("iban");
            $table->decimal("amount", 10, 2);
            // Unlike assoc_debits.mandate, this one is legitimately unique: a
            // RecurContribution is the recurring arrangement itself, one mandate
            // each, and CRM_DonationDebit_Form_RecurContribution::validateMandate
            // rejects a second RecurContribution reusing a mandate already in use
            // by another. It's the individual assoc_debits rows CreateDebits
            // generates from a RecurContribution that reuse its mandate.
            $table->string("mandate")->nullable()->unique();
            $table->date("mandate_date");
            $table->enum("frequency", ["monthly", "quarterly", "six-monthly", "annual"]);
            $table->boolean("active")->default(true);
            $table->date("next_due_date")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_recur_contributions');
    }
};
