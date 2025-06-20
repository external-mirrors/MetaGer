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
        Schema::create('membership_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            $table->enum("title", ["Herr", "Frau", "Neutral"])->nullable(false);
            $table->string("first_name")->nullable(false);
            $table->string("last_name")->nullable(false);
            $table->string("email")->nullable(false);
            $table->uuid("application_id")->unique()->references("id")->on("membership_applications");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_contacts');
    }
};
