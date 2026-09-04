<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $street
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property string|null $mastodon_id
 */
class Contact extends Model
{
    use HasUuids;

    protected $table = "assoc_contacts";

    protected $fillable = ["first_name", "last_name", "email", "street", "postal_code", "city", "country", "mastodon_id"];

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
}
