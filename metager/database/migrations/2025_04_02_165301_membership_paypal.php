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
        Schema::create('membership_paypal', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('civicrm_membership_id');
            $table->string('order_id')->nullable();
            $table->string('authorization_id')->nullable();
            $table->string('authorization_status')->nullable();
            $table->timestamp("expires_at")->nullable(false);
            $table->timestamp('created_at')->nullable(false)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_paypal');
    }
};
