<?php

namespace App\Console\Commands;

use Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class LoadSpam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spam:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads a list of current Spams into redis';

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
        // Redis might not be available now
        for ($count = 0; $count < 60; $count++) {
            try {
                $this->loadSpam();
                return 0;
            } catch (\Exception $e) {
                if ($count >= 59) {
                    // If its not available after 10 seconds we will exit
                    return 1;
                }
                sleep(1);
            }
        }
        
    }

    private function loadSpam() {
        $filePath = \storage_path('logs/metager/ban.txt');
        $bans = [];
        if (\file_exists($filePath)) {
            $bans = json_decode(file_get_contents($filePath), true);
        }

        $bansToLoad = [];

        foreach ($bans as $ban) {
            $bannedUntil = Carbon::createFromFormat("Y-m-d H:i:s", $ban["banned-until"]);
            if ($bannedUntil->isAfter(Carbon::now())) {
                $bansToLoad[] = $ban["regexp"];
            }
        }

        Redis::pipeline(function ($redis) use ($bansToLoad) {
            $redis->del("spam");
            foreach ($bansToLoad as $ban) {
                $redis->rpush("spam", $ban);
            }
        });
    }
}
