<?php

namespace App;

use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class QueryLogger
{
    const REDIS_KEY = "query_log";
    /**
     * @var float $start_time
     */
    private $start_time, $end_time;

    private $focus;
    private $referer;
    private $interface;
    private $query_string;


    /**
     * Constructor will be called at the start of Search
     */
    public function __construct()
    {
        $this->start_time = microtime(true);
        /**
         * Get the Request Object
         * @var Request $request
         */
        $request = App::make(Request::class);
        $this->focus = $request->input('focus', "");
        $this->referer = $request->header('Referer');
        $this->interface = LaravelLocalization::getCurrentLocale();
        $this->query_string = $request->input("eingabe", "");
    }

    /**
     * Combines the gathered data of the search query
     * and writes it into the Redis cache. It will be 
     * retrieved from there periodically and written to
     * disk in batches.
     */
    public function createLog()
    {
        $this->end_time = microtime(true);

        $log_entry = [
            "time" => (new DateTime('now', new DateTimeZone("Europe/Berlin")))->format("Y-m-d H:i:s"),
            "referer" => $this->referer,
            "request_time" => $this->end_time - $this->start_time,
            "interface" => $this->interface,
            "query_string" => $this->query_string
        ];

        /** @var \Redis $redis */
        $redis = Redis::connection();
        $redis->rpush(self::REDIS_KEY, \json_encode($log_entry));
    }

    /**
     * Gets all currently queued query logs from local Redis
     * And permanently writes them to the log file.
     */
    public static function flushLogs()
    {
        /** @var \Predis\Client */
        $redis = Redis::connection();

        $queue_size = $redis->llen(self::REDIS_KEY);

        /** 
         * Will hold the Strings that get written to logfile. One entry per line 
         * 
         * @var string[] $log_strings
         * */
        $log_strings = [];

        /**
         * The queued log Strings. Json encoded
         * 
         * @var string[] $query_logs
         */
        $query_logs = $redis->lrange(self::REDIS_KEY, 0, $queue_size - 1);
        foreach ($query_logs as $query_log) {
            $query_log = \json_decode($query_log);
            $time = DateTime::createFromFormat("Y-m-d H:i:s", $query_log->time, new DateTimeZone("Europe/Berlin"));
            $log_strings[] = $time->format("H:i:s") .
                " ref=" .
                $query_log->referer .
                " interface=" .
                $query_log->interface .
                " eingabe=" .
                $query_log->query_string;
        }

        if (sizeof($log_strings) > 0) {
            $log_file = self::getMGLogFile();
            if (file_put_contents(\App\MetaGer::getMGLogFile(), implode(PHP_EOL, $log_strings) . PHP_EOL, FILE_APPEND) === false) {
                Log::error("Konnte " . sizeof($log_strings) . " Log Zeile(n) nicht schreiben");
            } else {
                Log::info("Added " . sizeof($log_strings) . " lines to todays log! " . $log_file);
                // Now we can pop those elements from the list
                for ($i = 0; $i < sizeof($query_logs); $i++) {
                    $redis->lpop(self::REDIS_KEY);
                }
            }
        } else {
            Log::info("No logs to append to the file.");
        }
    }

    private static function getMGLogFile()
    {
        $logpath = storage_path("logs/metager/" . date("Y") . "/" . date("m") . "/");
        if (!file_exists($logpath)) {
            mkdir($logpath, 0777, true);
        }
        $logpath .= date("d") . ".log";
        return $logpath;
    }
}
