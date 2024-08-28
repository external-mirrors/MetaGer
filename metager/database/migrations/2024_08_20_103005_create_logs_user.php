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
        Schema::create('logs_user', function (Blueprint $table) {
            $table->string("email");
            $table->integer("discount")->default(0);
            $table->dateTime("last_activity")->nullable();
            $table->timestamps();

            $table->primary("email");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_user');
    }
};
