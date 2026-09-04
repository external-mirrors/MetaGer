<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string|null $contact_id
 * @property string|null $company_id
 * @property string|null $household_id
 * @property Contact|null $contact
 * @property Company|null $company
 * @property Household|null $household
 * @property string $source
 * @property string $iban
 * @property string $account_holder
 * @property string $amount
 * @property string $mandate
 * @property \Carbon\Carbon $mandate_date
 * @property string $status
 * @property string $end_to_end_reference
 * @property \Carbon\Carbon $due_date
 * @property string|null $reference
 */
class Debit extends Model
{
    use HasUuids;

    protected $table = "assoc_debits";

    protected $fillable = ["contact_id", "company_id", "household_id", "source", "iban", "account_holder", "amount", "mandate", "mandate_date", "status", "end_to_end_reference", "due_date", "reference"];

    protected $casts = [
        "amount" => "decimal:2",
        "mandate_date" => "date",
        "due_date" => "date",
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, "contact_id");
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, "company_id");
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, "household_id");
    }
}
