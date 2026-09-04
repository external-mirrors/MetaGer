<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $household_name
 * @property string|null $street
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 */
class Household extends Model
{
    use HasUuids;

    protected $table = "assoc_households";

    protected $fillable = ["household_name", "street", "postal_code", "city", "country"];

    public function debits(): HasMany
    {
        return $this->hasMany(Debit::class, "household_id");
    }

    public function recurContributions(): HasMany
    {
        return $this->hasMany(RecurContribution::class, "household_id");
    }
}
