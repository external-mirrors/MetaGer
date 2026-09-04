<?php

namespace Tests\Unit\Assoc;

use App\Assoc\BankStatementImporter;
use App\Assoc\BankStatementMatcher;
use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
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
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        Debit::create([
            "household_id" => $household->id,
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
}
