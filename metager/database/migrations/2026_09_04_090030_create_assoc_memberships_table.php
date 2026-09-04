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
            // can't be derived from those tables. "exempt" replaces CiviCRM's two
            // separate membership types for this (Ehrenmitglied/honorary,
            // Gegenseitigkeit/reciprocity — membership_type_id 21/22 in the
            // production dump, 9 of 2311 rows): both mean the same thing here,
            // "no dues are ever collected from this member," which is a billing
            // fact, not a membership-type distinction — see ChargeKeys.php, which
            // already charges every member's key at a flat 5€/month default
            // whenever the computed price is 0, with no distinction between the
            // two CiviCRM types.
            $table->enum("payment_method", ["banktransfer", "directdebit", "paypal", "card", "exempt"]);
            // The mandate/reference tying this membership to its debit row(s) —
            // "Beitrag.Zahlungsreferenz" today. Nullable: banktransfer/exempt
            // members have none.
            $table->string("payment_reference")->nullable();
            // PayPal/card billing agreement token — "Beitrag.PayPal_Vault" today.
            // Not modelled as a table of its own; MetaGer never stores card data,
            // only this opaque reference PayPal resolves on its side.
            $table->string("paypal_vault_id")->nullable();
            $table->date("join_date")->nullable();
            // Whether this person currently counts as a member — an admin action
            // (someone marked them "Ausgetreten"/"Verstorben"), not a payment
            // state. Deliberately not CiviCRM's 8-value Zahlungsstatus: the other
            // six values there (Eingetreten, Okay, Warte auf Lastschrifteingang,
            // Erste/Zweite Zahlungserinnerung) are all "how is dues collection
            // going for a banktransfer/directdebit member right now" — derivable
            // from end_date and the member's own assoc_debits rows (a directdebit
            // member has an unresolved "warte auf Lastschrifteingang" exactly when
            // they have a pending assoc_debits row — see
            // Civi\Api4\Action\Membership\CreateDebits, custom_37 => 2), so storing
            // it again here would just be re-deriving CiviCRM's own derived state
            // rather than the underlying fact.
            $table->enum("standing", ["active", "terminated", "deceased"])->default("active");
            $table->date("start_date")->nullable();
            $table->date("end_date")->nullable();
            $table->date("renewed_at")->nullable();
            // "Beitrag.Erm_igt_bis" today — until when the reduced rate applies.
            // FIND_REDUCTION_REMINDER reads this to warn a member their proof of
            // eligibility is expiring; nullable because most members pay full rate.
            $table->date("reduced_until")->nullable();
            // "Beitrag.Locale" today — which language to send reminder/service
            // emails in. Not derived from the contact: CiviCrm.php stores it
            // per-membership, independently of any browser/account locale.
            $table->string("locale")->nullable();
            // The MetaGer search key tied to this membership (see ChargeKeys in the
            // donation-debit extension). A plain identifier, not a foreign key —
            // keys are owned by the separate keymanager service.
            $table->uuid("key_id")->nullable();
            // "Mastodon.Mastodon_ID" today — confirmed against the production dump
            // to extend Membership, not Contact (civicrm_value_mastodon_10.entity_id
            // is a foreign key to civicrm_membership): a contact who re-joins after
            // lapsing could reasonably register a different Mastodon account against
            // the new membership, so this belongs here and not on assoc_contacts.
            $table->string("mastodon_id")->nullable();
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
