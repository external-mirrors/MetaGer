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
        Schema::create('assoc_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // Set only for rows brought in by assoc:import-civicrm — the CiviCRM
            // contact.id, so re-running the importer upserts instead of duplicating.
            $table->unsignedInteger("civicrm_id")->nullable()->unique();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("email");
            $table->string("street")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("city")->nullable();
            $table->string("country")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_contacts');
    }
};
