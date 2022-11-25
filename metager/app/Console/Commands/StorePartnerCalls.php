<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Http\Controllers\AdgoalController;
use Illuminate\Support\Facades\DB;

class StorePartnerCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affilliates:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stores cached clicks on affiliate links into DB.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        AdgoalController::storePartnerCalls();

        # Remove old entries
        # The duration in hours for entries to last is defined as constant in AdgoalController
        if (config("database.default") === "sqlite") {
            DB::delete("delete from affiliate_clicks where created_at < datetime('now', '-' || ? || ' hours');", [\App\Http\Controllers\AdgoalController::STORAGE_DURATION_HOURS]);
        } else {
            DB::delete("delete from affiliate_clicks where created_at < DATE_SUB(NOW(), INTERVAL ? HOUR);", [\App\Http\Controllers\AdgoalController::STORAGE_DURATION_HOURS]);
        }

        return 0;
    }
}
