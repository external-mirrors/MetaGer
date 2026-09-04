<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int|null $civicrm_id
 * @property string|null $contact_id
 * @property string|null $company_id
 * @property string|null $household_id
 * @property Contact|null $contact
 * @property Company|null $company
 * @property Household|null $household
 * @property string $source
 * @property string $iban
 * @property string $amount
 * @property string $mandate
 * @property \Carbon\Carbon $mandate_date
 * @property string $frequency
 * @property bool $active
 * @property \Carbon\Carbon|null $next_due_date
 */
class RecurContribution extends Model
{
    use HasUuids;

    protected $table = "assoc_recur_contributions";

    protected $fillable = ["civicrm_id", "contact_id", "company_id", "household_id", "source", "iban", "amount", "mandate", "mandate_date", "frequency", "active", "next_due_date"];

    protected $casts = [
        "amount" => "decimal:2",
        "mandate_date" => "date",
        "next_due_date" => "date",
        "active" => "boolean",
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
