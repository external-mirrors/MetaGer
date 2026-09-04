<?php

namespace App\Models\Assoc;

use App\Models\Assoc\Concerns\HasDonationReceiptPreference;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property int|null $civicrm_id
 * @property string $household_name
 * @property string|null $street
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property string|null $donation_receipt_preference
 */
class Household extends Model
{
    use HasUuids;
    use HasDonationReceiptPreference;

    protected $table = "assoc_households";

    protected $fillable = ["civicrm_id", "household_name", "street", "postal_code", "city", "country", "donation_receipt_preference"];

    public function debits(): HasMany
    {
        return $this->hasMany(Debit::class, "household_id");
    }

    public function recurContributions(): HasMany
    {
        return $this->hasMany(RecurContribution::class, "household_id");
    }

    public function donationReceipts(): HasMany
    {
        return $this->hasMany(DonationReceipt::class, "household_id");
    }
}
