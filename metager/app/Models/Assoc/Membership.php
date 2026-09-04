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
 * @property string $standing
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

    protected $fillable = ["civicrm_id", "contact_id", "company_id", "membership_type", "reduced", "interval", "amount", "payment_method", "payment_reference", "paypal_vault_id", "join_date", "standing", "start_date", "end_date", "renewed_at", "reduced_until", "locale", "key_id", "mastodon_id"];

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

    /**
     * German-only, deliberately not routed through the translation files —
     * the admin views this feeds render whatever locale the visitor's
     * browser negotiates (App\Http\Middleware\ResolveLocale runs on every
     * route, including admin/*), and suma-ev's imported membership data is
     * a German-language, German-staff-only concern that never appears on
     * the public, multi-locale membership pages.
     */
    public function standingLabel(): string
    {
        return match ($this->standing) {
            "terminated" => "Ausgetreten",
            "deceased" => "Verstorben",
            default => "Aktiv",
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            "exempt" => "Beitragsbefreit",
            "directdebit" => "Lastschrift",
            "banktransfer" => "Überweisung",
            "paypal" => "PayPal",
            "card" => "Kreditkarte",
            default => $this->payment_method,
        };
    }

    public function intervalLabel(): string
    {
        return match ($this->interval) {
            "monthly" => "monatlich",
            "quarterly" => "vierteljährlich",
            "six-monthly" => "halbjährlich",
            "annual" => "jährlich",
            default => $this->interval,
        };
    }
}
