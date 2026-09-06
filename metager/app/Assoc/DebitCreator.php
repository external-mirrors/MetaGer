<?php

namespace App\Assoc;

use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use App\Models\Assoc\RecurContribution;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Ported from de.suma-ev.donation-debit's Membership.CreateDebits and
 * RecurContribution.CreateDebits API4 actions — the two cron jobs that
 * generate the assoc_debits rows a SEPA batch (phase 6c) later collects (for
 * directdebit memberships only — a banktransfer debit, see below, is never
 * SEPA-collected, only ever accrued and waited on). "Membership" here means
 * membership dues (directdebit or banktransfer); RecurContribution covers
 * recurring donations only — civicrm_recur_contribution never had a
 * membership_id, dues were always driven off the membership row itself (see
 * CiviCrmImporter::importRecurContributions()).
 *
 * Both legacy actions used their own "is a debit already in flight for this"
 * guard to avoid re-offering the same due payment on every run — Membership's
 * via a Beitrag.Zahlungsstatus custom-field flag, RecurContribution's via a
 * civicrm_debit.recur_contribution_id foreign key. Neither exists here
 * (assoc_debits carries no membership_id/recur_contribution_id — see the
 * migration), so both are derived instead from the one thing that does link a
 * Debit back to its origin: a pending row already sharing the same mandate.
 * This guard is also what makes a member who silently stops paying stop
 * accruing new charges on top of the unpaid one: their last debit just stays
 * pending, blocking a new one, until it's confirmed or the membership is
 * cancelled elsewhere — no separate "in arrears" check needed.
 *
 * Membership dues have a second gap the recurring-donation side doesn't:
 * assoc_memberships carries no iban/bic/account_holder of its own (legacy
 * kept those on Membership custom fields 33-35, never imported — see
 * CiviCrmImporter::importMemberships()). A new due directdebit membership's
 * bank details are instead snapshotted from its own most recent assoc_debits
 * row sharing the same mandate, the same per-row-snapshot pattern every
 * historical imported debit already uses. A directdebit membership with no
 * debit history at all (a brand-new direct-debit sign-up never yet billed)
 * has nothing to snapshot from and is skipped rather than guessed at. A
 * banktransfer membership has no bank details to snapshot in the first
 * place — its assoc_debits.iban/bic stay null — so it is never skipped for
 * lack of history; its account_holder falls back to the payer's own name,
 * the same fallback createForRecurContribution() already uses for donations.
 */
class DebitCreator
{
    /** Membership.CreateDebits: 12 days of its own headroom + the 14-day SEPA forerun. */
    private const MEMBERSHIP_DUE_WITHIN_DAYS = 26;

    /** RecurContribution.CreateDebits' SEND_DEBIT_TO_BANK_FORERUN_DAYS. */
    private const RECUR_CONTRIBUTION_DUE_WITHIN_DAYS = 14;

    /**
     * @return Collection<int, Debit>
     */
    public function createForDueMemberships(int $limit = 25): Collection
    {
        $cutoff = Carbon::today()->addDays(self::MEMBERSHIP_DUE_WITHIN_DAYS);

        $due = Membership::whereIn("payment_method", ["directdebit", "banktransfer"])
            ->where("standing", "active")
            ->whereNotNull("payment_reference")
            ->whereNotNull("end_date")
            ->where("end_date", "<", $cutoff)
            ->limit($limit)
            ->get();

        $created = collect();
        foreach ($due as $membership) {
            $debit = $this->createForMembership($membership);
            if ($debit !== null) {
                $created->push($debit);
            }
        }

        return $created;
    }

    private function createForMembership(Membership $membership): ?Debit
    {
        if ($this->hasPendingDebit($membership->payment_reference)) {
            return null;
        }

        $bankDetails = Debit::where("mandate", $membership->payment_reference)
            ->orderByDesc("due_date")
            ->first();
        if ($bankDetails === null && $membership->payment_method === "directdebit") {
            // No collection history to snapshot bank details from — see the
            // class docblock. Left for manual handling rather than guessed at.
            // Doesn't apply to banktransfer: there is never anything to
            // snapshot for it (see the iban/bic null-safety below), so a
            // first-ever charge is not blocked on prior history.
            return null;
        }

        $months = Membership::MONTHS_PER_INTERVAL[$membership->interval];
        $dueDate = $membership->end_date->copy();

        $debit = Debit::create([
            "contact_id" => $membership->contact_id,
            "company_id" => $membership->company_id,
            "membership_id" => $membership->id,
            "source" => "membership",
            "iban" => $bankDetails?->iban,
            "bic" => $bankDetails?->bic,
            "account_holder" => $bankDetails?->account_holder
                ?? $membership->contact?->name()
                ?? $membership->company?->name,
            "amount" => $membership->amount,
            "mandate" => $membership->payment_reference,
            "mandate_date" => $membership->join_date ?? $bankDetails?->mandate_date,
            "status" => "pending",
            "end_to_end_reference" => $this->uniqueEndToEndReference(),
            "due_date" => $dueDate,
            "reference" => $this->reference("Mitgliedsbeitrag", $dueDate, $months),
        ]);

        // The accrual half of the payment-ledger design pass (see
        // docs/civicrm-replacement.md): this is what's owed, becoming due —
        // BankStatementMatcher::confirm() records the other half once it's
        // actually paid.
        LedgerEntry::create([
            "membership_id" => $membership->id,
            "debit_id" => $debit->id,
            "kind" => "charge",
            "amount" => $debit->amount,
        ]);

        return $debit;
    }

