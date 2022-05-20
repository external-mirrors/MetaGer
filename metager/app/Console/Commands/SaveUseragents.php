<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SaveUseragents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:useragents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Saves a list of recent User-Agents from Redis into a sqlite database';

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
     * @return mixed
     */
    public function handle()
    {
        $agents = [];
        $agent = null;
        $now = Carbon::now('utc')->toDateTimeString();

        while (!empty(($agent = Redis::lpop("useragents")))) {
            $newEntry = json_decode($agent, true);
            $newEntry["created_at"] = $now;
            $newEntry["updated_at"] = $now;
            $agents[] = $newEntry;
        }
        
        if (!empty($agents)) {
            \App\UserAgent::insert($agents);
        }

        // Delete old entries (older than 24h)
        $expiration = Carbon::now('utc')->subDays(1);
        \App\UserAgent::where('created_at', '<', $expiration)->delete();
    }
}
