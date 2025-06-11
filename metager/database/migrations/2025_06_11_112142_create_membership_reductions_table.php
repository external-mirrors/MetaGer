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
        Schema::create('membership_reductions', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            $table->string('file_path')->nullable();
            $table->string('file_mimetype')->nullable();
            $table->date('expires_at')->nullable();
            $table->uuid("application_id")->unique()->references("id")->on("membership_applications");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_reductions');
    }
};
