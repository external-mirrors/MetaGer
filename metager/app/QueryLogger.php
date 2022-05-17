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
use Illuminate\Support\Facades\Log;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use PDO;

class QueryLogger
{
    const REDIS_KEY = "query_log";
    const REFERER_MAX_LENGTH = 50;
    const QUERY_MAX_LENGTH = 150;
    const AVAILABLE_INTERFACES = [
        "de",
        "en",
        "es",
    ];
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
        if(sizeof($query_logs) > 0){
            if(self::insertLogEntries($query_logs)){
                Log::info("Added " . sizeof($query_logs) . " lines to todays log! ");
                // Now we can pop those elements from the list
                for ($i = 0; $i < sizeof($query_logs); $i++) {
                    $redis->lpop(self::REDIS_KEY);
                }
            }else{
                Log::error("Konnte " . sizeof($log_strings) . " Log Zeile(n) nicht schreiben");
            }
        }else {
            Log::info("No logs to append to the file.");
        }
    }

    /**
     * Inserts new Log entries into Sqlite database
     */
    private static function insertLogEntries($query_logs){
        $insert_array = [];

        foreach($query_logs as $query_log){
            $query_log = json_decode($query_log);
            $time = DateTime::createFromFormat("Y-m-d H:i:s", $query_log->time);
            $year = $time->format("Y");
            $month = $time->format("m");
            if(empty($insert_array[$year])){
                $insert_array[$year] = [];
            }
            if(empty($insert_array[$year][$month])){
                $insert_array[$year][$month] = [];
            }
            $insert_array[$year][$month][] = [
                "time" => $query_log->time,
                "referer" => substr($query_log->referer, 0, self::REFERER_MAX_LENGTH),
                "request_time" => round($query_log->request_time, 3),
                "interface" => $query_log->interface,
                "query" => $query_log->query_string
            ];
        }

        /** @var \Illuminate\Database\SQLiteConnection[] */
        $connections = [];
        foreach($insert_array as $year => $months){
            foreach($months as $month => $insert_array){
                if(empty($connection[$year])){
                    $connections[$year] = self::validateDatabase($year);
                }
                self::validateTable($connections[$year], $month);
                if(!$connections[$year]->table($month)->insert($insert_array)){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Verifies that the Sqlite Database and Table for todays Log exist
     */
    private static function validateDatabase($year) {
        $current_database_path = \storage_path("logs/metager/{$year}.sqlite");
        
        // Create Database if it does not exist yet
        if(!\file_exists($current_database_path)){
            if(!touch($current_database_path)){
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
    private static function validateTable($connection, $table){
        if(!$connection->getSchemaBuilder()->hasTable("$table")){
            // Create a new Table
            $connection->getSchemaBuilder()->create("$table", function(Blueprint $table){
                $table->bigIncrements("id");
                $table->dateTime("time");
                $table->string("referer", self::REFERER_MAX_LENGTH);
                $table->float("request_time", 5, 3);
                $table->enum("interface", self::AVAILABLE_INTERFACES);
                $table->string("query", self::QUERY_MAX_LENGTH);
            });
        }
    }
}
