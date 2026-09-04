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
            // Nullable together: set for a normal person (Ada/Lovelace). Left
            // null, with display_name set instead, for a payer whose name only
            // ever arrived as one unparsed string — CiviCRM's "Household"
            // contact type, which never meant an actual multi-person household
            // here; it was a way to avoid guessing how to split a name CiviCRM
            // required split. See display_name below.
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            // Set only when first_name/last_name aren't — the whole name as one
            // string, used in place of "$first_name $last_name" wherever a
            // display name is needed. Contact::name() is the single place that
            // decides which to use.
            $table->string("display_name")->nullable();
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
