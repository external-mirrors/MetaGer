<?php

namespace App\Assoc;

use App\Models\Assoc\Debit;
use App\Models\Assoc\SepaBatch;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\Facade\CustomerDirectDebitFacade;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ported from de.suma-ev.donation-debit's
 * CRM_DonationDebit_Form_ExecuteDebits::generateSepaXML()/createPaymentInfos()
 * — every currently "pending" directdebit Debit, folded into one
 * pain.008.001.02 SEPA collection batch via digitick/sepa-xml, the same
 * library the legacy extension used.
 *
 * This is the pain.008-generation half of phase 6c only (see
 * docs/civicrm-replacement.md) — handing the resulting file to the bank or
 * the Hibiscus Payment-Server still happens by hand today, same as CiviCRM's
 * admin always did; confirmation that a submitted debit was actually
 * collected still flows through the existing BankStatementMatcher once a
 * bank statement reflects it.
 *
 * Two things the legacy code did are deliberately not reproduced — this is a
 * port, not a byte-for-byte translation, and neither one is a preserved
 * quirk worth a characterization test the way ResultDeduplicator's/
 * LinkBuilder's are:
 * - Legacy called addPaymentInfo() for all three payment-name variants
 *   (-onetime/-first/-recurring) every time the collection date changed,
 *   regardless of which ones actually got a transfer that day.
 *   digitick/sepa-xml's asXML() emits a <PmtInf> block for every group ever
 *   registered with no transfer-count check (BaseCustomerTransferFileFacade::
 *   asXML() + BaseTransferFile::accept()), so most runs would have produced
 *   one or two empty, schema-invalid <PmtInf> blocks. This generator creates
 *   a payment-info group lazily, the moment its first transfer needs it.
 * - Every group's id (-> PmtInfId) was the literal string 'firstPayment',
 *   repeated across every group in the file. Groups here get a unique id
 *   derived from the batch's own message id and position instead.
 *
 * Also dropped: the "-onetime"/OOFF case, which only ever applied to a debit
 * with neither a recur_contribution_id nor a membership_id — a SEPA
 * collection with no mandate history at all. Every Debit DebitCreator
 * creates comes from either a Membership or a RecurContribution, so that
 * case cannot occur in a collection batch; this is not a claim that one-off
 * SEPA messages never happen anywhere in this system — a manual refund's
 * eventual outgoing transfer (LedgerEntryController::storeForDebit()'s
 * "refund"/"sepa_credit_transfer" kind) will be a one-off pain.001 SEPA
 * Credit Transfer once that's wired to Hibiscus, a different message format
 * needing its own generator, not built here.
 */
class SepaDirectDebitBatchGenerator
{
    /** Legacy's own minimum lead time for a mandate's first-ever collection. */
    private const FIRST_MIN_WEEKDAYS = 5;

    /** Legacy's own minimum lead time for a recurring collection. */
    private const RECURRING_MIN_WEEKDAYS = 2;

    /**
     * @return SepaBatch|null null when there was nothing eligible to batch —
     *   no batch row, no file, same as legacy's form simply showing nothing
     *   to submit.
     */
    public function generate(): ?SepaBatch
    {
        $debits = Debit::where("status", "pending")
            ->whereNotNull("iban")
            ->orderBy("due_date")
            ->get();

        if ($debits->isEmpty()) {
            return null;
        }

        $creditor = $this->creditorConfig();

        $messageId = "SEPA-" . Carbon::now()->format("YmdHis") . "-" . Str::random(6);
        $groupHeader = new GroupHeader($messageId, $creditor["name"]);
        $file = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($groupHeader, "pain.008.001.02");

        $groupNames = [];
        $totalCents = 0;

        foreach ($debits as $debit) {
            $seqType = $this->isRecurring($debit) ? PaymentInformation::S_RECURRING : PaymentInformation::S_FIRST;
            $minWeekdays = $seqType === PaymentInformation::S_RECURRING ? self::RECURRING_MIN_WEEKDAYS : self::FIRST_MIN_WEEKDAYS;
            $minDate = Carbon::today()->modify("+{$minWeekdays} weekdays");
            $collectionDate = $debit->due_date->greaterThanOrEqualTo($minDate) ? $debit->due_date->copy() : $minDate;

            $groupKey = $collectionDate->format("Y-m-d") . "|" . $seqType;
            if (!isset($groupNames[$groupKey])) {
                $paymentName = $groupKey;
                $file->addPaymentInfo($paymentName, [
                    "id" => "PMT-{$messageId}-" . count($groupNames),
                    "dueDate" => $collectionDate->format("Y-m-d"),
                    "creditorName" => $creditor["name"],
                    "creditorAccountIBAN" => $creditor["iban"],
                    "creditorAgentBIC" => $creditor["bic"],
                    "seqType" => $seqType,
                    "creditorId" => $creditor["id"],
                    "localInstrumentCode" => "CORE",
                ]);
                $groupNames[$groupKey] = $paymentName;
            }

            $file->addTransfer($groupNames[$groupKey], [
                "amount" => (int) round((float) $debit->amount * 100),
                "debtorIban" => $debit->iban,
                "debtorBic" => $debit->bic,
                "debtorName" => $debit->account_holder,
                "debtorMandate" => $debit->mandate,
                "debtorMandateSignDate" => $debit->mandate_date,
                "remittanceInformation" => $debit->reference ?? $debit->end_to_end_reference,
                "endToEndId" => $debit->end_to_end_reference,
            ]);

            $totalCents += (int) round((float) $debit->amount * 100);
        }

        $xml = $file->asXML();
        $path = "assoc/sepa-batches/{$messageId}.xml";
        Storage::disk("local")->put($path, $xml);

        $batch = SepaBatch::create([
            "message_id" => $messageId,
            "generated_at" => now(),
            "debit_count" => $debits->count(),
            "total_amount" => number_format($totalCents / 100, 2, ".", ""),
            "xml_path" => $path,
        ]);

        Debit::whereIn("id", $debits->pluck("id"))->update([
            "status" => "submitted",
            "sepa_batch_id" => $batch->id,
        ]);

        return $batch->refresh();
    }

    /**
     * Whether this mandate already has collection history — FRST vs RCUR.
     * Excludes "failed" (bounced/reversed) debits from that history, same as
     * legacy excluded its "chargeback" status: if the only prior attempt
     * under this mandate bounced, nothing was ever successfully collected
     * under it, so the next attempt is still a first, not a recurring, one.
     * Counting includes $debit itself (it's already a real row, still
     * "pending" at this point), so ">1" means "at least one other one
     * exists" — matching legacy's own off-by-one-looking but correct check.
     */
    private function isRecurring(Debit $debit): bool
    {
        return Debit::where("mandate", $debit->mandate)->where("status", "!=", "failed")->count() > 1;
    }

    /**
     * @return array{name: string, iban: string, bic: string, id: string}
     */
    private function creditorConfig(): array
    {
        $config = [
            "name" => config("assoc.sepa_creditor_name"),
            "iban" => config("assoc.sepa_creditor_iban"),
            "bic" => config("assoc.sepa_creditor_bic"),
            "id" => config("assoc.sepa_creditor_id"),
        ];

        foreach ($config as $key => $value) {
            if ($value === null || $value === "") {
                throw new \RuntimeException("assoc.sepa_creditor_{$key} is not configured — see config/assoc.php.");
            }
        }

        return $config;
    }
}
