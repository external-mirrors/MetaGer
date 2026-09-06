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
        Schema::table('assoc_debits', function (Blueprint $table) {
            // Which SEPA batch this debit was submitted in, if any — a
            // decorative FK, same convention as membership_id (see
            // 2026_09_05_120000_add_membership_id_to_assoc_debits_table.php):
            // admin traceability ("which file did this go out in"), not
            // something any query branches on.
            $table->uuid("sepa_batch_id")->nullable()->references("id")->on("assoc_sepa_batches");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assoc_debits', function (Blueprint $table) {
            $table->dropColumn('sepa_batch_id');
        });
    }
};
