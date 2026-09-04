<?php

namespace App\Models\Assoc;

use App\Models\Assoc\Concerns\HasDonationReceiptPreference;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property int|null $civicrm_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $display_name
 * @property string $email
 * @property string|null $street
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property string|null $donation_receipt_preference
 */
class Contact extends Model
{
    use HasUuids;
    use HasDonationReceiptPreference;

    protected $table = "assoc_contacts";

    protected $fillable = ["civicrm_id", "first_name", "last_name", "display_name", "email", "street", "postal_code", "city", "country", "donation_receipt_preference"];

    /**
     * Almost every contact has first_name+last_name. display_name exists for
     * the payer whose name only ever arrived as one unparsed string — what
     * CiviCRM's "Household" contact type was actually for here, never an
     * actual multi-person household (see the assoc_contacts migration).
     */
    public function name(): string
    {
        return $this->display_name ?? trim("{$this->first_name} {$this->last_name}");
    }

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class, "contact_id");
    }

    public function debits(): HasMany
    {
        return $this->hasMany(Debit::class, "contact_id");
    }

    public function recurContributions(): HasMany
    {
        return $this->hasMany(RecurContribution::class, "contact_id");
    }

    public function donationReceipts(): HasMany
    {
        return $this->hasMany(DonationReceipt::class, "contact_id");
    }
}
