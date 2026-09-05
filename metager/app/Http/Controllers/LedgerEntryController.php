<?php

namespace App\Http\Controllers;

use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The two manual ledger-adjustment actions from the payment-ledger design
 * pass (see docs/civicrm-replacement.md): a waiver (admin write-off on an
 * accepted late cancellation) and a refund. Both are record-keeping only —
 * a refund here means "this amount went back out", not itself moving money;
 * the actual outgoing transfer still happens by hand until phase 6c's SEPA
 * generation (and, per the design doc, the Hibiscus Payment-Server) exists.
 */
class LedgerEntryController extends Controller
{
    public function store(Request $request, string $id): RedirectResponse
    {
        $membership = Membership::findOrFail($id);

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

        LedgerEntry::create([
            "membership_id" => $membership->id,
            "kind" => $kind,
            "amount" => number_format($amount, 2, ".", ""),
            "channel" => $channel,
        ]);

        return redirect()->back();
    }
}
