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
        // The payment-ledger design pass in docs/civicrm-replacement.md: one row
        // per accrual/payment/adjustment event against a membership. A
        // membership's balance is derived by summing these — assoc_debits keeps
        // its existing role as "a specific SEPA collection attempt" but stops
        // being what payment-status is read from.
        Schema::create('assoc_ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary(true);
            // Nullable: a donation-sourced entry (see assoc_debits.source)
            // has no Membership at all — its payer is reached via debit_id
            // instead (every donation-sourced entry, auto or manual, always
            // carries one — see DebitCreator/BankStatementMatcher).
            $table->uuid("membership_id")->nullable()->references("id")->on("assoc_memberships");
            // The Debit/BankStatementLine this entry came from, where
            // applicable — a manual admin adjustment (waiver, a refund not
            // yet tied to a specific statement line) has neither.
            $table->uuid("debit_id")->nullable()->references("id")->on("assoc_debits");
            $table->uuid("bank_statement_line_id")->nullable()->references("id")->on("assoc_bank_statement_lines");
            // charge: accrued from the membership's own amount/interval, the
            //   source of truth for what's owed — never a payment's amount.
            // payment: received, from whatever channel.
            // chargeback_fee: owed, excluded from donation-receipt totals.
            // waiver: admin write-off on an accepted late cancellation.
            // refund: admin-recorded outgoing amount.
            $table->enum("kind", ["charge", "payment", "chargeback_fee", "waiver", "refund"]);
            $table->decimal("amount", 10, 2);
            // How the money moved — set for payment/refund, null for
            // charge/chargeback_fee/waiver, which aren't a transfer of money
            // by themselves. Refunds route differently by channel (SEPA
            // credit transfer vs. PayPal); a payment's channel is preserved
            // so a later refund can be routed the same way it arrived.
            $table->enum("channel", ["directdebit", "banktransfer", "paypal", "sepa_credit_transfer"])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assoc_ledger_entries');
    }
};
