<?php

namespace App\Console\Commands;

use App\QueryLogger;
use Illuminate\Console\Command;

class MigrateLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:migrate {year} {month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $year = $this->argument("year");
        $month = $this->argument("month");

        QueryLogger::migrate($year, $month);
        return 0;
    }
}
