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
        Schema::create('assoc_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // Exactly one of contact_id/company_id is set, same convention as
            // membership_applications.crm_contact vs. a company relation today —
            // enforced at the application layer, not a DB constraint.
            $table->uuid("contact_id")->nullable()->references("id")->on("assoc_contacts");
            $table->uuid("company_id")->nullable()->references("id")->on("assoc_companies");
            $table->enum("membership_type", ["person", "company"]);
            $table->boolean("reduced")->default(false);
            $table->enum("interval", ["monthly", "quarterly", "six-monthly", "annual"]);
            // Canonical values for CiviCRM's "Beitrag.Zahlungsstatus" custom field,
            // as actually read/written by App\Models\Membership\CiviCrm and
            // MembershipPaymentReminder today: Eingetreten, Okay,
            // Erste Zahlungserinnerung, Zweite Zahlungserinnerung, Unterbrochen,
            // Ausgetreten, Verstorben. Stored here as the canonical identifier,
            // not the German label, so the import path needs to map one to the
            // other rather than copy the label verbatim.
            $table->enum("status", [
                "eingetreten",
                "okay",
                "erste_zahlungserinnerung",
                "zweite_zahlungserinnerung",
                "unterbrochen",
                "ausgetreten",
                "verstorben",
            ]);
            $table->date("start_date")->nullable();
            $table->date("end_date")->nullable();
            $table->date("renewed_at")->nullable();
            // The MetaGer search key tied to this membership (see ChargeKeys in the
            // donation-debit extension). A plain identifier, not a foreign key —
            // keys are owned by the separate keymanager service.
            $table->uuid("key_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_memberships');
    }
};
