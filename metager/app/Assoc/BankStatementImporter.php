<?php

namespace App\Assoc;

use App\Models\Assoc\BankStatementLine;

/**
 * Parses a Hibiscus "Umsätze exportieren" XML export — the same format
 * de.suma-ev.bescheinigungen's FetchBankAccount::postProcess() read — into
 * assoc_bank_statement_lines, then hands each new line to BankStatementMatcher.
 *
 * Differences from the CiviCRM original, both deliberate:
 *  - No account allowlist by default. The original hardcoded konto_id 1/2 (its
 *    two Volksbank accounts) because it also asserted both were present in
 *    every upload; that assertion doesn't fit a shadow-mode validation pass
 *    against arbitrary exports, so filtering is opt-in via $accountIds.
 *  - No date-watermark ("bankaccount_update_date"). The original tracked the
 *    last-imported date in a CiviCRM setting and trusted every upload to only
 *    contain newer rows. Row-level deduplication (iban+amount+reference+
 *    booked_at) is used instead — it tolerates re-uploading a file that
 *    overlaps a previous one, which the watermark approach could not.
 *
 * Hibiscus's export includes a payer IBAN per row (assoc_bank_statement_lines.
 * iban expects one — see the migration), which FetchBankAccount.php never
 * read because matching there never used it. The exact tag Hibiscus uses
 * isn't nailed down against a real export in this pass, so several plausible
 * names are tried; BankStatementImporterTest pins whichever the first real
 * import confirms.
 *
 * A Rücklastschrift (chargeback) arrives as its own negative line, `art`
 * "Retourenbelastung" — confirmed against one real export
 * (docs/civicrm-replacement.md), open to another value showing up later the
 * same way IBAN_FIELDS is. It's routed to BankStatementMatcher::matchChargeback()
 * instead of match(), ahead of the normal "skip non-positive amounts" check.
 *
 * Hibiscus wraps a long purpose text across zweck/zweck2/zweck3, sometimes
 * mid-word or mid-number, with no guaranteed document order — a real
 * Rücklastschrift line split "ORG.BETR.: 12" / "0,00 EUR" across two of
 * them is what surfaced this. Reading only `zweck` (as before) silently
 * truncated any line long enough to wrap, chargeback or not.
 */
class BankStatementImporter
{
    private const IBAN_FIELDS = ["empfaenger_iban", "gegenkonto_iban", "iban"];

    private const CHARGEBACK_ART_VALUES = ["Retourenbelastung"];

    public function __construct(private BankStatementMatcher $matcher)
    {
    }

    /**
     * @param int[] $accountIds Hibiscus konto_id values to accept; empty means accept all.
     * @return array{created: int, duplicates: int, skipped_outgoing: int, skipped_account: int, skipped_invalid: int, matched: array<string,int>, unmatched: int, chargebacks: array{matched: int, unmatched: int}}
     */
    public function importHibiscusXml(string $path, array $accountIds = []): array
    {
        $xml = simplexml_load_file($path);
        if ($xml === false) {
            throw new \RuntimeException("Die Datei beinhaltet kein gültiges XML.");
        }

        $summary = [
            "created" => 0,
            "duplicates" => 0,
            "skipped_outgoing" => 0,
            "skipped_account" => 0,
            "skipped_invalid" => 0,
            "matched" => ["mandate_reference" => 0, "regex" => 0, "substring" => 0],
            "unmatched" => 0,
            "chargebacks" => ["matched" => 0, "unmatched" => 0],
        ];

        foreach ($xml->object as $payment) {
            $accountId = filter_var((string) $payment->konto_id, FILTER_VALIDATE_INT);
            if ($accountIds !== [] && !in_array($accountId, $accountIds, true)) {
                $summary["skipped_account"]++;
                continue;
            }

            $isChargeback = in_array(trim((string) ($payment->art ?? "")), self::CHARGEBACK_ART_VALUES, true);

            $amount = filter_var((string) $payment->betrag, FILTER_VALIDATE_FLOAT);
            // Only incoming money is reconciled here, same as the original —
            // outgoing payments (refunds, admin transfers) have no counterpart
            // in assoc_debits/assoc_recur_contributions to match against. A
            // Rücklastschrift is also a negative line but is exactly the one
            // outgoing case that does have a counterpart (the Debit it
            // reverses), so a negative amount is let through when it's one.
            if ($amount === false || ($isChargeback ? $amount >= 0 : $amount <= 0)) {
                $summary["skipped_outgoing"]++;
                continue;
            }

            $bookedAt = \DateTime::createFromFormat("d.m.Y H:i:s", (string) $payment->valuta);
            if ($bookedAt === false) {
                $summary["skipped_invalid"]++;
                continue;
            }

            $iban = $this->extractIban($payment);
            $reference = str_replace("\n", "", (string) $payment->zweck . (string) ($payment->zweck2 ?? "") . (string) ($payment->zweck3 ?? ""));
            $reference = trim($reference);
            $reference = $reference !== "" ? $reference : null;

            $mandate = null;
            if (isset($payment->mandateid)) {
                $mandate = trim((string) $payment->mandateid) ?: null;
            }

            $endToEndReference = null;
            if (isset($payment->endtoendid)) {
                $raw = trim((string) $payment->endtoendid);
                $endToEndReference = ($raw !== "" && $raw !== "NOTPROVIDED") ? $raw : null;
            }

            $amountString = number_format($amount, 2, ".", "");
            $bookedAtString = $bookedAt->format("Y-m-d");

            // whereDate, not where(): "booked_at" is a `date` cast, but Eloquent
            // still persists it using the model's Y-m-d H:i:s date format, so a
            // raw string comparison against "Y-m-d" here would never match.
            $duplicate = BankStatementLine::where("iban", $iban)
                ->where("amount", $amountString)
                ->where("reference", $reference)
                ->whereDate("booked_at", $bookedAtString)
                ->exists();
            if ($duplicate) {
                $summary["duplicates"]++;
                continue;
            }

            $line = BankStatementLine::create([
                "iban" => $iban,
                "amount" => $amountString,
                "reference" => $reference,
                "booked_at" => $bookedAtString,
            ]);
            $summary["created"]++;

            if ($isChargeback) {
                if ($this->matcher->matchChargeback($line, $mandate, $endToEndReference)) {
                    $summary["chargebacks"]["matched"]++;
                } else {
                    $summary["chargebacks"]["unmatched"]++;
                }
            } elseif ($this->matcher->match($line, $mandate, $endToEndReference)) {
                $summary["matched"][$line->match_method]++;
            } else {
                $summary["unmatched"]++;
            }
        }

        return $summary;
    }

    private function extractIban(\SimpleXMLElement $payment): string
    {
        foreach (self::IBAN_FIELDS as $field) {
            if (isset($payment->$field)) {
                $value = trim((string) $payment->$field);
                if ($value !== "") {
                    return $value;
                }
            }
        }

        return "";
    }
}
