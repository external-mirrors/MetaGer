<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $order_id
 * @property string $authorization_id
 * @property string $authorization_status
 * @property string $vault_id
 * @property string $application_id
 * @property MembershipApplication $application
 */
class MembershipPaymentPaypal extends Model
{
    use HasUuids;
    protected $fillable = ["order_id", "authorization_id", "authorization_status", "vault_id", "application_id"];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class);
    }

    protected static function booted(): void
    {

        static::deleting(function (MembershipPaymentPaypal $paypal) {
            if ($paypal->authorization_id !== null && $paypal->authorization_status === "CREATED") {
                // There is a pending authorized payment. Let's void it before deleting this 
                PayPal::VOID_AUTHORIZATION($paypal->authorization_id);
            }
            $application = $paypal->application;
            if ($application !== null && $application->payment_method !== null) {
                $application->payment_method = null;
                $application->save();
            }
        });

    }
}
