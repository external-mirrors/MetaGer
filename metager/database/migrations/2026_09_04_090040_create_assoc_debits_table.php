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
        // One-off SEPA direct debits. Shared by membership dues and one-time
        // donations, same as CiviCRM's existing Debit entity — see
        // Civi\Api4\Action\Debit\MetaGerDonation for the donation side.
        Schema::create('assoc_debits', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // Set only for rows brought in by assoc:import-civicrm — the
            // civicrm_debit.id from de.suma-ev.donation-debit, so re-running the
            // importer upserts instead of duplicating.
            $table->unsignedInteger("civicrm_id")->nullable()->unique();
            // Exactly one of these two is set.
            $table->uuid("contact_id")->nullable()->references("id")->on("assoc_contacts");
            $table->uuid("company_id")->nullable()->references("id")->on("assoc_companies");
            $table->enum("source", ["membership", "donation"]);
            $table->string("iban");
            // Nullable: SEPA generation only needs a BIC for non-SEPA-area IBANs,
            // and civicrm_debit.bic is itself not required — preserved from the
            // source rather than derived, so it stays whatever was captured then.
            $table->string("bic")->nullable();
            $table->string("account_holder");
            $table->decimal("amount", 10, 2);
            // Not unique: a SEPA mandate is reused for every collection made
            // under it (renewals, recurring instalments), so the same value
            // legitimately repeats across rows here.
            $table->string("mandate")->index();
            $table->date("mandate_date");
            $table->enum("status", ["pending", "executed", "failed"])->default("pending");
            // The per-transaction identifier — this is what's actually
            // unique per collection, not the mandate.
            $table->string("end_to_end_reference")->unique();
            $table->date("due_date");
            $table->string("reference")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_debits');
    }
};
