<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string|null $contact_id
 * @property string|null $company_id
 * @property Contact|null $contact
 * @property Company|null $company
 * @property string $membership_type
 * @property bool $reduced
 * @property string $interval
 * @property string $status
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property \Carbon\Carbon|null $renewed_at
 * @property string|null $key_id
 */
class Membership extends Model
{
    use HasUuids;

    protected $table = "assoc_memberships";

    protected $fillable = ["contact_id", "company_id", "membership_type", "reduced", "interval", "status", "start_date", "end_date", "renewed_at", "key_id"];

    protected $casts = [
        "reduced" => "boolean",
        "start_date" => "date",
        "end_date" => "date",
        "renewed_at" => "date",
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
