<?php

namespace App;

use DateTime;
use DateTimeZone;
use ErrorException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use PDO;

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
        /** @var MetaGer */
        $metager = App::make(MetaGer::class);
        $log_entry = [
            "time" => (new DateTime('now', new DateTimeZone("Europe/Berlin")))->format("Y-m-d H:i:s"),
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
            $time = DateTime::createFromFormat("Y-m-d H:i:s", $query_log->time);
            $year = $time->format("Y");
            $month = $time->format("m");
            $day = $time->format("d");
            if (empty($insert_array[$year])) {
                $insert_array[$year] = [];
            }
            if (empty($insert_array[$year][$month])) {
                $insert_array[$year][$month] = [];
            }
            if (empty($insert_array[$year][$month][$day])) {
                $insert_array[$year][$month][$day] = [];
            }
            $insert_array[$year][$month][$day][] = [
                "time" => $query_log->time,
                "referer" => substr($query_log->referer, 0, self::REFERER_MAX_LENGTH),
                "request_time" => round($query_log->request_time, 3),
                "focus" => $query_log->focus,
                "interface" => substr($query_log->interface, 0, 5),
                "query" => $query_log->query_string
            ];
        }

        /** @var \Illuminate\Database\SQLiteConnection[] */
        $connections = [];
        foreach ($insert_array as $year => $months) {
            foreach ($months as $month => $days) {
                foreach ($days as $day => $insert_array) {
                    if (empty($connection[$year])) {
                        $connections[$year] = [];
                    }
                    if (empty($connections[$year][$month])) {
                        $connections[$year][$month] = self::validateDatabase($year, $month);
                    }
                    self::validateTable($connections[$year][$month], $day);
                    if (!$connections[$year][$month]->table($day)->insert($insert_array)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Verifies that the Sqlite Database and Table for todays Log exist
     */
    private static function validateDatabase($year, $month)
    {
        $folder = \storage_path("logs/metager/$year");
        if (!\file_exists($folder)) {
            if (!mkdir($folder, 0777, true)) {
                throw new ErrorException("Couldn't create folder for sqlite Databse in \"$folder\"");
            }
        }
        $current_database_path = \storage_path("logs/metager/$year/$month.sqlite");

        // Create Database if it does not exist yet
        if (!\file_exists($current_database_path)) {
            if (!touch($current_database_path)) {
                throw new ErrorException("Couldn't create sqlite Databse in \"$current_database_path\"");
            }
        }

        $connection = new SQLiteConnection(new PDO("sqlite:${current_database_path}"));

        return $connection;
    }

    /**
     * Creates the table for the requested month if it does not exist
     * 
     * @param Illuminate\Database\SQLiteConnection $connection
     * @param string $table
     */
    private static function validateTable($connection, $table)
    {
        if (!$connection->getSchemaBuilder()->hasTable("$table")) {
            // Create a new Table
            $connection->getSchemaBuilder()->create("$table", function (Blueprint $table) {
                $table->bigIncrements("id");
                $table->dateTime("time");
                $table->string("referer", self::REFERER_MAX_LENGTH);
                $table->float("request_time", 5, 3);
                $table->string("focus", 10);
                $table->string("interface", 5);
                $table->string("query", self::QUERY_MAX_LENGTH)->nullable();
            });
        }
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
            if (\in_array($file, [".", ".."])) continue;

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
