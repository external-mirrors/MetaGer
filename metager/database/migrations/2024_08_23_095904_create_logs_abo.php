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
        // timestamps, interval, user_email, monthly_price

        Schema::create('logs_abo', function (Blueprint $table) {
            $table->id("user_id");
            $table->enum("interval", ["monthly", "quarterly", "six-monthly", "annual"]);
            $table->float("monthly_price", 2)->unsigned();
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("logs_users")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_abo');
    }
};
