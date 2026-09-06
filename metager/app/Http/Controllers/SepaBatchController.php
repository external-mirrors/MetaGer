<?php

namespace App\Http\Controllers;

use App\Assoc\SepaDirectDebitBatchGenerator;
use App\Models\Assoc\Debit;
use App\Models\Assoc\SepaBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin surface for the pain.008 half of phase 6c (see
 * docs/civicrm-replacement.md) — generating a SEPA direct-debit collection
 * batch from every currently "pending" directdebit Debit and downloading the
 * resulting XML to hand to the bank/Hibiscus by hand. Confirmation still
 * flows through the existing BankStatementController/BankStatementMatcher
 * once a bank statement reflects it — this controller never touches that.
 */
class SepaBatchController extends Controller
{
    public function index(): Response
    {
        $eligibleCount = Debit::where("status", "pending")->whereNotNull("iban")->count();
        $eligibleTotal = Debit::where("status", "pending")->whereNotNull("iban")->sum("amount");

        $batches = SepaBatch::query()
            ->orderByDesc("generated_at")
            ->paginate(50)
            ->withQueryString();

        return response(view("admin.assoc.sepa_batches", [
            "title" => "SEPA-Sammellastschriften",
            "eligibleCount" => $eligibleCount,
            "eligibleTotal" => $eligibleTotal,
            "batches" => $batches,
        ]));
    }

    public function generate(SepaDirectDebitBatchGenerator $generator): RedirectResponse
    {
        $generator->generate();

        return redirect()->back();
    }

    public function download(string $id): StreamedResponse
    {
        $batch = SepaBatch::findOrFail($id);

        return Storage::disk("local")->download($batch->xml_path, "{$batch->message_id}.xml");
    }
}
