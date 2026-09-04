<?php

namespace App\Assoc;

use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Debit;
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
 * Runs purely in shadow mode: it only ever writes matched_type/matched_id/
 * match_method/matched_at back onto the BankStatementLine itself. It never
 * touches Debit/RecurContribution status — unlike the CiviCRM original, which
 * flipped a Debit to "executed" the moment it matched. The point of this phase
 * is to see how well the cascade performs against real traffic before trusting
 * it to drive state, so a match here is a proposal for a human to confirm via
 * the admin UI, not an executed reconciliation.
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
                return $this->assign($line, "debit", $debit->id, "mandate_reference");
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
            return $this->assign($line, "debit", $debit->id, $method);
        }

        $recur = $this->activeRecurContributions()->firstWhere("mandate", $mandate);
        if ($recur !== null) {
            return $this->assign($line, "recur_contribution", $recur->id, $method);
        }

        return false;
    }

    private function assign(BankStatementLine $line, string $type, string $id, string $method): bool
    {
        $line->matched_type = $type;
        $line->matched_id = $id;
        $line->match_method = $method;
        $line->matched_at = now();
        $line->save();

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
