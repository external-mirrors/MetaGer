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
        Schema::create('logs_access_key', function (Blueprint $table) {
            $table->id();
            $table->string("user_email");
            $table->string("name");
            $table->string("key");
            $table->dateTime("created_at")->useCurrent();
            $table->dateTime("accessed_at")->nullable();

            $table->foreign("user_email")->references("email")->on("logs_users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_access_key');
    }
};
