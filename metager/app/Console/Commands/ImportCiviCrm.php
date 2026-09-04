<?php

namespace App\Console\Commands;

use App\Assoc\CiviCrmImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCiviCrm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assoc:import-civicrm {--dry-run : Roll back every write at the end, for a trial run against real data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports contacts, debits and recur contributions from CiviCRM into assoc_*. Re-runnable: matches existing rows by civicrm_id.';

    public function handle(CiviCrmImporter $importer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        DB::beginTransaction();
        $counts = $importer->import();
        if ($dryRun) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        foreach ($counts as $label => $count) {
            $this->line(str_replace('_', ' ', $label) . ': ' . $count);
        }
        if ($dryRun) {
            $this->warn('Dry run — no changes were kept.');
        }

        return self::SUCCESS;
    }
}
