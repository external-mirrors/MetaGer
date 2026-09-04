<?php

namespace App\Console\Commands;

use App\Assoc\BankStatementImporter;
use Illuminate\Console\Command;

class ImportBankStatement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assoc:import-bank-statement {file : Path to a Hibiscus "Umsätze exportieren" XML file}
        {--account=* : Hibiscus konto_id to accept; repeatable, default accepts every account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports a Hibiscus bank-statement export into assoc_bank_statement_lines and runs the shadow-mode matcher against it. Re-runnable: duplicate rows (same iban/amount/reference/booked_at) are skipped.';

    public function handle(BankStatementImporter $importer): int
    {
        $file = $this->argument('file');
        if (!is_file($file)) {
            $this->error("Datei nicht gefunden: {$file}");

            return self::FAILURE;
        }

        $accountIds = array_map('intval', $this->option('account'));

        $summary = $importer->importHibiscusXml($file, $accountIds);

        $this->line("Neu angelegt: {$summary['created']}");
        $this->line("Duplikate übersprungen: {$summary['duplicates']}");
        $this->line("Ausgehende Zahlungen übersprungen: {$summary['skipped_outgoing']}");
        $this->line("Andere Konten übersprungen: {$summary['skipped_account']}");
        $this->line("Ungültige Zeilen übersprungen: {$summary['skipped_invalid']}");
        foreach ($summary['matched'] as $method => $count) {
            $this->line("Automatisch zugeordnet ({$method}): {$count}");
        }
        $this->line("Nicht zugeordnet (manuelle Prüfung nötig): {$summary['unmatched']}");

        return self::SUCCESS;
    }
}
