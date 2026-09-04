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
        // Donation-only payer, keyed by the donor's full name as entered on the
        // direct-debit donation form — never becomes a member, so it stays
        // separate from assoc_contacts rather than folding donor and member
        // identity into one entity.
        Schema::create('assoc_households', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // Set only for rows brought in by assoc:import-civicrm — the CiviCRM
            // contact.id (Household), so re-running the importer upserts instead of
            // duplicating.
            $table->unsignedInteger("civicrm_id")->nullable()->unique();
            $table->string("household_name");
            $table->string("street")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("city")->nullable();
            $table->string("country")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_households');
    }
};
