<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One accrual/payment/adjustment event against a Membership — see the
 * "Payment-ledger design pass" section of docs/civicrm-replacement.md.
 * Nothing in phases 4-6 reads this yet; Membership::ledgerBalance() is the
 * only consumer so far.
 *
 * @property string $id
 * @property string $membership_id
 * @property Membership $membership
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

    public function debit(): BelongsTo
    {
        return $this->belongsTo(Debit::class, "debit_id");
    }

    public function bankStatementLine(): BelongsTo
    {
        return $this->belongsTo(BankStatementLine::class, "bank_statement_line_id");
    }
}
