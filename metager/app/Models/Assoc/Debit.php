<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property int|null $civicrm_id
 * @property string|null $contact_id
 * @property string|null $company_id
 * @property Contact|null $contact
 * @property Company|null $company
 * @property string|null $membership_id
 * @property Membership|null $membership
 * @property string $source
 * @property string|null $iban
 * @property string|null $bic
 * @property string $account_holder
 * @property string $amount
 * @property string $mandate
 * @property \Carbon\Carbon $mandate_date
 * @property string $status
 * @property string $end_to_end_reference
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $previous_end_date
 * @property string|null $reference
 * @property string|null $donation_receipt_id
 * @property DonationReceipt|null $donationReceipt
 * @property string|null $sepa_batch_id
 * @property SepaBatch|null $sepaBatch
 */
class Debit extends Model
{
    use HasUuids;

    protected $table = "assoc_debits";

    protected $fillable = ["civicrm_id", "contact_id", "company_id", "membership_id", "source", "iban", "bic", "account_holder", "amount", "mandate", "mandate_date", "status", "end_to_end_reference", "due_date", "previous_end_date", "reference", "donation_receipt_id", "sepa_batch_id"];

    protected $casts = [
        "amount" => "decimal:2",
        "mandate_date" => "date",
        "due_date" => "date",
        "previous_end_date" => "date",
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, "contact_id");
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, "company_id");
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, "membership_id");
    }

    public function donationReceipt(): BelongsTo
    {
        return $this->belongsTo(DonationReceipt::class, "donation_receipt_id");
    }

    public function sepaBatch(): BelongsTo
    {
        return $this->belongsTo(SepaBatch::class, "sepa_batch_id");
    }

    public function payer(): Contact|Company|null
    {
        return $this->contact ?? $this->company;
    }

    /**
     * Every ledger event tied to this specific collection attempt — the
     * charge that accrued it, a chargeback's refund/fee if it bounced, and
     * any payment that settled either. This is the whole history an admin
     * looking at one Debit needs (see AssocController::debit()).
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, "debit_id");
    }

    /**
     * What was actually, finally kept from this debit — payment entries add,
     * a refund (whether an automatic chargeback reversal or a manual admin
     * refund, see LedgerEntryController::storeForDebit()) subtracts. Never a
     * chargeback_fee: that kind only ever attaches to the *bounced* debit
     * itself (LedgerEntry::create() in BankStatementMatcher::
     * confirmChargeback()), never to the debit currently being collected, so
     * it structurally can't appear in this sum — a bank fee is never a
     * donation or a dues payment, and this is decision 4 of the
     * payment-ledger design pass (docs/civicrm-replacement.md) satisfied
     * without needing to special-case it here.
     *
     * Clamped to a minimum of 0 — a debit that was fully charged back or
     * refunded must never look receiptable as a negative amount.
     *
     * Falls back to $this->amount when there are no ledger entries at all —
     * CiviCrmImporter::importDebits() doesn't backfill the ledger for
     * historical debits, so an imported one would otherwise look completely
     * unpaid; a known, explicitly deferred gap (see
     * docs/civicrm-replacement.md), not solved here.
     */
    public function netLedgerAmount(): string
    {
        if ($this->ledgerEntries->isEmpty()) {
            return $this->amount;
        }

        $cents = 0;
        foreach ($this->ledgerEntries as $entry) {
            if ($entry->kind === "payment") {
                $cents += (int) round($entry->amount * 100);
            } elseif ($entry->kind === "refund") {
                $cents -= (int) round($entry->amount * 100);
            }
        }

        return number_format(max(0, $cents) / 100, 2, ".", "");
    }
}
