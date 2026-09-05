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
        // Snapshots Membership::end_date immediately before
        // BankStatementMatcher::confirm() advances it on this debit's
        // payment — the only way to roll end_date back exactly if the
        // payment later bounces (a Rücklastschrift), since confirm()'s
        // advance isn't always "add one interval" (see its docblock's
        // resumption case). Null until confirmed; also null forever for a
        // debit that never gets confirmed at all.
        Schema::table('assoc_debits', function (Blueprint $table) {
            $table->date("previous_end_date")->nullable()->after("due_date");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assoc_debits', function (Blueprint $table) {
            $table->dropColumn("previous_end_date");
        });
    }
};
