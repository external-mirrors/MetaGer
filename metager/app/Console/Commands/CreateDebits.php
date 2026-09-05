<?php

namespace App\Console\Commands;

use App\Assoc\DebitCreator;
use Illuminate\Console\Command;

/**
 * Ported from de.suma-ev.donation-debit's Membership.CreateDebits and
 * RecurContribution.CreateDebits cron jobs — see DebitCreator's docblock for
 * the mapping onto assoc_memberships/assoc_recur_contributions.
 */
class CreateDebits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assoc:create-debits {--limit=25 : Maximum memberships/recur contributions to process per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates assoc_debits rows for due membership dues and recurring donations, ready for the next SEPA batch.';

    public function handle(DebitCreator $creator): int
    {
        $limit = (int) $this->option('limit');

        $memberships = $creator->createForDueMemberships($limit);
        $this->line("Mitgliedsbeiträge angelegt: {$memberships->count()}");

        $recurContributions = $creator->createForDueRecurContributions($limit);
        $this->line("Daueraufträge angelegt: {$recurContributions->count()}");

        return self::SUCCESS;
    }
}
