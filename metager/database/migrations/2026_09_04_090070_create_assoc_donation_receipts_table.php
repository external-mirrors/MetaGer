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
        Schema::create('assoc_donation_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // Exactly one of these three is set.
            $table->uuid("contact_id")->nullable()->references("id")->on("assoc_contacts");
            $table->uuid("company_id")->nullable()->references("id")->on("assoc_companies");
            $table->uuid("household_id")->nullable()->references("id")->on("assoc_households");
            $table->unsignedSmallInteger("year");
            $table->decimal("total_amount", 10, 2);
            $table->dateTime("generated_at")->nullable();
            $table->string("pdf_path")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_donation_receipts');
    }
};
