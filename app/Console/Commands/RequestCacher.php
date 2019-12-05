<?php

namespace App\Console\Commands;

use Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RequestCacher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:cacher';

    const CACHER_QUEUE = 'cacher.queue';
    protected $shouldRun = true;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listens to a buffer of fetched search results and writes them to the filesystem cache.';

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
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, [$this, "sig_handler"]);
        pcntl_signal(SIGTERM, [$this, "sig_handler"]);
        pcntl_signal(SIGHUP, [$this, "sig_handler"]);

        while ($this->shouldRun) {
            $cacheItem = Redis::blpop(self::CACHER_QUEUE, 1);
            if (!empty($cacheItem)) {
                $cacheItem = json_decode($cacheItem[1], true);
                if (empty($cacheItem["body"])) {
                    $cacheItem["body"] = "no-result";
                }
                Cache::put($cacheItem["hash"], $cacheItem["body"], now()->addMinutes($cacheItem["cacheDuration"]));
            }
        }
    }

    public function sig_handler($sig)
    {
        $this->shouldRun = false;
        echo ("Terminating Cacher Process\n");
    }
}
