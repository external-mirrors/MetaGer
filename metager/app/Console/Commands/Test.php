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
        $start = Carbon::createFromFormat("Y-m-d", "2022-09-28");
        $daysToProcess = 3;

        $chunk = 10000;

        $connection = DB::connection("logs");

        while ($daysToProcess > 0) {
            $path = storage_path("logs/metager/" . $start->format("Y/m/d") . ".sqlite");
            $this->info("Processing: $path");
            $sqliteConnection = new SQLiteConnection(new PDO("sqlite:" . $path));

            // Test if chunks fit in memory
            $entry_count = $sqliteConnection->table("logs")->count();
            $progress_bar = $this->output->createProgressBar($entry_count);
            $progress_bar->start();

            $sqliteConnection->table("logs")->select("*")->orderBy("time", "asc")->chunk($chunk, function ($log_entries) use ($connection, $progress_bar) {
                $data = array();
                foreach ($log_entries as $log_entry) {
                    $data[] = [
                        "time" => $log_entry->time . " +0000",
                        "referer" => $log_entry->referer,
                        "request_time" => $log_entry->request_time,
                        "focus" => substr($log_entry->focus, 0, 20),
                        "locale" => substr($log_entry->interface, 0, 5),
                        "query" => $log_entry->query
                    ];
                }
                $connection->table("logs")->insert($data);
                $progress_bar->advance(sizeof($log_entries));
            });
            $progress_bar->finish();
            $daysToProcess--;
            $start->addDay();
            $this->newLine(2);
        }

        $this->info("Finished");
        return 0;
    }
}