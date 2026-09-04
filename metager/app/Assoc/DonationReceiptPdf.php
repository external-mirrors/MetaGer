<?php

namespace App\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\DonationReceipt;
use App\Models\Assoc\Household;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Renders a DonationReceipt to a PDF via mpdf and the
 * resources/views/assoc/donation_receipt_pdf blade view — the same library
 * and broadly the same legally-required text Bescheinigungen/
 * Spendenbescheinigung.php used (its Smarty templates, ported to Blade).
 *
 * Deliberately does not port the original's thank-you letter, embedded
 * suma-ev/MetaGer logos or the multi-page layout for >2 line items — none of
 * that carries legal weight, so it was left for a later pass rather than
 * blocking this one. What's here is the certificate itself, which does.
 *
 * The signee name and scanned signature image are read from
 * config('assoc.donation_receipt_signee_name'/'_signature_path') rather than
 * committed to the codebase — the original hardcoded two board members'
 * names and JPEG signature scans directly into extension source
 * (CRM/Bescheinigungen/Form/DownloadReceipts.php), which both bakes a
 * personal signature image into version control and means a change of
 * signee needs a deploy. A missing/unset path just prints no signature
 * image and lets the receipt be signed by hand.
 */
class DonationReceiptPdf
{
    /**
     * @param Collection<int, Debit> $debits
     */
    public function render(DonationReceipt $receipt, Collection $debits): string
    {
        $mpdf = new Mpdf([
            "margin_left" => 25,
            "margin_right" => 20,
            "margin_top" => 28,
            "margin_bottom" => 25,
        ]);

        $payer = $receipt->payer();

        $html = View::make("assoc.donation_receipt_pdf", [
            "sourceLabel" => $receipt->sourceLabel(),
            "isDonation" => $receipt->source === "donation",
            "payerName" => $this->payerName($payer),
            "payerStreet" => $payer?->street,
            "payerPostalCode" => $payer?->postal_code,
            "payerCity" => $payer?->city,
            "lines" => $debits->map(fn (Debit $debit) => $this->line($debit)),
            "date" => now()->format("d.m.Y"),
            "signeeName" => config("assoc.donation_receipt_signee_name"),
            "signatureDataUri" => $this->signatureDataUri(),
        ])->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output("", Destination::STRING_RETURN);
    }

    /**
     * @return array{amount: string, amountWords: string, date: string}
     */
    private function line(Debit $debit): array
    {
        [$whole, $cents] = explode(".", (string) $debit->amount);

        $words = NumberToGermanWords::convert((int) $whole) . " EURO";
        if ((int) $cents > 0) {
            $words .= " UND " . NumberToGermanWords::convert((int) $cents) . " CENT";
        }

        return [
            "amount" => number_format($debit->amount, 2, ",", "."),
            "amountWords" => mb_strtoupper($words),
            "date" => $debit->due_date->format("d.m.Y"),
        ];
    }

    private function payerName(Contact|Company|Household|null $payer): string
    {
        return match (true) {
            $payer instanceof Contact => trim("{$payer->first_name} {$payer->last_name}"),
            $payer instanceof Company => $payer->name,
            $payer instanceof Household => $payer->household_name,
            default => "",
        };
    }

    private function signatureDataUri(): ?string
    {
        $path = config("assoc.donation_receipt_signature_path");
        if (!$path || !is_file($path)) {
            return null;
        }

        $mime = str_ends_with(strtolower($path), ".png") ? "image/png" : "image/jpeg";

        return "data:{$mime};base64," . base64_encode(file_get_contents($path));
    }
}
