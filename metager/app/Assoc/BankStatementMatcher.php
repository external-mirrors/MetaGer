<?php

namespace App\Assoc;

use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use App\Models\Assoc\RecurContribution;
use Illuminate\Support\Collection;

/**
 * Ported from de.suma-ev.bescheinigungen's FetchBankAccount::searchForMandates()/
 * checkMandates() and de.suma-ev.donation-debit's IncomingPayment/Auto.php — but
 * against our own assoc_debits/assoc_recur_contributions, which already carry the
 * mandate (and, for debits, the SEPA end-to-end reference) directly on each row.
 * The original had to round-trip through CiviCRM's API to collect known mandates
 * from Membership/RecurContribution; here it's two indexed queries.
 *
 * Phase 6 turned this live: a match now also flips the matched Debit from
 * "pending" to "executed", same as the CiviCRM original did the moment
 * IncomingPayment.Auto resolved a payment. Only a "debit" match has anything
 * to flip — a "recur_contribution" match means the payment arrived before any
 * per-collection assoc_debits row existed for it (see matchByMandate()), and
 * there is no status on RecurContribution itself to change.
 *
 * Four-tier cascade, matching the assoc_bank_statement_lines.match_method enum:
 *  1. mandate_reference — an exact match on a structured identifier the bank
 *     itself supplied: the SEPA end-to-end reference (Debit::end_to_end_reference,
 *     unique per collection) if the statement line carries one, else the SEPA
 *     mandate id itself.
 *  2. regex — the mandate id doesn't appear as a structured field, but is found
 *     as a whole word inside the free-text purpose ("Verwendungszweck") — the
 *     `\bMANDATE\b` search FetchBankAccount::searchForMandates() did against
 *     bank-transfer memo lines, which carry no structured SEPA fields at all.
 *  3. substring — loosest fallback: the mandate id appears anywhere in the free
 *     text, not bounded to a whole word (a member paraphrasing or truncating it).
 *  4. no match — left for manual triage in the admin UI.
 */
class BankStatementMatcher
{
    private ?Collection $pendingDebits = null;

    private ?Collection $activeRecurContributions = null;

