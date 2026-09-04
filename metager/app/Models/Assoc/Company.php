<?php

namespace App\Models\Assoc;

use App\Models\Assoc\Concerns\HasDonationReceiptPreference;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property int|null $civicrm_id
 * @property string $name
 * @property string|null $contact_person_id
 * @property Contact|null $contactPerson
 * @property string|null $street
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property string|null $tax_id
 * @property string|null $donation_receipt_preference
 */
class Company extends Model
{
    use HasUuids;
    use HasDonationReceiptPreference;

    protected $table = "assoc_companies";

    protected $fillable = ["civicrm_id", "name", "contact_person_id", "street", "postal_code", "city", "country", "tax_id", "donation_receipt_preference"];

    public function contactPerson(): BelongsTo
    {
        return $this->belongsTo(Contact::class, "contact_person_id");
    }

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class, "company_id");
    }

    public function debits(): HasMany
    {
        return $this->hasMany(Debit::class, "company_id");
    }

    public function recurContributions(): HasMany
    {
        return $this->hasMany(RecurContribution::class, "company_id");
    }

    public function donationReceipts(): HasMany
    {
        return $this->hasMany(DonationReceipt::class, "company_id");
    }
}
