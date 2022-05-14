<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class AppendLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:gather';
    const LOGKEY = "metager.logs.2021";

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
        $this->handleMGLogs();
    }

    private function handleMGLogs()
    {
        $redis = null;
        
        $redis = Redis::connection(config('cache.stores.redis.connection'));

        if ($redis === null) {
            $this->error("No valid Redis Connection specified");
            return;
        }

        $elements = [];
        while(($value = $redis->lpop(\App\Console\Commands\AppendLogs::LOGKEY)) !== false){
            $elements[] = $value;
        }

        if (file_put_contents(\App\MetaGer::getMGLogFile(), implode(PHP_EOL, $elements) . PHP_EOL, FILE_APPEND) === false) {
            $this->error("Konnte " . sizeof($elements) . " Log Zeile(n) nicht schreiben");
            foreach($elements as $element){
                $redis->lPush(\App\Console\Commands\AppendLogs::LOGKEY, $element);
            }
        }else{
            $this->info("Added " . sizeof($elements) . " lines to todays log! " . \App\MetaGer::getMGLogFile());
        }

        
    }
}
