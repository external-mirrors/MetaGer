<?php

namespace App\Assoc;

use App\Models\Assoc\Debit;
use App\Models\Assoc\DonationReceipt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Turns executed, not-yet-receipted assoc_debits rows into
 * assoc_donation_receipts — the Zuwendungsbestätigung/Beitragsbescheinigung a
 * payer is legally owed for tax purposes, ported from
 * Bescheinigungen/Spendenbescheinigung.php's eligibility rules.
 *
 * The original checked a preference on the Contribution first, falling back
 * to one on the Contact (Bescheinigungen.Spende_bescheinigen for donations,
 * Mitgliedsbeitrag_bescheinigen for dues — two independent settings), and
 * generated no receipt at all when neither was set. This schema collapses
 * that to one donation_receipt_preference per payer (see
 * CiviCrmImporter::importContacts() for how an existing contact where the two
 * disagreed is resolved) with no per-contribution override, plus
 * config('assoc.donation_receipt_default_preference') for a payer with no
 * preference of their own at all — so "nothing configured" now means
 * something, instead of silently never receipting.
 *
 * A receipt never mixes assoc_debits.source values (donation vs membership
 * dues), matching the German Zuwendungsbestätigung's distinct legally
 * required wording for each — see Spendenbescheinigung.php's separate
 * "zuwendungen"/"beitragsbescheinigungen" template paths.
 */
class DonationReceiptGenerator
{
    public function __construct(private readonly DonationReceiptPdf $pdf)
    {
    }

    /**
     * Explicit, on-demand generation for one debit. Bypasses the payer's
     * preference entirely — an admin choosing to generate a receipt right
     * now is itself the decision every preference check exists to make.
     */
    public function generateSingle(Debit $debit): DonationReceipt
    {
        if ($debit->status !== "executed") {
            throw new \RuntimeException("Cannot receipt debit {$debit->id}: not executed yet.");
        }
        if ($debit->donation_receipt_id !== null) {
            throw new \RuntimeException("Debit {$debit->id} already belongs to a donation receipt.");
        }

        return $this->createReceipt(collect([$debit]), $debit->due_date->year);
    }

    /**
     * Every executed, unreceipted debit whose effective preference is
     * "immediate" — one receipt per debit, meant to run right after a debit
     * is confirmed executed rather than batched.
     *
     * @return Collection<int, DonationReceipt>
     */
    public function generateImmediate(): Collection
    {
        $receipts = collect();

        foreach ($this->eligibleDebits() as $debit) {
            if ($this->effectivePreference($debit) !== "immediate") {
                continue;
            }
            $receipts->push($this->createReceipt(collect([$debit]), $debit->due_date->year));
        }

        return $receipts;
    }

    /**
     * One receipt per payer+source covering every executed, unreceipted
     * debit due in $year or earlier, for payers whose effective preference
     * is "annual" — the catch-up CiviCRM's "Jährlich" preference performed
     * for anything from before the first of January.
     *
     * @return Collection<int, DonationReceipt>
     */
    public function generateAnnualBatch(int $year): Collection
    {
        $groups = [];

        foreach ($this->eligibleDebits() as $debit) {
            if ($this->effectivePreference($debit) !== "annual") {
                continue;
            }
            if ($debit->due_date->year > $year) {
                continue;
            }
            $groups[$this->payerKey($debit) . "|" . $debit->source][] = $debit;
        }

        return collect($groups)->map(fn (array $debits) => $this->createReceipt(collect($debits), $year))->values();
    }

    /**
     * @return Collection<int, Debit>
     */
    private function eligibleDebits(): Collection
    {
        return Debit::where("status", "executed")
            ->whereNull("donation_receipt_id")
            ->with(["contact", "company", "household"])
            ->get();
    }

    private function effectivePreference(Debit $debit): string
    {
        return $debit->payer()?->effectiveDonationReceiptPreference()
            ?? config("assoc.donation_receipt_default_preference");
    }

    private function payerKey(Debit $debit): string
    {
        return $debit->contact_id ?? $debit->company_id ?? $debit->household_id;
    }

    /**
     * @param Collection<int, Debit> $debits
     */
    private function createReceipt(Collection $debits, int $year): DonationReceipt
    {
        $first = $debits->first();
        if ($debits->contains(fn (Debit $debit) => $debit->source !== $first->source)) {
            throw new \InvalidArgumentException("A donation receipt cannot mix membership and donation debits.");
        }

        $receipt = DonationReceipt::create([
            "contact_id" => $first->contact_id,
            "company_id" => $first->company_id,
            "household_id" => $first->household_id,
            "year" => $year,
            "total_amount" => $this->sumAmounts($debits),
            "source" => $first->source,
            "generated_at" => now(),
        ]);

        $path = "assoc/donation-receipts/{$receipt->id}.pdf";
        Storage::disk("local")->put($path, $this->pdf->render($receipt, $debits));
        $receipt->update(["pdf_path" => $path]);

        Debit::whereIn("id", $debits->pluck("id"))->update(["donation_receipt_id" => $receipt->id]);

        return $receipt->refresh();
    }

    /**
     * Adds decimal:2 amount strings in integer cents — bcmath isn't
     * installed in the fpm image, and summing as float risks the exact
     * round-trip issue CLAUDE.md's decimal:2 cast rule exists to avoid.
     *
     * @param Collection<int, Debit> $debits
     */
    private function sumAmounts(Collection $debits): string
    {
        $cents = $debits->sum(function (Debit $debit) {
            [$whole, $fraction] = explode(".", (string) $debit->amount);
            return ((int) $whole) * 100 + (int) $fraction;
        });

        return sprintf("%d.%02d", intdiv($cents, 100), $cents % 100);
    }
}
