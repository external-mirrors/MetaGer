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
            // Exactly one of these three is set.
            $table->uuid("contact_id")->nullable()->references("id")->on("assoc_contacts");
            $table->uuid("company_id")->nullable()->references("id")->on("assoc_companies");
            $table->uuid("household_id")->nullable()->references("id")->on("assoc_households");
            $table->enum("source", ["membership", "donation"]);
            $table->string("iban");
            $table->string("account_holder");
            $table->decimal("amount", 10, 2);
            $table->string("mandate")->unique();
            $table->date("mandate_date");
            $table->enum("status", ["pending", "executed", "failed"])->default("pending");
            $table->string("end_to_end_reference");
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
