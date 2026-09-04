<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\BankStatementLine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class ImportBankStatementCommandTest extends TestCase
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

    public function testANormalRunPersistsTheImportedLines(): void
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

        Artisan::call('assoc:import-bank-statement', ['file' => $file]);

        $this->assertSame(1, BankStatementLine::count());
    }

    public function testReportsCountsInItsOutput(): void
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

        Artisan::call('assoc:import-bank-statement', ['file' => $file]);
        $output = Artisan::output();

        $this->assertStringContainsString('Neu angelegt: 1', $output);
        $this->assertStringContainsString('Nicht zugeordnet (manuelle Prüfung nötig): 1', $output);
    }

    public function testAMissingFileFailsCleanly(): void
    {
        $exitCode = Artisan::call('assoc:import-bank-statement', ['file' => '/nonexistent/path.xml']);

        $this->assertSame(1, $exitCode);
        $this->assertSame(0, BankStatementLine::count());
    }

    public function testTheAccountOptionRestrictsWhichKontoIdsAreAccepted(): void
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

        Artisan::call('assoc:import-bank-statement', ['file' => $file, '--account' => [1, 2]]);

        $this->assertSame(0, BankStatementLine::count());
    }
}
