<?php

namespace App\Http\Controllers;

use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The manual ledger-adjustment actions from the payment-ledger design pass
 * (see docs/civicrm-replacement.md): a waiver (admin write-off on an
 * accepted late cancellation) and a refund. Both are record-keeping only —
 * a refund here means "this amount went back out", not itself moving money;
 * the actual outgoing transfer still happens by hand until phase 6c's SEPA
 * generation (and, per the design doc, the Hibiscus Payment-Server) exists.
 *
 * A membership's action (store()) adjusts its ongoing balance. A donation's
 * (storeForDebit()) is scoped to one specific Debit instead — "refund/waive
 * this one donation", not an adjustment to some running per-donor balance,
 * since a donation debit is its own event with no equivalent "coverage" to
 * carry a balance for.
 */
class LedgerEntryController extends Controller
{
    public function store(Request $request, string $id): RedirectResponse
    {
        $membership = Membership::findOrFail($id);
        [$kind, $amount, $channel] = $this->validated($request);

        LedgerEntry::create([
            "membership_id" => $membership->id,
            "kind" => $kind,
            "amount" => $amount,
            "channel" => $channel,
        ]);

        return redirect()->back();
    }

    public function storeForDebit(Request $request, string $id): RedirectResponse
    {
        $debit = Debit::findOrFail($id);
        [$kind, $amount, $channel] = $this->validated($request);

        LedgerEntry::create([
            "membership_id" => $debit->membership_id,
            "debit_id" => $debit->id,
            "kind" => $kind,
            "amount" => $amount,
            "channel" => $channel,
        ]);

        return redirect()->back();
    }

    /**
     * @return array{0: string, 1: string, 2: string|null}
     */
    private function validated(Request $request): array
    {
        $kind = $request->input("kind");
        abort_unless(in_array($kind, ["waiver", "refund"], true), 422);

        $amount = filter_var($request->input("amount"), FILTER_VALIDATE_FLOAT);
        abort_if($amount === false || $amount <= 0, 422);

        // Only a refund carries a channel: money paid down by a payment has
        // to go back out the same way it arrived (see LedgerEntry's
        // migration comment on "channel"); a waiver isn't a transfer of
        // money at all, so any channel submitted alongside one is ignored.
        $channel = null;
        if ($kind === "refund") {
            $channel = $request->input("channel");
            abort_unless(in_array($channel, ["sepa_credit_transfer", "paypal"], true), 422);
        }

        return [$kind, number_format($amount, 2, ".", ""), $channel];
    }
}
