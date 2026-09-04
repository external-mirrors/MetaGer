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
            // Exactly one of these three is set.
            $table->uuid("contact_id")->nullable()->references("id")->on("assoc_contacts");
            $table->uuid("company_id")->nullable()->references("id")->on("assoc_companies");
            $table->uuid("household_id")->nullable()->references("id")->on("assoc_households");
            $table->enum("source", ["membership", "donation"]);
            $table->string("iban");
            $table->decimal("amount", 10, 2);
            $table->string("mandate")->unique();
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
