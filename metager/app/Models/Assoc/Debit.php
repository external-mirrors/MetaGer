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
 * @property Contact|null $contact
 * @property Company|null $company
 * @property string $source
 * @property string $iban
 * @property string|null $bic
 * @property string $account_holder
 * @property string $amount
 * @property string $mandate
 * @property \Carbon\Carbon $mandate_date
 * @property string $status
 * @property string $end_to_end_reference
 * @property \Carbon\Carbon $due_date
 * @property string|null $reference
 * @property string|null $donation_receipt_id
 * @property DonationReceipt|null $donationReceipt
 */
class Debit extends Model
{
    use HasUuids;

    protected $table = "assoc_debits";

    protected $fillable = ["civicrm_id", "contact_id", "company_id", "source", "iban", "bic", "account_holder", "amount", "mandate", "mandate_date", "status", "end_to_end_reference", "due_date", "reference", "donation_receipt_id"];

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

    public function donationReceipt(): BelongsTo
    {
        return $this->belongsTo(DonationReceipt::class, "donation_receipt_id");
    }

    public function payer(): Contact|Company|null
    {
        return $this->contact ?? $this->company;
    }
}
