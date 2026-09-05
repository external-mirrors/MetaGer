<?php

namespace Tests\Unit\Assoc;

use App\Assoc\BankStatementImporter;
use App\Assoc\BankStatementMatcher;
use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Membership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * Reads the same Hibiscus "Umsätze exportieren" XML shape
 * de.suma-ev.bescheinigungen's FetchBankAccount::postProcess() parsed
 * (<object> per row: konto_id, empfaenger_name, betrag, valuta, zweck,
 * mandateid, endtoendid) — see BankStatementImporter's docblock for what's
 * deliberately different (no account/date-watermark assumptions).
 */
class BankStatementImporterTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    private function xmlFile(string $objectsXml): string
    {
        $path = tempnam(sys_get_temp_dir(), "hibiscus") . ".xml";
        file_put_contents($path, "<container>{$objectsXml}</container>");
        $this->tempFiles[] = $path;

        return $path;
    }

    private function importer(): BankStatementImporter
    {
        return new BankStatementImporter(new BankStatementMatcher());
    }

    public function testImportsAnIncomingPaymentAsAnUnmatchedLine(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <empfaenger_name>Ada Lovelace</empfaenger_name>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>10.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Spende, vielen Dank</zweck>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(1, $summary["created"]);
        $this->assertSame(1, $summary["unmatched"]);
        $line = BankStatementLine::sole();
        $this->assertSame("DE02120300000000202051", $line->iban);
        $this->assertSame("10.00", (string) $line->amount);
        $this->assertSame("Spende, vielen Dank", $line->reference);
        $this->assertSame("2026-01-05", $line->booked_at->format("Y-m-d"));
        $this->assertNull($line->matched_type);
    }

    public function testSkipsOutgoingPayments(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>-25.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Erstattung</zweck>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(0, $summary["created"]);
        $this->assertSame(1, $summary["skipped_outgoing"]);
        $this->assertSame(0, BankStatementLine::count());
    }

    public function testFiltersOutRowsFromUnwantedAccountsWhenAnAllowlistIsGiven(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>3</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>10.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Spende</zweck>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file, [1, 2]);

        $this->assertSame(0, $summary["created"]);
        $this->assertSame(1, $summary["skipped_account"]);
    }

    public function testAcceptsEveryAccountWhenNoAllowlistIsGiven(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>3</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>10.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Spende</zweck>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(1, $summary["created"]);
    }

    public function testSkipsRowsWithAnUnparsableDate(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>10.00</betrag>
                <valuta>not a date</valuta>
                <zweck>Spende</zweck>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(0, $summary["created"]);
        $this->assertSame(1, $summary["skipped_invalid"]);
    }

    public function testReimportingTheSameFileSkipsDuplicates(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>10.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Spende</zweck>
            </object>
        ');

        $this->importer()->importHibiscusXml($file);
        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(0, $summary["created"]);
        $this->assertSame(1, $summary["duplicates"]);
        $this->assertSame(1, BankStatementLine::count());
    }

    public function testAStructuredMandateIdIsPassedToTheMatcherAndResolvedImmediately(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        Debit::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M20260105120000",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);

        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>10.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Mitgliedsbeitrag</zweck>
                <mandateid>M20260105120000</mandateid>
                <endtoendid>NOTPROVIDED</endtoendid>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(1, $summary["created"]);
        $this->assertSame(1, $summary["matched"]["mandate_reference"]);
        $this->assertSame(0, $summary["unmatched"]);
        $line = BankStatementLine::sole();
        $this->assertSame("mandate_reference", $line->match_method);
    }

    public function testTriesSeveralPlausibleIbanTagNames(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <gegenkonto_iban>DE02120300000000202051</gegenkonto_iban>
                <betrag>10.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Spende</zweck>
            </object>
        ');

        $this->importer()->importHibiscusXml($file);

        $this->assertSame("DE02120300000000202051", BankStatementLine::sole()->iban);
    }

    /**
     * Real shape confirmed against a Hibiscus export supplied for this
     * pass (docs/civicrm-replacement.md) — a Rücklastschrift is `art`
     * "Retourenbelastung", negative `betrag`, with the original collection's
     * end-to-end reference/mandate carried over exactly. The purpose text
     * here is genuinely split "...IBAN: DE22760300" / "800803566136 BIC:
     * ..." across zweck/zweck2, which the reference assertion pins.
     */
    public function testRecognisesARetourenbelastungLineAsAChargebackAndReversesTheOriginalDebit(): void
    {
        $contact = Contact::create(["first_name" => "Karsten", "last_name" => "Fieber", "email" => "karsten@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "quarterly",
            "amount" => "30.00",
            "payment_method" => "directdebit",
            "payment_reference" => "M20260811064448",
            "end_date" => "2026-10-18",
        ]);
        $debit = Debit::create([
            "contact_id" => $contact->id,
            "membership_id" => $membership->id,
            "source" => "membership",
            "iban" => "DE22760300800803566136",
            "account_holder" => "Karsten Fieber",
            "amount" => "30.00",
            "mandate" => "M20260811064448",
            "mandate_date" => "2026-08-01",
            "status" => "executed",
            "previous_end_date" => "2026-08-18",
            "end_to_end_reference" => "P20260811070003498498195",
            "due_date" => "2026-08-18",
        ]);

        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <empfaenger_iban>DE22760300800803566136</empfaenger_iban>
                <zweck>Retoure SEPA Lastschrift vom 18.08.2026, Rueckgabegrund: AC06 Konto gesperrt SVWZ: RETURN/REFUND, Mitgliedsbeitrag Aug. 2026 - Okt. 2026 EREF: P20260811070003498498195 ENTG: 2,50 EUR Entgelt eingehende Rücklastschrift ORG.BETR.: 30,00 EUR IBAN: DE22760300</zweck>
                <art>Retourenbelastung</art>
                <betrag>-32.5</betrag>
                <valuta>19.08.2026 00:00:00</valuta>
                <zweck2>800803566136 BIC: CSDBDE71XXX</zweck2>
                <endtoendid>P20260811070003498498195</endtoendid>
                <mandateid>M20260811064448</mandateid>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(1, $summary["created"]);
        $this->assertSame(1, $summary["chargebacks"]["matched"]);
        $this->assertSame(0, $summary["chargebacks"]["unmatched"]);
        $line = BankStatementLine::sole();
        $this->assertSame("-32.50", (string) $line->amount);
        $this->assertSame("debit_reversal", $line->matched_type);
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertStringContainsString(
            "ORG.BETR.: 30,00 EUR IBAN: DE22760300800803566136 BIC: CSDBDE71XXX",
            $line->reference
        );
        $this->assertSame("failed", $debit->fresh()->status);
        $this->assertSame("2026-08-18", $membership->fresh()->end_date->format("Y-m-d"));
    }

    public function testAnUnmatchedChargebackLineStaysUnmatched(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>1</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <zweck>Retoure SEPA Lastschrift, kein bekanntes Mandat</zweck>
                <art>Retourenbelastung</art>
                <betrag>-12.50</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <mandateid>UNKNOWN-MANDATE</mandateid>
            </object>
        ');

        $summary = $this->importer()->importHibiscusXml($file);

        $this->assertSame(1, $summary["created"]);
        $this->assertSame(0, $summary["chargebacks"]["matched"]);
        $this->assertSame(1, $summary["chargebacks"]["unmatched"]);
        $line = BankStatementLine::sole();
        $this->assertSame("-12.50", (string) $line->amount);
        $this->assertNull($line->matched_type);
    }

    /**
     * Not chargeback-specific — confirmed against a second, unrelated line
     * in the same real export that also wrapped across zweck2/zweck3.
     * Reading only `zweck` (as before) silently truncated it.
     */
    public function testConcatenatesAWrappedPurposeTextAcrossZweckFieldsForANormalLineToo(): void
    {
        $file = $this->xmlFile('
            <object>
                <konto_id>19</konto_id>
                <empfaenger_iban>DE02120300000000202051</empfaenger_iban>
                <betrag>10.00</betrag>
                <valuta>05.01.2026 00:00:00</valuta>
                <zweck>Verwendungszweck: MVF-002275-W GENERAL LIVING</zweck>
                <zweck3>EXPENSES continued</zweck3>
            </object>
        ');

        $this->importer()->importHibiscusXml($file);

        $this->assertSame(
            "Verwendungszweck: MVF-002275-W GENERAL LIVINGEXPENSES continued",
            BankStatementLine::sole()->reference
        );
    }
}
