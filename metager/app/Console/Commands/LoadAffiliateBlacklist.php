<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class LoadAffiliateBlacklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:affiliate-blacklist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads the Affiliate Blacklist from DB into Redis';

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
        // Redis might not be available now
        for ($count = 0; $count < 60; $count++) {
            try {
                return $this->loadAffiliateBlacklist();
            } catch (\Exception $e) {
                if ($count >= 59) {
                    // If its not available after 10 seconds we will exit
                    return 1;
                }
                sleep(1);
            }
        }
    }

    private function loadAffiliateBlacklist()
    {
        $blacklistItems = DB::table("affiliate_blacklist", "b")
            ->select("hostname")
            ->where("blacklist", true)
            ->get();

        Redis::pipeline(function ($redis) use ($blacklistItems) {
            $redisKey = \App\Http\Controllers\AdgoalController::REDIS_BLACKLIST_KEY;
            $redis->del($redisKey);
            foreach ($blacklistItems as $item) {
                $hostname = $item->hostname;
                $redis->hset(\App\Http\Controllers\AdgoalController::REDIS_BLACKLIST_KEY, $hostname, true);
            }
        });

        return 0;
    }
}