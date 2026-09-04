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
        // Hibiscus bank-statement lines, imported for reconciliation against
        // assoc_debits/assoc_recur_contributions. matched_id is not a foreign
        // key: it points into whichever table matched_type names, and no single
        // column can carry a real FK constraint to two different tables.
        Schema::create('assoc_bank_statement_lines', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            $table->string("iban");
            $table->decimal("amount", 10, 2);
            $table->string("reference")->nullable();
            $table->date("booked_at");
            $table->enum("matched_type", ["debit", "recur_contribution"])->nullable();
            $table->uuid("matched_id")->nullable();
            $table->enum("match_method", ["mandate_reference", "regex", "substring", "manual"])->nullable();
            $table->string("matched_by")->nullable();
            $table->dateTime("matched_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_bank_statement_lines');
    }
};
