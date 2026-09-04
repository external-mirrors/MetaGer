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
            // Set only for rows brought in by assoc:import-civicrm — the CiviCRM
            // membership.id, so re-running the importer upserts instead of
            // duplicating.
            $table->unsignedInteger("civicrm_id")->nullable()->unique();
            // Exactly one of contact_id/company_id is set, same convention as
            // membership_applications.crm_contact vs. a company relation today —
            // enforced at the application layer, not a DB constraint.
            $table->uuid("contact_id")->nullable()->references("id")->on("assoc_contacts");
            $table->uuid("company_id")->nullable()->references("id")->on("assoc_companies");
            $table->enum("membership_type", ["person", "company"]);
            $table->boolean("reduced")->default(false);
            $table->enum("interval", ["monthly", "quarterly", "six-monthly", "annual"]);
            $table->decimal("amount", 10, 2);
            // How dues get collected — a member can be on banktransfer/paypal/card
            // with no assoc_debits/assoc_recur_contributions row at all, so this
            // can't be derived from those tables; it's what
            // App\Models\Membership\CiviCrm's "Beitrag.Zahlungsweise" tracks today.
            $table->enum("payment_method", ["banktransfer", "directdebit", "paypal", "card"]);
            // The mandate/reference tying this membership to its debit row(s) —
            // "Beitrag.Zahlungsreferenz" today. Nullable: banktransfer members have
            // none.
            $table->string("payment_reference")->nullable();
            // PayPal/card billing agreement token — "Beitrag.PayPal_Vault" today.
            // Not modelled as a table of its own; MetaGer never stores card data,
            // only this opaque reference PayPal resolves on its side.
            $table->string("paypal_vault_id")->nullable();
            $table->date("join_date")->nullable();
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
