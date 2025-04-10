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
        Schema::create('membership_directdebit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('iban');
            $table->string('bic')->nullable();
            $table->string('name')->nullable();
            $table->timestamp("expires_at")->nullable(false);
            $table->timestamp('created_at')->nullable(false)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_directdebit');
    }
};
