<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Carbon;

class Heartbeat extends Command
{
    const REDIS_KEY = "heartbeat";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stores in local Redis when it last ran. Provides a heartbeat for liveness probes to check whether scheduler is running or not.';

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
        try{
            $now = Carbon::now();
            Redis::set(self::REDIS_KEY, $now->format('Y-m-d H:i:s'));
        } catch (\Exception $e){
            echo $e->getTraceAsString();
            return 1;
        }
        return 0;
    }
}
