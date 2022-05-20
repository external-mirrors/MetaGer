<?php

namespace App\Http\Controllers;

use App\Jobs\ConvertCountFile;
use App\QueryLogger;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use PDO;
use Response;

class AdminInterface extends Controller
{

    public function count(Request $request)
    {


        if ($request->input('out', 'web') === "web") {
            $days = $request->input("days", 28);
            return view('admin.count')
                ->with('title', 'Suchanfragen - MetaGer')
                ->with('days', $days)
                ->with('css', [mix('/css/count/style.css')])
                ->with('darkcss', [mix('/css/count/dark.css')])
                ->with('js', [
                    mix('/js/admin/count.js')
                ]);
        }
    }

    public function getCountDataTotal(Request $request)
    {
        $date = $request->input('date', '');
        $date = Carbon::createFromFormat("Y-m-d H:i:s", "$date 00:00:00");
        if ($date === false) {
            abort(404);
        }

        $year = $date->format("Y");
        $month = $date->format("m");
        $day = $date->format("d");
        $cache_key = "admin_count_total_${year}_${month}_${day}";
        Cache::forget($cache_key);
        $total_count = Cache::get($cache_key);

        if ($total_count === null) {
            $database_file = \storage_path("logs/metager/$year/$month.sqlite");
            if (!\file_exists($database_file)) {
                abort(404);
            }

            $connection = new SQLiteConnection(new PDO("sqlite:$database_file"));
            if (!$connection->getSchemaBuilder()->hasTable($day)) {
                abort(404);
            }

            $total_count = $connection->table($day)->count('*');
            // No Cache for today
            if (!now()->isSameDay($date)) {
                Cache::put($cache_key, $total_count, now()->addWeek());
            }
        }

        $result = [
            "status" => 200,
            "error" => false,
            "data" => [
                "date" => "$year-$month-$day",
                "total" => $total_count,
            ]
        ];
        return \response()->json($result);
    }

    public function getCountDataUntil(Request $request)
    {
        $date = $request->input('date', '');
        $date = DateTime::createFromFormat("Y-m-d", $date);
        if ($date === false) {
            abort(404);
        }

        $year = $date->format("Y");
        $month = $date->format("m");
        $day = $date->format("d");
        $time = now()->format("H:i:s");


        $database_file = \storage_path("logs/metager/$year/$month.sqlite");
        if (!\file_exists($database_file)) {
            abort(404);
        }

        $connection = new SQLiteConnection(new PDO("sqlite:$database_file"));
        if (!$connection->getSchemaBuilder()->hasTable($day)) {
            abort(404);
        }

        $total_count = $connection->table($day)->whereTime("time", "<", $time)->count();

        $result = [
            "status" => 200,
            "error" => false,
            "data" => [
                "date" => "$year-$month-$day",
                "total" => $total_count,
            ]
        ];
        return \response()->json($result);
    }

    public function engineStats()
    {
        $result = [];
        $result["loadavg"] = sys_getloadavg();

        // Memory Data
        $data = explode("\n", trim(file_get_contents("/proc/meminfo")));
        $meminfo = [];
        foreach ($data as $line) {
            list($key, $val) = explode(":", $line);
            $meminfo[$key] = trim($val);
        }
        $conversions = [
            "KB",
            "MB",
            "GB",
            "TB",
        ];

        $memAvailable = $meminfo["MemAvailable"];
        $memAvailable = intval(explode(" ", $memAvailable)[0]);
        $counter = 0;
        while ($memAvailable > 1000) {
            $memAvailable /= 1000.0;
            $counter++;
        }
        $memAvailable = round($memAvailable);
        $memAvailable .= " " . $conversions[$counter];

        $result["memoryAvailable"] = $memAvailable;

        $resultCount = 0;
        $file = "/var/log/metager/mg3.log";
        if (file_exists($file)) {
            $fh = fopen($file, "r");
            try {
                while (fgets($fh) !== false) {
                    $resultCount++;
                }
            } finally {
                fclose($fh);
            }
        }

        $result["resultCount"] = number_format($resultCount, 0, ",", ".");
        return response()->json($result);
    }

    private function getStats($days)
    {
        $maxDate = Carbon::createFromFormat('d.m.Y', "28.06.2016");
        $selectedDate = Carbon::now()->subDays($days);
        if ($maxDate > $selectedDate) {
            $days = $maxDate->diffInDays(Carbon::now());
        }

        $logToday = \App\MetaGer::getMGLogFile();

        $archivePath = storage_path("logs/metager/");

        $today = [
            'logFile' => $logToday,
            'countPath' => storage_path('logs/metager/count/'),
            'countFile' => storage_path('logs/metager/count/' . getmypid()),
        ];
        if (\file_exists($today["countFile"])) {
            unlink($today["countFile"]);
        }

        $neededLogs = [
            0 => $today,
        ];
        $logsToRequest = [
            0 => $today,
        ];
        $requestedLogs = [];
        for ($i = 1; $i <= $days; $i++) {
            $date = Carbon::now()->subDays($i);
            $countPath = storage_path('logs/metager/count/' . $date->format("Y/m") . "/");
            $countFile = $countPath . $date->day . ".json";
            $neededLogs[$i] = [
                'logFile' => $archivePath . $date->format("Y/m/d") . ".log",
                'countPath' => $countPath,
                'countFile' => $countFile,
            ];
            if (!file_exists($neededLogs[$i]['countFile'])) {
                $logsToRequest[$i] = $neededLogs[$i];
            }
        }
        if (sizeof($logsToRequest) > 100) {
            set_time_limit(600);
        }
        // Create the Jobs for count file creation
        while (sizeof($logsToRequest) > 0 || sizeof($requestedLogs) > 0) {
            if (sizeof($requestedLogs) < 20 && sizeof($logsToRequest) > 0) {
                $newJob = array_shift($logsToRequest);
                $newJob["startedAt"] = time();
                $requestedLogs[] = $newJob;

                Redis::set(md5($newJob["countFile"]), "running");
                Redis::expire(md5($newJob["countFile"]), 15);
                ConvertCountFile::dispatch($newJob);
            } else {
                usleep(50000);
            }
            // Remove all finished Jobs
            $removedOne = false;
            do {
                $removedOne = false;
                foreach ($requestedLogs as $index => $requestedLog) {
                    if (!Redis::exists(md5($requestedLog["countFile"]))) {
                        unset($requestedLogs[$index]);
                        $removedOne = true;
                        break;
                    }
                }
            } while ($removedOne === true);
        }

        $result = [];

        foreach ($neededLogs as $key => $value) {
            $countFile = $value["countFile"];
            if (file_exists($countFile)) {
                $result[$key] = json_decode(file_get_contents($countFile));
            }
        }

        if (\file_exists($today["countFile"])) {
            unlink($today["countFile"]);
        }

        return $result;
    }

    public function check()
    {
        $q = "";

        /** @var QueryLogger */
        $query_logger = App::make(QueryLogger::class);
        $query = $query_logger->getLatestLogs(1);
        if (sizeof($query) > 0) {
            $q = $query[0]->query;
        }

        return view('admin.check')
            ->with('title', 'Wer sucht was? - MetaGer')
            ->with('q', $q);
    }
}
