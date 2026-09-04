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
        // Per-payer override of App\Assoc\DonationReceiptGenerator's default —
        // config('assoc.donation_receipt_default_preference') — for whether a
        // future donation/dues payment gets receipted the moment it's collected
        // ("immediate") or gathered into one receipt per year ("annual"), or not
        // receipted automatically at all ("never"). Null means "no override, use
        // the default"; it is deliberately not itself one of the three states, so
        // changing the default later moves everyone who never set a preference.
        // Mirrors CiviCRM's Bescheinigungen.Spende_bescheinigen /
        // Mitgliedsbeitrag_bescheinigen contact-level settings, collapsed from two
        // independent fields into one — see CiviCrmImporter::importContacts() for
        // how a contact where the two disagreed is resolved.
        foreach (["assoc_contacts", "assoc_companies"] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->enum("donation_receipt_preference", ["never", "immediate", "annual"])->nullable();
            });
        }

        // Which receipt (if any) this debit's payment has already been included
        // in — the equivalent of CiviCRM's civicrm_contribution.receipt_date, but
        // as a link to the receipt itself rather than just a timestamp, since a
        // receipt here can be regenerated/reprinted without that meaning the
        // underlying debit needs receipting again.
        Schema::table("assoc_debits", function (Blueprint $table) {
            $table->uuid("donation_receipt_id")->nullable()->references("id")->on("assoc_donation_receipts");
        });

        // Donations and membership dues are legally distinct instruments on a
        // German Zuwendungsbestätigung (different mandatory wording), so one
        // receipt never mixes assoc_debits.source values — see
        // Bescheinigungen/Spendenbescheinigung.php's separate "zuwendungen" and
        // "beitragsbescheinigungen" template paths, which this preserves.
        Schema::table("assoc_donation_receipts", function (Blueprint $table) {
            $table->enum("source", ["donation", "membership"])->default("donation");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("assoc_debits", function (Blueprint $table) {
            $table->dropColumn("donation_receipt_id");
        });
        Schema::table("assoc_donation_receipts", function (Blueprint $table) {
            $table->dropColumn("source");
        });
        foreach (["assoc_contacts", "assoc_companies"] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn("donation_receipt_preference");
            });
        }
    }
};
