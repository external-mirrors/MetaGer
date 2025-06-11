<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $iban
 * @property string $bic
 * @property string $accountholder
 */
class MembershipPaymentDirectdebit extends Model
{
    use HasUuids;
    protected $fillable = ["iban", "bic", "accountholder"];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class);
    }
}
