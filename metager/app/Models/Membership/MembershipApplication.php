<?php

namespace App\Models\Membership;

use App\Models\Membership\MembershipContact;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id 
 * @property MembershipContact $contact
 * @property MembershipCompany $company
 * @property MembershipReduction $reduction
 * @property MembershipPaymentDirectdebit $directdebit
 * @property MembershipPaymentPaypal $paypal
 * @property int $crm_contact
 * @property int $crm_membership
 * @property float $amount
 * @property string $interval
 * @property string $payment_method
 * @property string $payment_reference
 * @property string $key
 * @property string $locale
 * @property boolean $is_update
 */
class MembershipApplication extends Model
{
    use HasUuids;

    protected $fillable = ["locale", "amount", "interval", "payment_method", "payment_reference", "key", "crm_contact", "crm_membership", "payment_reference", "is_update"];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_update' => false,
    ];

    /**
     * Scope to query all applications containing all required data
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeFinished(Builder $query)
    {
        $query
            ->where("is_update", "=", false)
            ->where(function (Builder $query) {
                $query->whereHas("contact")->orWhereHas("company")->orWhereNotNull("crm_contact");
            })->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNotNull("amount")->wherenotNull("interval")->where(function (Builder $query) {
                        $query->has("reduction", "=", 0)->orWhereRelation("reduction", "expires_at", "!=", null);
                    });
                })->orWhereNotNull("crm_membership");
            })
            ->whereNotNull("payment_method");
    }

    /**
     * Scope to query all applications containing all required data
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeUpdateRequests(Builder $query)
    {
        $query
            ->where("is_update", "=", true)
            ->whereNotNull("crm_membership")
            ->where(function (Builder $query) {
                $query->whereHas("contact")->orWhereHas("company")->orWhereNotNull("crm_contact");
            })->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNotNull("amount")->wherenotNull("interval")->where(function (Builder $query) {
                        $query->has("reduction", "=", 0)->orWhereRelation("reduction", "expires_at", "!=", null);
                    });
                })->orWhereNotNull("crm_membership");
            })
            ->whereNotNull("payment_method");
    }

    /**
     * Scope to query all applications containing all required data
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeReductionRequests(Builder $query)
    {
        $query
            ->has("reduction")
            ->whereRelation("reduction", "expires_at", "=", null);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(MembershipContact::class, "application_id");
    }

    public function company(): HasOne
    {
        return $this->hasOne(MembershipCompany::class, "application_id");
    }

    public function reduction(): HasOne
    {
        return $this->hasOne(MembershipReduction::class, "application_id");
    }

    public function directdebit(): HasOne
    {
        return $this->hasOne(MembershipPaymentDirectdebit::class, "application_id");
    }

    public function paypal(): HasOne
    {
        return $this->hasOne(MembershipPaymentPaypal::class, "application_id");
    }

    public function isEmpty(): bool
    {
        return $this->locale === null &&
            $this->amount === null &&
            $this->interval === null &&
            $this->payment_method === null &&
            $this->payment_reference;
    }

    /**
     * Returns an editable instance if this instance is not backed
     * by an database entry yet
     * @return MembershipApplication
     */
    public function editable(): MembershipApplication
    {
        if ($this->id !== null)
            return $this;

        $new_entry = MembershipApplication::create($this->attributes);
        if ($this->directdebit !== null) {
            $new_entry->directdebit()->create($this->directdebit->attributes);
        }
        if ($this->paypal !== null) {
            $new_entry->paypal()->create($this->paypal->attributes);
        }
        if ($this->directdebit !== null) {
            $new_entry->directdebit()->create($this->directdebit->attributes);
        }
        if ($this->reduction !== null) {
            $new_entry->reduction()->create($this->reduction->attributes);
        }
        if ($this->contact !== null) {
            $new_entry->contact()->create($this->contact->attributes);
        }
        if ($this->company !== null) {
            $new_entry->company()->create($this->company->attributes);
        }

        return $new_entry;
    }

    public function isComplete(): bool
    {
        return ($this->contact !== null || $this->company !== null) &&
            $this->locale !== null &&
            $this->amount !== null &&
            $this->payment_method !== null &&
            $this->payment_reference !== null && (
            $this->amount >= 5 ||
            ($this->reduction !== null &&
                $this->reduction->expires_at !== null)
        ) &&
            $this->interval !== null;
    }

    protected static function booted(): void
    {

        static::deleting(function (MembershipApplication $application) {
            // Delete relations aswell
            if ($application->contact !== null)
                $application->contact->delete();
            if ($application->company !== null)
                $application->company->delete();
            if ($application->reduction !== null)
                $application->reduction->delete();
            if ($application->directdebit !== null)
                $application->directdebit->delete();
            if ($application->paypal !== null) {
                if ($application->paypal->order_id === null)
                    $application->paypal->delete();
                else {
                    $paypal = $application->paypal;
                    $paypal->vault_id = null;
                    $paypal->application_id = null;
                    $paypal->save();
                }
            }
        });

    }
}
