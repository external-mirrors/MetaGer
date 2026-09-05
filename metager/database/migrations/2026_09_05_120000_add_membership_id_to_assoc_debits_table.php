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
        // assoc_debits has no way back to the Membership a "membership"-source
        // row is collecting dues for — only the shared mandate string, which
        // isn't unique (see assoc_debits' own migration comment). DebitCreator
        // already has the Membership in hand when it creates one of these rows;
        // this lets it record that link instead of everything downstream having
        // to re-derive it. Null for "donation"-source rows, which have no
        // Membership at all.
        Schema::table('assoc_debits', function (Blueprint $table) {
            $table->uuid("membership_id")->nullable()->after("company_id")->references("id")->on("assoc_memberships");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assoc_debits', function (Blueprint $table) {
            $table->dropColumn("membership_id");
        });
    }
};