    /**
     * @return bool whether the line was matched (and saved)
     */
    public function match(BankStatementLine $line, ?string $mandate = null, ?string $endToEndReference = null): bool
    {
        if ($endToEndReference !== null) {
            $debit = $this->pendingDebits()->firstWhere("end_to_end_reference", $endToEndReference);
            if ($debit !== null) {
                return $this->confirm($line, "debit", $debit->id, "mandate_reference");
            }
        }

        if ($mandate !== null && $this->matchByMandate($line, $mandate, "mandate_reference")) {
            return true;
        }

        if ($line->reference === null || trim($line->reference) === "") {
            return false;
        }

        foreach ($this->knownMandates() as $candidate) {
            if (preg_match("/\b" . preg_quote($candidate, "/") . "\b/u", $line->reference) === 1) {
                if ($this->matchByMandate($line, $candidate, "regex")) {
                    return true;
                }
            }
        }

        foreach ($this->knownMandates() as $candidate) {
            if (str_contains($line->reference, $candidate)) {
                if ($this->matchByMandate($line, $candidate, "substring")) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Re-attempts the regex/substring tiers (the ones that only need the free
     * text, not a structured mandate/end-to-end id the importer already tried)
     * against lines still unmatched from an earlier import — useful once new
     * debits/recur contributions exist that didn't when a line first came in.
     *
     * @return array{matched: int, still_unmatched: int}
     */
    public function rematchUnresolved(): array
    {
        $this->pendingDebits = null;
        $this->activeRecurContributions = null;

        $matched = 0;
        foreach (BankStatementLine::whereNull("matched_type")->get() as $line) {
            if ($this->match($line)) {
                $matched++;
            }
        }

        return [
            "matched" => $matched,
            "still_unmatched" => BankStatementLine::whereNull("matched_type")->count(),
        ];
    }

    private function matchByMandate(BankStatementLine $line, string $mandate, string $method): bool
    {
        $candidates = $this->pendingDebits()->where("mandate", $mandate)->sortBy("due_date");
        // Prefer a debit whose own amount matches the payment exactly — the
        // same mandate can carry several pending debits (recurring dues), and
        // the amount is the only other signal available to tell them apart.
        // Falls back to the earliest-due one on a mismatch (over/underpayment)
        // rather than leaving an otherwise-identified payment unmatched.
        $debit = $candidates->first(fn (Debit $d) => (string) $d->amount === (string) $line->amount)
            ?? $candidates->first();
        if ($debit !== null) {
            return $this->confirm($line, "debit", $debit->id, $method);
        }

        $recur = $this->activeRecurContributions()->firstWhere("mandate", $mandate);
        if ($recur !== null) {
            return $this->confirm($line, "recur_contribution", $recur->id, $method);
        }

        return false;
    }

    /**
     * Records a match — automatic or, via BankStatementController::match(),
     * manual — and, for a "debit" match, flips that Debit from "pending" to
     * "executed". Guarded to "pending" only: a manual match lets an admin pick
     * any debit regardless of its current status, and this must not silently
     * downgrade an already-"failed" (bounced/returned) collection back to
     * looking executed.
     *
     * A membership-dues debit (Debit::membership_id set — see DebitCreator)
     * also gets a "payment" LedgerEntry here, the other half of the
     * payment-ledger design pass (see docs/civicrm-replacement.md): a
     * "donation"-source debit has no Membership to record one against, so it
     * gets none.
     *
     * It also advances Membership::end_date — design decision 1 of the
     * payment-ledger pass ("coverage only advances once it's actually paid
     * for"). On-time, this is just end_date + one interval, the same
     * calculation DebitCreator used to set the debit's own due date. A debit
     * that sat pending past its due date (a member who stopped paying, then
     * resumed — see the class docblock's design-pass reference) would leave
     * end_date stale if simply advanced by one interval from wherever it
     * still was; instead coverage restarts fresh from the day the payment
     * was booked, so the membership "takes up again from that day forward"
     * as agreed, rather than slowly catching up one skipped period at a time
     * on subsequent assoc:create-debits runs. Nothing here creates a new
     * Membership row — the existing one just continues; multiple
     * memberships are only for a genuinely lapsed-then-rejoined member (see
     * docs/civicrm-replacement.md's retention section), not this case.
     */
    public function confirm(BankStatementLine $line, string $type, string $id, string $method, ?string $matchedBy = null): bool
    {
        $line->matched_type = $type;
        $line->matched_id = $id;
        $line->match_method = $method;
        $line->matched_by = $matchedBy;
        $line->matched_at = now();
        $line->save();

        if ($type === "debit") {
            $debit = Debit::where("id", $id)->where("status", "pending")->first();
            if ($debit !== null) {
                $debit->update(["status" => "executed"]);
                $membership = $debit->membership;
                if ($membership !== null) {
                    LedgerEntry::create([
                        "membership_id" => $membership->id,
                        "debit_id" => $debit->id,
                        "kind" => "payment",
                        "amount" => $debit->amount,
                        "channel" => $membership->payment_method,
                    ]);

                    $months = Membership::MONTHS_PER_INTERVAL[$membership->interval];
                    $onTimeAdvance = $membership->end_date->copy()->addMonths($months);
                    $membership->end_date = $onTimeAdvance->greaterThanOrEqualTo($line->booked_at)
                        ? $onTimeAdvance
                        : $line->booked_at->copy()->addMonths($months);
                    $membership->save();
                }
            }
        }

        return true;
    }

    /**
     * @return Collection<int, string> distinct mandate ids across pending
     *   debits and active recur contributions, longest first — a short mandate
     *   id that happens to be a substring of a longer, unrelated one must not
     *   shadow it.
     */
    private function knownMandates(): Collection
    {
        return $this->pendingDebits()->pluck("mandate")
            ->merge($this->activeRecurContributions()->pluck("mandate"))
            ->filter()
            ->unique()
            ->sortByDesc(fn (string $m) => strlen($m))
            ->values();
    }

    private function pendingDebits(): Collection
    {
        return $this->pendingDebits ??= Debit::where("status", "pending")->get();
    }

    private function activeRecurContributions(): Collection
    {
        return $this->activeRecurContributions ??= RecurContribution::where("active", true)->whereNotNull("mandate")->get();
    }
}
