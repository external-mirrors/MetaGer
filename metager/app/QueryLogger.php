<?php

namespace App;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use DB;

class QueryLogger
{
    const REDIS_KEY = "query_log";
    const REFERER_MAX_LENGTH = 150;
    const QUERY_MAX_LENGTH = 250;

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
        $this->interface = Localization::getLanguage();
        if ($this->interface == "de" && Localization::getRegion() !== "DE") {
            $this->interface = strtolower(Localization::getRegion());
        }
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
        /** @var MetaGer */
        $metager = App::make(MetaGer::class);
        $log_entry = [
            "time" => (new DateTime('now', new DateTimeZone("UTC")))->format("Y-m-d H:i:s"),
            "referer" => $this->referer,
            "request_time" => $this->end_time - $this->start_time,
            "focus" => $metager->getFokus(),
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
        if (sizeof($query_logs) > 0) {
            if (self::insertLogEntries($query_logs)) {
                Log::info("Added " . sizeof($query_logs) . " lines to todays log! ");
                // Now we can pop those elements from the list
                for ($i = 0; $i < sizeof($query_logs); $i++) {
                    $redis->lpop(self::REDIS_KEY);
                }
            } else {
                Log::error("Konnte " . sizeof($log_strings) . " Log Zeile(n) nicht schreiben");
            }
        } else {
            Log::info("No logs to append to the file.");
        }
    }

    /**
     * Inserts new Log entries into Sqlite database
     */
    private static function insertLogEntries($query_logs)
    {
        $insert_array = [];

        foreach ($query_logs as $query_log) {
            $query_log_object = json_decode($query_log);
            if (empty($query_log_object)) {
                Log::error(var_export($query_log, true));
                continue;
            } else {
                $query_log = $query_log_object;
            }
            $insert_array[] = [
                "time" => $query_log->time,
                "referer" => substr($query_log->referer, 0, self::REFERER_MAX_LENGTH),
                "request_time" => round($query_log->request_time, 2),
                "focus" => substr($query_log->focus, 0, 20),
                "locale" => substr($query_log->interface, 0, 5),
                "query" => $query_log->query_string
            ];
        }

        if (sizeof($insert_array) > 0) {
            return DB::connection("logs")->table("logs")->insert($insert_array);
        }
        return false;
    }

    /**
     * Fetches the latest n logs
     * 
     * @param int $n How many logs to fetch max
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getLogsSince(Carbon $since)
    {
        $connection = DB::connection("logs");
        $since->setTimezone("UTC"); // We will query in UTC time

        $queries = $connection->table("logs")
            ->whereRaw("(time at time zone 'UTC') > '" . $since->format("Y-m-d H:i:s") . "'")
            ->orderBy("time", "asc")
            ->get();
        return $queries;
    }

    /**
     * Migrates old text file logs to sqlite
     * 
     * @param string $year
     * @param string $month
     */
    public static function migrate($year, $month)
    {
        $batch_size = 10000;
        $path = \storage_path("logs/metager/$year/$month");

        /** @var \Predis\Client */
        $redis = Redis::connection();

        $files = scandir($path);
        foreach ($files as $file) {
            if (\in_array($file, [".", ".."]) || preg_match("/\.sqlite$/", $file))
                continue;
            if ($year === "2022" && $month === "05" && $file === "20.log")
                continue;

            $day = substr($file, 0, stripos($file, ".log"));
            Log::info("Parsing $file");
            $file_path = $path . "/" . $file;
            \exec("iconv -f utf-8 -t utf-8 -c " . $file_path . " -o " . $file_path . ".bak");
            \exec("mv " . $file_path . ".bak" . " " . $file_path);
            $fh = fopen($file_path, "r");
            $batch_count = 0;
            while (($line = fgets($fh)) !== false) {
                if (preg_match("/^(\d{2}:\d{2}:\d{2})\s+?ref=(.*?)\s+?time=([^\s]+)\s+?serv=([^\s]+)\s+?interface=([^\s]+).*?eingabe=(.+)$/", $line, $matches) != false) {
                    $log_entry = [
                        "time" => "$year-$month-$day " . $matches[1],
                        "referer" => trim($matches[2]),
                        "request_time" => trim($matches[3]),
                        "focus" => trim($matches[4]),
                        "interface" => trim($matches[5]),
                        "query_string" => trim($matches[6])
                    ];
                    $json_string = \json_encode($log_entry);
                    if ($json_string === false) {
                        Log::error("Couldn't encode");
                        Log::error(var_export($log_entry, true));
                        continue;
                    }
                    $redis->rpush(self::REDIS_KEY, $json_string);
                    $batch_count++;
                    if ($batch_count >= $batch_size) {
                        Artisan::call("logs:gather");
                        $batch_count = 0;
                    }
                } else {
                    Log::error("Regexp did not work for");
                    Log::error($line);
                    continue;
                }
            }
            Artisan::call("logs:gather");
            Log::info("Finished $file");
            fclose($fh);
        }
    }
}