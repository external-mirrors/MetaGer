<?php

namespace App\Http\Controllers;

use App\QueryLogger;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use PDO;

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
            try {
                if (!$connection->getSchemaBuilder()->hasTable($day)) {
                    abort(404);
                }

                $total_count = $connection->table($day)->count('*');
            } finally {
                $connection->disconnect();
            }
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
        try {
            if (!$connection->getSchemaBuilder()->hasTable($day)) {
                abort(404);
            }

            $total_count = $connection->table($day)->whereTime("time", "<", $time)->count();
        } finally {
            $connection->disconnect();
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
