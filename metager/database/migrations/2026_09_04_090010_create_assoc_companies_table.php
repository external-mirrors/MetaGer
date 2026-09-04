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
        Schema::create('assoc_companies', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            $table->string("name");
            $table->uuid("contact_person_id")->nullable()->references("id")->on("assoc_contacts");
            $table->string("street")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("city")->nullable();
            $table->string("country")->nullable();
            $table->string("tax_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_companies');
    }
};
