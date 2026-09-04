<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $iban
 * @property string $amount
 * @property string|null $reference
 * @property \Carbon\Carbon $booked_at
 * @property string|null $matched_type
 * @property string|null $matched_id
 * @property string|null $match_method
 * @property string|null $matched_by
 * @property \Carbon\Carbon|null $matched_at
 */
class BankStatementLine extends Model
{
    use HasUuids;

    protected $table = "assoc_bank_statement_lines";

    protected $fillable = ["iban", "amount", "reference", "booked_at", "matched_type", "matched_id", "match_method", "matched_by", "matched_at"];

    protected $casts = [
        "booked_at" => "date",
        "matched_at" => "datetime",
    ];

    /**
     * The Debit or RecurContribution this line was matched to, per matched_type.
     * Not a real Eloquent relation — matched_id points into one of two tables
     * depending on matched_type, which a single relation can't express.
     */
    public function matched(): Debit|RecurContribution|null
    {
        if ($this->matched_type === null || $this->matched_id === null) {
            return null;
        }

        return match ($this->matched_type) {
            "debit" => Debit::find($this->matched_id),
            "recur_contribution" => RecurContribution::find($this->matched_id),
        };
    }
}
