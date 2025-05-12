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
        Schema::create('membership_reduction', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_path')->nullable(false);
            $table->string('file_mimetype')->nullable(false);
            $table->unsignedBigInteger('membership_id')->references("id")->on("membership");
            $table->timestamp("expires_at")->nullable(false);
            $table->timestamp('created_at')->nullable(false)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_reduction');
    }
};
