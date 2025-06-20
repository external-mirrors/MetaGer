<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $title
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property MembershipApplication $application
 */
class MembershipContact extends Model
{
    use HasUuids;
    protected $fillable = ["title", "first_name", "last_name", "email", "application_id"];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, "application_id");
    }
}
