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
            $table->string('vault_id')->nullable();
            $table->string('order_id')->nullable(false);
            $table->string('authorization_id')->nullable();
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
