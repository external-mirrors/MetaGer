<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The operator blocklist, which used to be three text files mounted from the
 * `secrets` Secret (see App\Search\Blacklists and docs/blocklist-api.md).
 *
 * `kind` and `reason` are plain strings rather than enum columns: an enum is a
 * CHECK constraint on SQLite and a type of its own on Postgres, and adding a
 * value later would be a different migration on each. App\Models\BlocklistEntry
 * holds the allowed values and the API validates against them.
 *
 * `terms` is the normalised term set joined by single spaces. Normalised terms
 * never contain whitespace, so that is lossless, and it makes "the same entry
 * twice" an exact string comparison.
 *
 * Soft deletes, so the list keeps its history: a removed entry still says who
 * removed it and why it existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create("blocklist_entries", function (Blueprint $table) {
            $table->id();
            $table->string("kind", 16);
            $table->string("value", 2048);
            $table->string("terms", 1024)->nullable();
            $table->string("reason", 32);
            $table->text("note")->nullable();
            $table->string("external_ref", 191)->nullable()->index();
            $table->string("created_by", 191)->nullable();
            $table->string("deleted_by", 191)->nullable();
            $table->date("review_at")->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(["kind", "deleted_at"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("blocklist_entries");
    }
};
