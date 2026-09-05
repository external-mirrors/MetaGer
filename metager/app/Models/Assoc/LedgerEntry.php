<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One accrual/payment/adjustment event against a Membership or, for a
 * donation-sourced Debit with no Membership at all, reached via debit_id
 * instead — see the "Payment-ledger design pass" section of
 * docs/civicrm-replacement.md.
 *
 * @property string $id
 * @property string|null $membership_id
 * @property Membership|null $membership
 * @property string|null $debit_id
 * @property Debit|null $debit
 * @property string|null $bank_statement_line_id
 * @property BankStatementLine|null $bankStatementLine
 * @property string $kind
 * @property string $amount
 * @property string|null $channel
 */
class LedgerEntry extends Model
{
    use HasUuids;

    protected $table = "assoc_ledger_entries";

    protected $fillable = ["membership_id", "debit_id", "bank_statement_line_id", "kind", "amount", "channel"];

    protected $casts = [
        "amount" => "decimal:2",
    ];

    /**
     * The sign each kind contributes to Membership::ledgerBalance() — a
     * positive balance is what's owed, negative is a credit carried
     * forward. See Membership::ledgerBalance()'s docblock for the reasoning.
     */
    public const BALANCE_SIGN = [
        "charge" => 1,
        "payment" => -1,
        "chargeback_fee" => 1,
        "waiver" => -1,
        "refund" => 1,
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, "membership_id");
    }

    /**
     * German-only, same reasoning as Membership's own label methods — these
     * feed admin views only (see AssocController's docblock).
     */
    public function kindLabel(): string
    {
        return match ($this->kind) {
            "charge" => "Belastung",
            "payment" => "Zahlung",
            "chargeback_fee" => "Rücklastschriftgebühr",
            "waiver" => "Erlass",
            "refund" => "Erstattung",
            default => $this->kind,
        };
    }

    public function channelLabel(): ?string
    {
        return match ($this->channel) {
            null => null,
            "directdebit" => "Lastschrift",
            "banktransfer" => "Überweisung",
            "paypal" => "PayPal",
            "sepa_credit_transfer" => "SEPA-Überweisung",
            default => $this->channel,
        };
    }

    public function debit(): BelongsTo
    {
        return $this->belongsTo(Debit::class, "debit_id");
    }

    public function bankStatementLine(): BelongsTo
    {
        return $this->belongsTo(BankStatementLine::class, "bank_statement_line_id");
    }
}
