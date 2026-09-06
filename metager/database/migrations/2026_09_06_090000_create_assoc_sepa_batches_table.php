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
        // One row per generated pain.008.001.02 SEPA collection batch — see
        // SepaDirectDebitBatchGenerator. Every debit it covers gets its
        // status flipped to "submitted" and its sepa_batch_id set (see the
        // following migration), same audit-trail shape assoc_donation_receipts
        // already uses for the debits a receipt covers.
        Schema::create('assoc_sepa_batches', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // The pain.008 GrpHdr/MsgId — unique per batch by construction
            // (timestamp + random suffix), kept here too so a batch can be
            // matched back to its own XML by eye if needed.
            $table->string("message_id")->unique();
            $table->dateTime("generated_at");
            $table->unsignedInteger("debit_count");
            $table->decimal("total_amount", 10, 2);
            $table->string("xml_path");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_sepa_batches');
    }
};
