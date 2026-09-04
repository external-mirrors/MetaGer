<?php

namespace App\Console\Commands;

use App\Assoc\DonationReceiptGenerator;
use App\Models\Assoc\Debit;
use Illuminate\Console\Command;

class GenerateDonationReceipts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assoc:generate-donation-receipts {--debit=* : Generate a single receipt for these assoc_debits IDs, regardless of the payer\'s preference}
        {--year= : Generate the annual batch for this year, for payers whose effective preference is "annual"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generates donation/membership-dues receipts (assoc_donation_receipts) from executed, unreceipted assoc_debits. With no options, generates every payer with an \"immediate\" effective preference. --year generates the annual catch-up batch. --debit generates one or more specific receipts regardless of preference.";

    public function handle(DonationReceiptGenerator $generator): int
    {
        $debitIds = $this->option("debit");
        $year = $this->option("year");

        if ($debitIds !== [] && $year !== null) {
            $this->error("--debit and --year are mutually exclusive.");

            return self::FAILURE;
        }

        if ($debitIds !== []) {
            foreach ($debitIds as $debitId) {
                $debit = Debit::find($debitId);
                if ($debit === null) {
                    $this->error("Debit nicht gefunden: {$debitId}");

                    return self::FAILURE;
                }

                try {
                    $receipt = $generator->generateSingle($debit);
                } catch (\RuntimeException $e) {
                    $this->error($e->getMessage());

                    return self::FAILURE;
                }

                $this->line("Bescheinigung erstellt: {$receipt->id} ({$receipt->sourceLabel()}, {$receipt->total_amount} €)");
            }

            return self::SUCCESS;
        }

        if ($year !== null) {
            $receipts = $generator->generateAnnualBatch((int) $year);
            $this->line("Jahresbescheinigungen für {$year} erstellt: {$receipts->count()}");

            return self::SUCCESS;
        }

        $receipts = $generator->generateImmediate();
        $this->line("Sofort-Bescheinigungen erstellt: {$receipts->count()}");

        return self::SUCCESS;
    }
}
