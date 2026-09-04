<?php

namespace App\Http\Controllers;

use App\Assoc\DonationReceiptGenerator;
use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\DonationReceipt;
use App\Models\Assoc\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin surface for phase 5 — see docs/civicrm-replacement.md. Unlike
 * AssocController this writes: generating a receipt links it to the debits
 * it covers and stores a PDF. It never changes an assoc_debits.status —
 * "executed" still only ever comes from CiviCrmImporter, same as phase 4.
 */
class DonationReceiptController extends Controller
{
    public function index(Request $request): Response
    {
        $receipts = DonationReceipt::query()
            ->orderByDesc("generated_at")
            ->paginate(50)
            ->withQueryString();

        return response(view("admin.assoc.donation_receipts", [
            "title" => "Zuwendungsbestätigungen",
            "receipts" => $receipts,
        ]));
    }

    public function download(string $id): StreamedResponse
    {
        $receipt = DonationReceipt::whereNotNull("pdf_path")->findOrFail($id);

        return Storage::disk("local")->download($receipt->pdf_path, "{$receipt->sourceLabel()}-{$receipt->year}.pdf");
    }

    public function generate(Request $request, DonationReceiptGenerator $generator, string $debitId): RedirectResponse
    {
        $debit = Debit::findOrFail($debitId);

        try {
            $generator->generateSingle($debit);
        } catch (\RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * All outstanding debits of one source for one payer, folded into a
     * single receipt — see DonationReceiptGenerator::generateForPayer().
     */
    public function generateForPayer(Request $request, DonationReceiptGenerator $generator, string $type, string $id): RedirectResponse
    {
        $source = $request->input("source");
        abort_unless(in_array($source, ["donation", "membership"], true), 422);

        $generator->generateForPayer($this->findPayer($type, $id), $source);

        return redirect()->back();
    }

    public function updatePreference(Request $request, string $type, string $id): RedirectResponse
    {
        // Global ConvertEmptyStringsToNull middleware turns the "no
        // override" option's "" value into null before this runs, so null
        // is the actual clear-the-preference sentinel, not "".
        $preference = $request->input("donation_receipt_preference");
        abort_unless(in_array($preference, [null, "never", "immediate", "annual"], true), 422);

        $this->findPayer($type, $id)->update(["donation_receipt_preference" => $preference]);

        return redirect()->back();
    }

    private function findPayer(string $type, string $id): Contact|Company|Household
    {
        abort_unless(in_array($type, ["contact", "company", "household"], true), 404);

        return match ($type) {
            "contact" => Contact::findOrFail($id),
            "company" => Company::findOrFail($id),
            "household" => Household::findOrFail($id),
        };
    }
}
