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
        Schema::create('logs_order', function (Blueprint $table) {
            $table->id();
            $table->string("user_email");
            $table->dateTime("from");
            $table->dateTime("to");
            $table->float("price", 2);
            $table->string("invoice_id")->unique()->nullable();
            $table->timestamps();
            $table->foreign("user_email")->references("email")->on("logs_users")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_order');
    }
};
