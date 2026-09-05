<?php

namespace App\Http\Controllers;

use App\Assoc\BankStatementMatcher;
use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Debit;
use App\Models\Assoc\RecurContribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bank-statement matching triage — see docs/civicrm-replacement.md phase 4/6.
 * Unlike AssocController this does write: it records a human's manual match
 * decision, and lets one re-run the automatic cascade. A manual match of a
 * normal payment line goes through BankStatementMatcher::confirm(), same as
 * the automatic cascade, so matching a line to a debit flips that debit to
 * "executed" either way. A chargeback line (BankStatementLine::isChargeback())
 * instead goes through confirmChargeback() against an "executed" Debit — the
 * automatic path (matchChargeback(), triggered from BankStatementImporter)
 * only finds one by end-to-end reference or mandate, and leaves the line
 * unmatched for exactly this triage when that lookup fails (a re-used mandate
 * across several executed debits, a missing/garbled reference, etc.).
 */
class BankStatementController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query("status", "unmatched");
        $query = BankStatementLine::query()->orderByDesc("booked_at");
        if ($status === "unmatched") {
            $query->whereNull("matched_type");
        } elseif ($status === "matched") {
            $query->whereNotNull("matched_type");
        }

        $lines = $query->paginate(50)->withQueryString();

        return response(view("admin.assoc.bank_statements", [
            "title" => "Geldeingänge",
            "lines" => $lines,
            "status" => $status,
        ]));
    }

    public function show(Request $request, string $id): Response
    {
        $line = BankStatementLine::whereNull("matched_type")->findOrFail($id);

        $search = trim((string) $request->query("q", ""));
        $candidates = collect();
        if ($search !== "") {
            $candidates = $line->isChargeback()
                ? $this->searchChargebackCandidates($search, $line)
                : $this->searchCandidates($search);
        }

        return response(view("admin.assoc.bank_statement", [
            "title" => $line->isChargeback() ? "Rücklastschrift zuordnen" : "Geldeingang zuordnen",
            "line" => $line,
            "search" => $search,
            "candidates" => $candidates,
        ]));
    }

    public function match(Request $request, string $id, BankStatementMatcher $matcher): RedirectResponse
    {
        $line = BankStatementLine::whereNull("matched_type")->findOrFail($id);

        $type = $request->input("type");
        abort_unless(in_array($type, ["debit", "recur_contribution"], true), 422);

        if ($line->isChargeback()) {
            // A chargeback reverses a specific collection attempt, never a
            // standing recurring authorization — there is nothing on a
            // RecurContribution for confirmChargeback() to flip.
            abort_unless($type === "debit", 422);
            $debit = Debit::findOrFail($request->input("target_id"));
            // False means the debit wasn't actually "executed" anymore, or the
            // line's amount doesn't come out to a positive fee against it —
            // surfaced as a rejected request rather than a silent no-op
            // redirect, since a manually-picked target is a real assertion by
            // the admin that this is correct.
            abort_unless($matcher->confirmChargeback($line, $debit), 422);

            return redirect(route("assoc_admin_bank_statements"));
        }

        $target = $type === "debit"
            ? Debit::findOrFail($request->input("target_id"))
            : RecurContribution::findOrFail($request->input("target_id"));

        $matcher->confirm($line, $type, $target->id, "manual", Auth::user()?->email ?? "admin");

        return redirect(route("assoc_admin_bank_statements"));
    }

    public function rematch(BankStatementMatcher $matcher): RedirectResponse
    {
        $matcher->rematchUnresolved();

        return redirect(route("assoc_admin_bank_statements"));
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{type: string, model: Debit|RecurContribution}>
     */
    private function searchCandidates(string $search): \Illuminate\Support\Collection
    {
        $debits = Debit::where("status", "pending")
            ->where(function ($q) use ($search) {
                $q->where("account_holder", "like", "%{$search}%")->orWhere("mandate", "like", "%{$search}%");
            })
            ->orderBy("due_date")
            ->limit(10)
            ->get()
            ->map(fn (Debit $d) => ["type" => "debit", "model" => $d]);

        $recurs = RecurContribution::where("active", true)
            ->where(function ($q) use ($search) {
                $q->where("account_holder", "like", "%{$search}%")->orWhere("mandate", "like", "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn (RecurContribution $r) => ["type" => "recur_contribution", "model" => $r]);

        return $debits->concat($recurs);
    }

    /**
     * Candidates for a chargeback line: "executed" debits (only something
     * already collected can bounce), the same status matchChargeback() itself
     * requires. Carries a "fee" alongside each — the same computation
     * confirmChargeback() will actually use (abs(line amount) - debit
     * amount) — so an admin can tell at a glance which candidate produces a
     * sane (small, positive) bank fee rather than guessing blind.
     *
     * @return \Illuminate\Support\Collection<int, array{type: string, model: Debit, fee: float}>
     */
    private function searchChargebackCandidates(string $search, BankStatementLine $line): \Illuminate\Support\Collection
    {
        return Debit::where("status", "executed")
            ->where(function ($q) use ($search) {
                $q->where("account_holder", "like", "%{$search}%")->orWhere("mandate", "like", "%{$search}%");
            })
            ->orderByDesc("due_date")
            ->limit(10)
            ->get()
            ->map(fn (Debit $d) => [
                "type" => "debit",
                "model" => $d,
                "fee" => round(abs((float) $line->amount) - (float) $d->amount, 2),
            ]);
    }
}
