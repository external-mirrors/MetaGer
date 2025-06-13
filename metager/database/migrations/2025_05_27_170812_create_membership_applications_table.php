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
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            $table->string("locale")->nullable();
            $table->float("amount")->nullable();
            $table->enum("interval", ["annual", "six-monthly", "quarterly", "monthly"])->nullable();
            $table->enum("payment_method", ["directdebit", "banktransfer", "paypal", "card"])->nullable();
            $table->string('payment_reference')->nullable();
            $table->uuid("key")->nullable();
            $table->boolean("is_update")->default(false);
            $table->integer("crm_contact")->nullable()->unique();
            $table->integer("crm_membership")->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
    }
};
