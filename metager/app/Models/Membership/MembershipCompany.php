<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $company
 * @property string $employees
 * @property string $email
 * @property string $application_id
 */
class MembershipCompany extends Model
{
    use HasUuids;
    protected $fillable = ["company", "employees", "email", "application_id"];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, "application_id");
    }
}