    /**
     * @return Collection<int, Debit>
     */
    public function createForDueRecurContributions(int $limit = 25): Collection
    {
        $cutoff = Carbon::today()->addDays(self::RECUR_CONTRIBUTION_DUE_WITHIN_DAYS);

        $due = RecurContribution::where("active", true)
            ->where(function ($query) use ($cutoff) {
                $query->whereNull("next_due_date")->orWhere("next_due_date", "<=", $cutoff);
            })
            ->limit($limit)
            ->get();

        $created = collect();
        foreach ($due as $recurContribution) {
            if ($recurContribution->next_due_date === null) {
                $recurContribution->next_due_date = $this->initialNextDueDate();
                $recurContribution->save();
            }

            if (!$this->hasPendingDebit($recurContribution->mandate)) {
                $created->push($this->createForRecurContribution($recurContribution));
            }

            $months = Membership::MONTHS_PER_INTERVAL[$recurContribution->frequency];
            $recurContribution->next_due_date = $recurContribution->next_due_date->copy()->addMonths($months);
            $recurContribution->save();
        }

        return $created;
    }

    private function createForRecurContribution(RecurContribution $recurContribution): Debit
    {
        $months = Membership::MONTHS_PER_INTERVAL[$recurContribution->frequency];
        $dueDate = $recurContribution->next_due_date->copy();

        $debit = Debit::create([
            "contact_id" => $recurContribution->contact_id,
            "company_id" => $recurContribution->company_id,
            "source" => $recurContribution->source,
            "iban" => $recurContribution->iban,
            "bic" => $recurContribution->bic,
            "account_holder" => $recurContribution->account_holder
                ?? $recurContribution->contact?->name()
                ?? $recurContribution->company?->name,
            "amount" => $recurContribution->amount,
            "mandate" => $recurContribution->mandate,
            "mandate_date" => $recurContribution->mandate_date,
            "status" => "pending",
            "end_to_end_reference" => $this->uniqueEndToEndReference(),
            "due_date" => $dueDate,
            "reference" => $this->reference("Vielen Dank für Ihre Spende", $dueDate, $months),
        ]);

        // The accrual half of the payment-ledger design pass (see
        // docs/civicrm-replacement.md) — mirrors createForMembership()'s own
        // charge entry. membership_id stays null: a recurring donation has
        // no Membership, only a payer reached via this Debit's own
        // contact_id/company_id.
        LedgerEntry::create([
            "debit_id" => $debit->id,
            "kind" => "charge",
            "amount" => $debit->amount,
        ]);

        return $debit;
    }

    /**
     * The 3rd of this month if today is still early in it, else the 3rd of
     * next month — ported as-is from RecurContribution::CreateDebits' fix-up
     * for a recur contribution that has never had a next_debit date set.
     */
    private function initialNextDueDate(): Carbon
    {
        $today = Carbon::today();
        $thirdOfThisMonth = Carbon::create($today->year, $today->month, 3);

        return $today->day <= 10 ? $thirdOfThisMonth : $thirdOfThisMonth->addMonth();
    }

    private function hasPendingDebit(?string $mandate): bool
    {
        if ($mandate === null) {
            return false;
        }

        // "submitted" counts too: a debit already sent out in a SEPA batch is
        // still unresolved, just no longer eligible for a fresh one — see
        // SepaDirectDebitBatchGenerator and the assoc_debits status comment.
        return Debit::where("mandate", $mandate)->whereIn("status", ["pending", "submitted"])->exists();
    }

    private function reference(string $prefix, Carbon $start, int $months): string
    {
        $startLabel = $start->copy()->locale("de")->translatedFormat("M Y");
        if ($months <= 1) {
            return "{$prefix} {$startLabel}";
        }

        $endLabel = $start->copy()->addMonths($months - 1)->locale("de")->translatedFormat("M Y");

        return "{$prefix} {$startLabel} - {$endLabel}";
    }

    /**
     * Ported from CRM_DonationDebit_BAO_Debit::createTransactionID() ("P" plus
     * a microsecond timestamp), with the same collision retry — vanishingly
     * unlikely with microsecond resolution, but so was the original's.
     */
    private function uniqueEndToEndReference(): string
    {
        do {
            $candidate = "P" . Carbon::now()->format("YmdHisu");
        } while (Debit::where("end_to_end_reference", $candidate)->exists());

        return $candidate;
    }
}
