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
        Schema::create('logs_nda', function (Blueprint $table) {
            $table->string("user_email");
            $table->binary("nda");
            $table->timestamps();

            $table->primary("user_email");
            $table->foreign("user_email")->references("email")->on("logs_user");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_nda');
    }
};
