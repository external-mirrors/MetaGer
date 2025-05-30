<?php

namespace App\Models\Membership;

use App\Models\Membership\MembershipContact;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id 
 */
class MembershipApplication extends Model
{
    use HasUuids;

    public function contact(): HasOne
    {
        return $this->hasOne(MembershipContact::class, "application_id");
    }
}
