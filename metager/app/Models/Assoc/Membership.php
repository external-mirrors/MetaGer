<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int|null $civicrm_id
 * @property string|null $contact_id
 * @property string|null $company_id
 * @property Contact|null $contact
 * @property Company|null $company
 * @property string $membership_type
 * @property string $category
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
 * @property string|null $reminder_stage
 * @property string|null $key_id
 * @property string|null $mastodon_id
 */
class Membership extends Model
{
    use HasUuids;

    /**
     * Shared by DebitCreator (computing a due date/spanning reference) and
     * BankStatementMatcher (advancing end_date once a charge is confirmed
     * paid — see confirm()'s docblock).
     */
    public const MONTHS_PER_INTERVAL = [
        "monthly" => 1,
        "quarterly" => 3,
        "six-monthly" => 6,
        "annual" => 12,
    ];

    protected $table = "assoc_memberships";

    protected $fillable = ["civicrm_id", "contact_id", "company_id", "membership_type", "category", "reduced", "interval", "amount", "payment_method", "payment_reference", "paypal_vault_id", "join_date", "standing", "start_date", "end_date", "renewed_at", "reduced_until", "locale", "reminder_stage", "key_id", "mastodon_id"];

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
     * Which language to render this membership's correspondence in.
     * assoc_contacts.locale is the payer's own property and wins; a company
     * payer has none of its own, so its contactPerson stands in — the same
     * fallback PaymentReminderProcessor::recipient() already uses for email/
     * name. assoc_memberships.locale (CiviCRM's Beitrag.Locale, imported
     * per-membership before Contact had anywhere to put it) is the next
     * fallback, so already-imported data isn't discarded; config's
     * assoc.default_locale is the last resort.
     */
    public function resolvedLocale(): string
    {
        return $this->contact?->locale
            ?? $this->company?->contactPerson?->locale
            ?? $this->locale
            ?? config("assoc.default_locale");
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, "membership_id");
    }

    /**
     * Derived, not stored — the payment-ledger design pass's whole point
     * (see docs/civicrm-replacement.md). Positive: still owed. Negative:
     * overpaid, carried forward as a credit. Zero: settled.
     *
     * Sign convention (a decision made when this was built, not itself in
     * the design doc): charge/chargeback_fee add to what's owed;
     * payment/waiver reduce it; refund adds back what a payment had
     * reduced, since the money it paid down is no longer with the
     * association.
     */
    public function ledgerBalance(): string
    {
        // Summed in integer cents, not floats or bcmath (ext-bcmath isn't in
        // the fpm image — see build/fpm/Dockerfile) — decimal(10,2) amounts
        // never carry more than two fractional digits, so this is exact.
        $cents = 0;
        foreach ($this->ledgerEntries as $entry) {
            $cents += LedgerEntry::BALANCE_SIGN[$entry->kind] * (int) round($entry->amount * 100);
        }

        return number_format($cents / 100, 2, ".", "");
    }

    /**
     * What PaymentReminderProcessor measures a shortfall's age from (design
     * decision 2) — needed because the ledger records amounts but never
     * allocates a given payment to a specific charge. A FIFO walk: "debt"
     * entries (charge, chargeback_fee, refund — see
     * LedgerEntry::BALANCE_SIGN) are consumed oldest-first by the pooled
     * total of "reduction" entries (payment, waiver); the first debt entry
     * the pool can't fully cover is the oldest unpaid one. Ordered by the
     * entry's own Debit::due_date where one exists (charge/chargeback_fee
     * always have one; a chargeback's refund shares its debit's), falling
     * back to the entry's own created_at for an admin-recorded refund with
     * no debit at all.
     *
     * Returns null once the balance is fully covered — nothing left
     * unpaid, matching ledgerBalance()'s own sign convention.
     */
    public function oldestUnpaidChargeDueDate(): ?Carbon
    {
        $debtKinds = ["charge", "chargeback_fee", "refund"];

        $debts = $this->ledgerEntries
            ->filter(fn (LedgerEntry $entry) => in_array($entry->kind, $debtKinds, true))
            ->sortBy(fn (LedgerEntry $entry) => ($entry->debit?->due_date ?? $entry->created_at)->timestamp);

        $pool = 0;
        foreach ($this->ledgerEntries as $entry) {
            if (in_array($entry->kind, ["payment", "waiver"], true)) {
                $pool += (int) round($entry->amount * 100);
            }
        }

        foreach ($debts as $entry) {
            $cents = (int) round($entry->amount * 100);
            if ($cents <= $pool) {
                $pool -= $cents;
                continue;
            }

            return $entry->debit?->due_date ?? $entry->created_at;
        }

        return null;
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
