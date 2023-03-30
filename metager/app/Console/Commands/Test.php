<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Database\SQLiteConnection;
use PDO;
use Carbon\Carbon;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migratelogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = DB::connection("pgsql");
        $this->info("Processing: 2022/03/01.sqlite");
        $sqliteConnection = new SQLiteConnection(new PDO("sqlite:" . storage_path("logs/metager/2022/03/02.sqlite")));

        $sqliteConnection->table("logs")->select("*")->orderBy("time", "asc")->limit(100)->chunk(100, function ($log_entries) use ($connection) {
            $data = array();
            foreach ($log_entries as $log_entry) {
                $data[] = [
                    "time" => $log_entry->time . "+00",
                    "referer" => $log_entry->referer,
                    "request_time" => $log_entry->request_time,
                    "focus" => $log_entry->focus,
                    "interface" => $log_entry->interface,
                    "query" => $log_entry->query
                ];
            }
            $connection->table("logs")->insert($data);
        });

        return 0;
    }
}