<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Log;

class AppendLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:gather';
    const LOGKEY = "metager.logs";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves all Log Entries from Redis and writes them to file';

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
        $redis = null;

        if (env("REDIS_CACHE_DRIVER", "redis") === "redis") {
            $redis = Redis::connection('cache');
        } elseif (env("REDIS_CACHE_DRIVER", "redis") === "redis-sentinel") {
            $redis = RedisSentinel::connection('cache');
        }
        if ($redis === null) {
            Log::error("No valid Redis Connection specified");
            return;
        }

        $elements = [];
        $reply = $redis->pipeline(function ($pipe) use ($elements) {
            $pipe->lrange(\App\Console\Commands\AppendLogs::LOGKEY, 0, -1);
            $pipe->del(\App\Console\Commands\AppendLogs::LOGKEY);
        });
        $elements = $reply[0];
        if (!is_array($elements) || sizeof($elements) <= 0) {
            return;
        }
        if (file_put_contents(\App\MetaGer::getMGLogFile(), implode(PHP_EOL, $elements) . PHP_EOL, FILE_APPEND) === false) {
            Log::error("Konnte Log Zeile(n) nicht schreiben");
            $redis->lpush(array_reverse($elements));
        } else {
            Log::info("Added " . sizeof($elements) . " lines to todays log!");
        }
    }
}
