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
        Schema::create('membership', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('company')->nullable();
            $table->string('employees')->nullable();
            $table->string("email")->nullable();
            $table->boolean("email_optout")->nullable();
            $table->string("key")->nullable(false);
            $table->double("amount")->nullable();
            $table->enum("interval", ["monthly", "quarterly", "six-monthly", "annual"])->nullable();
            $table->timestamp("reduced_until")->nullable();
            $table->enum("payment_method", ["banktransfer", "directdebit", "paypal", "creditcard"])->nullable();
            $table->unsignedBigInteger("civicrm_membership_id")->nullable()->unique();
            $table->unsignedBigInteger('directdebit')->references("id")->on("membership_directdebit")->nullable()->unique();
            $table->unsignedBigInteger('paypal')->references("id")->on("membership_directdebit")->nullable()->unique();
            $table->string("locale")->nullable(false);
            $table->timestamp("expires_at")->nullable(false);
            $table->timestamp('created_at')->nullable(false)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership');
    }
};
