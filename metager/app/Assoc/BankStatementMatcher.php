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
 *
 * A fifth path, matchChargeback()/confirmChargeback(), handles a
 * Rücklastschrift: not a payment but a reversal of one already recorded.
 * BankStatementImporter routes a line here instead of match() when it
 * recognises one (see its CHARGEBACK_ART_VALUES).
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
     * manual — and, for a "debit" match, flips that Debit from "pending" or
     * "submitted" to "executed". Guarded to those two only: a manual match
     * lets an admin pick any debit regardless of its current status, and
     * this must not silently downgrade an already-"failed" (bounced/
     * returned) collection back to looking executed.
     *
     * Records a "payment" LedgerEntry — the other half of the payment-ledger
     * design pass (see docs/civicrm-replacement.md) whose accrual half
     * DebitCreator writes. Membership-tied when the debit is (membership_id
     * set); a "donation"-source debit has no Membership, so it's tied by
     * debit_id alone instead.
     *
     * Before crediting this debit, settleOutstandingFees() first pays down
     * any chargeback fee still outstanding on this same mandate (a member
     * who bounced once and is now catching up) — only the remainder, if any,
     * is what actually gets credited here. See that method's docblock.
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
     * docs/civicrm-replacement.md's retention section), not this case. This
     * is membership-only — a donation has no "coverage" concept.
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
            // "submitted" is guarded here too, same reasoning as
            // pendingDebits(): a debit already sent out in a SEPA batch is
            // still exactly what a matching payment should confirm.
            $debit = Debit::where("id", $id)->whereIn("status", ["pending", "submitted"])->first();
            if ($debit !== null) {
                $debit->update(["status" => "executed"]);
                $membership = $debit->membership;

                // The amount actually received, not $debit->amount (what was
                // owed) — decision 1 of the payment-ledger design pass needs
                // the ledger to see a real under/overpayment, not silently
                // assume the charge was paid in full just because something
                // matched. May be less than $line->amount if some of it went
                // to an outstanding chargeback fee first.
                $remaining = $this->settleOutstandingFees($debit, $line);
                if ((float) $remaining > 0) {
                    LedgerEntry::create([
                        "membership_id" => $membership?->id,
                        "debit_id" => $debit->id,
                        "bank_statement_line_id" => $line->id,
                        "kind" => "payment",
                        "amount" => $remaining,
                        "channel" => $this->channelFor($debit, $membership),
                    ]);
                }

                if ($membership !== null) {
                    // Coverage only advances once it's actually paid for
                    // (design decision 1): a partial payment is credited to
                    // the balance above but must not by itself push
                    // end_date forward. previous_end_date is still
                    // snapshotted unconditionally below so a later
                    // Rücklastschrift's rollback is always a well-defined
                    // no-op when nothing actually advanced.
                    $previousEndDate = $membership->end_date->copy();
                    if ($membership->fresh()->ledgerBalance() <= 0) {
                        $months = Membership::MONTHS_PER_INTERVAL[$membership->interval];
                        $onTimeAdvance = $previousEndDate->copy()->addMonths($months);
                        $membership->end_date = $onTimeAdvance->greaterThanOrEqualTo($line->booked_at)
                            ? $onTimeAdvance
                            : $line->booked_at->copy()->addMonths($months);
                        $membership->save();
                    }

                    // Snapshotted so a later Rücklastschrift can roll this
                    // back exactly — see confirmChargeback() and the
                    // migration comment. Not "add one interval" in reverse:
                    // the resumption branch above means the advance isn't
                    // always one interval, so only the real prior value
                    // reverses it correctly.
                    $debit->update(["previous_end_date" => $previousEndDate]);
                }
            }
        }

        return true;
    }

    /**
     * A member who bounced a collection now owes the association a fee (see
     * confirmChargeback()) on top of whatever they were already being
     * collected for — and per an explicit decision, the next payment that
     * comes in under the same mandate settles that fee first, before any of
     * it counts toward the current debit. Otherwise the fee would just sit
     * on the balance forever unless collected as its own separate line, and
     * a chargeback fee must never look like it was paid toward (and so could
     * be summed into a receipt for) an actual charge or donation.
     *
     * Walks this mandate's "failed" (bounced) debits oldest-first, settling
     * each one's still-outstanding fee (outstandingFeeCents()) out of
     * $line->amount before anything is left for the debit actually being
     * confirmed. A fee-settling payment is tied to the *bounced* debit's own
     * id, not the one currently being collected — the same debit_id its
     * chargeback_fee entry already carries, which is what keeps it out of
     * Debit::netLedgerAmount() for the debit actually being paid, and
     * (structurally, since a "failed" debit is never receipt-eligible)
     * out of any donation receipt.
     *
     * @return string the decimal amount left over for $matchedDebit itself
     */
    private function settleOutstandingFees(Debit $matchedDebit, BankStatementLine $line): string
    {
        $remainingCents = (int) round((float) $line->amount * 100);

        $failedDebits = Debit::where("mandate", $matchedDebit->mandate)
            ->where("status", "failed")
            ->orderBy("due_date")
            ->get();

        foreach ($failedDebits as $failedDebit) {
            if ($remainingCents <= 0) {
                break;
            }

            $feeCents = $this->outstandingFeeCents($failedDebit);
            if ($feeCents <= 0) {
                continue;
            }

            $applied = min($feeCents, $remainingCents);
            LedgerEntry::create([
                "membership_id" => $failedDebit->membership_id,
                "debit_id" => $failedDebit->id,
                "bank_statement_line_id" => $line->id,
                "kind" => "payment",
                "amount" => number_format($applied / 100, 2, ".", ""),
                "channel" => $this->channelFor($failedDebit, $failedDebit->membership),
            ]);
            $remainingCents -= $applied;
        }

        return number_format($remainingCents / 100, 2, ".", "");
    }

    /**
     * A bounced debit's chargeback_fee entries (there is only ever one today
     * — see confirmChargeback() — but this sums rather than assumes that)
     * minus whatever payment entries have already settled it.
     */
    private function outstandingFeeCents(Debit $failedDebit): int
    {
        $feeCents = 0;
        $paidCents = 0;
        foreach ($failedDebit->ledgerEntries as $entry) {
            if ($entry->kind === "chargeback_fee") {
                $feeCents += (int) round($entry->amount * 100);
            } elseif ($entry->kind === "payment") {
                $paidCents += (int) round($entry->amount * 100);
            }
        }

        return max(0, $feeCents - $paidCents);
    }

    /**
     * How the money moved — $membership->payment_method when there's a
     * Membership to read it from, else derived from the debit's own bank
     * details: iban set means a SEPA collection (directdebit), null means a
     * banktransfer collection never had bank details to snapshot in the
     * first place. Same nullability convention DebitCreator's own docblock
     * already establishes.
     */
    private function channelFor(Debit $debit, ?Membership $membership): string
    {
        return $membership?->payment_method ?? ($debit->iban !== null ? "directdebit" : "banktransfer");
    }

    /**
     * Recognises a Rücklastschrift: BankStatementImporter routes a line here
     * instead of match() once it sees the bank's own "this was a return"
     * art value. Looks up the *original* debit among "executed" ones (only
     * something already collected can bounce), by end-to-end reference
     * first, then mandate — same cascade shape as matchByMandate(), against
     * the opposite status.
     *
     * @return bool whether the line was matched (and saved)
     */
    public function matchChargeback(BankStatementLine $line, ?string $mandate = null, ?string $endToEndReference = null): bool
    {
        $debit = null;
        if ($endToEndReference !== null) {
            $debit = Debit::where("end_to_end_reference", $endToEndReference)->where("status", "executed")->first();
        }

        if ($debit === null && $mandate !== null) {
            $debit = Debit::where("mandate", $mandate)->where("status", "executed")
                ->orderByDesc("due_date")->first();
        }

        if ($debit === null) {
            return false;
        }

        return $this->confirmChargeback($line, $debit);
    }

    /**
     * The chargeback counterpart to confirm(): flips the original Debit to
     * "failed", reverses the earlier "payment" entry and adds the bank's fee
     * as new debt — for a donation-sourced debit exactly as for a
     * membership-dues one, tied by debit_id alone when there's no
     * Membership.
     *
     * Guarded to "executed" only, same reasoning confirm() guards to
     * "pending" only: re-running this against an already-"failed" debit
     * (e.g. a re-import) must not double the reversal.
     *
     * The fee is computed, not parsed off the statement's free text: the
     * statement nets the original amount and however many fees applied into
     * one number (see docs/civicrm-replacement.md), and both operands here
     * — the line's own amount and the original debit's — are already exact.
     * A fee that doesn't come out positive is left unmatched for manual
     * triage rather than posting a nonsensical entry.
     */
    public function confirmChargeback(BankStatementLine $line, Debit $debit): bool
    {
        $debit = Debit::where("id", $debit->id)->where("status", "executed")->first();
        if ($debit === null) {
            return false;
        }

        $fee = round((float) $line->amount * -1 - (float) $debit->amount, 2);
        if ($fee <= 0) {
            return false;
        }

        $line->matched_type = "debit_reversal";
        $line->matched_id = $debit->id;
        $line->match_method = "mandate_reference";
        $line->matched_at = now();
        $line->save();

        $debit->update(["status" => "failed"]);

        $membership = $debit->membership;

        LedgerEntry::create([
            "membership_id" => $membership?->id,
            "debit_id" => $debit->id,
            "bank_statement_line_id" => $line->id,
            "kind" => "refund",
            "amount" => $debit->amount,
            "channel" => $this->channelFor($debit, $membership),
        ]);

        LedgerEntry::create([
            "membership_id" => $membership?->id,
            "debit_id" => $debit->id,
            "bank_statement_line_id" => $line->id,
            "kind" => "chargeback_fee",
            "amount" => $fee,
        ]);

        if ($membership !== null && $debit->previous_end_date !== null) {
            $membership->end_date = $debit->previous_end_date;
            $membership->save();
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
        // "submitted" too: once SepaDirectDebitBatchGenerator has sent a
        // debit out, it's still waiting on this same confirmation, just no
        // longer eligible for a fresh batch — see the assoc_debits status
        // comment.
        return $this->pendingDebits ??= Debit::whereIn("status", ["pending", "submitted"])->get();
    }

    private function activeRecurContributions(): Collection
    {
        return $this->activeRecurContributions ??= RecurContribution::where("active", true)->whereNotNull("mandate")->get();
    }
}
