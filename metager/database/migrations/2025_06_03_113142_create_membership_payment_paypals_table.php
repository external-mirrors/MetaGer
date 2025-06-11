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
        Schema::create('membership_payment_paypals', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            $table->string('order_id')->nullable();
            $table->string('authorization_id')->nullable();
            $table->string('authorization_status')->nullable();
            $table->string('vault_id')->nullable();
            $table->uuid("application_id")->unique()->references("id")->on("membership_applications");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_payment_paypals');
    }
};
