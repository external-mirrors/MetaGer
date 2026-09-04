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
 * @property string $membership_type
 * @property bool $reduced
 * @property string $interval
 * @property string $amount
 * @property string $payment_method
 * @property string|null $payment_reference
 * @property string|null $paypal_vault_id
 * @property \Carbon\Carbon|null $join_date
 * @property string $status
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property \Carbon\Carbon|null $renewed_at
 * @property \Carbon\Carbon|null $reduced_until
 * @property string|null $locale
 * @property string|null $key_id
 * @property string|null $mastodon_id
 */
class Membership extends Model
{
    use HasUuids;

    protected $table = "assoc_memberships";

    protected $fillable = ["civicrm_id", "contact_id", "company_id", "membership_type", "reduced", "interval", "amount", "payment_method", "payment_reference", "paypal_vault_id", "join_date", "status", "start_date", "end_date", "renewed_at", "reduced_until", "locale", "key_id", "mastodon_id"];

    protected $casts = [
        "reduced" => "boolean",
        "amount" => "decimal:2",
        "join_date" => "date",
        "start_date" => "date",
        "end_date" => "date",
        "renewed_at" => "date",
        "reduced_until" => "date",
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, "contact_id");
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, "company_id");
    }
}
