<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|null $contact_id
 * @property string|null $company_id
 * @property string|null $household_id
 * @property Contact|null $contact
 * @property Company|null $company
 * @property Household|null $household
 * @property int $year
 * @property string $total_amount
 * @property string $source
 * @property \Carbon\Carbon|null $generated_at
 * @property string|null $pdf_path
 */
class DonationReceipt extends Model
{
    use HasUuids;

    protected $table = "assoc_donation_receipts";

    protected $fillable = ["contact_id", "company_id", "household_id", "year", "total_amount", "source", "generated_at", "pdf_path"];

    protected $casts = [
        "total_amount" => "decimal:2",
        "generated_at" => "datetime",
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

    public function debits(): HasMany
    {
        return $this->hasMany(Debit::class, "donation_receipt_id");
    }

    public function payer(): Contact|Company|Household|null
    {
        return $this->contact ?? $this->company ?? $this->household;
    }

    public function sourceLabel(): string
    {
        return match ($this->source) {
            "membership" => "Beitragsbescheinigung",
            default => "Spendenbescheinigung",
        };
    }
}
