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
 * decision, and lets one re-run the automatic cascade. A manual match goes
 * through BankStatementMatcher::confirm(), same as the automatic cascade, so
 * matching a line to a debit flips that debit to "executed" either way.
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
        $candidates = $search !== "" ? $this->searchCandidates($search) : collect();

        return response(view("admin.assoc.bank_statement", [
            "title" => "Geldeingang zuordnen",
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
}
