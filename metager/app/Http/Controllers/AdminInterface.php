<?php

namespace App\Http\Controllers;

use App\QueryLogger;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use PDO;

class AdminInterface extends Controller
{

    public function count(Request $request)
    {
        if ($request->input('out', 'web') === "web") {
            $days = $request->input("days", 28);
            $interface = $request->input('interface', 'all');
            return view('admin.count')
                ->with('title', 'Suchanfragen - MetaGer')
                ->with('days', $days)
                ->with('interface', $interface)
                ->with('css', [mix('/css/count/style.css')])
                ->with('darkcss', [mix('/css/count/dark.css')])
                ->with('js', [
                    mix('/js/admin/count.js')
                ]);
        }
    }

    public function getCountData(Request $request)
    {
        $date = $request->input('date', '');
        $date = Carbon::createFromFormat("Y-m-d H:i:s", "$date 00:00:00");
        if ($date === false) {
            abort(404);
        }

        $interface = $request->input('interface', 'all');

        $year = $date->format("Y");
        $month = $date->format("m");
        $day = $date->format("d");
        $now = now();
        $cache_key = "admin_count_data_${interface}_${year}_${month}_${day}";
        $result = Cache::get($cache_key);


        if ($result === null) {
            $result = [
                "total" => 0,
                "until_now" => 0
            ];

            $database_file = \storage_path("logs/metager/" . $date->format("Y/m/d") . ".sqlite");
            if ($database_file === null) {
                abort(404);
            }

            $connection = new SQLiteConnection(new PDO("sqlite:$database_file", null, null, [PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY]));
            $connection->disableQueryLog();
            try {
                if (!$connection->getSchemaBuilder()->hasTable("logs")) {
                    abort(404);
                }

                $data = $connection->table("logs")->selectRaw("COUNT(*) AS count, time");
                if ($interface !== "all") {
                    $data = $data->where("interface", "=", $interface);
                }
                $data->groupByRaw("STRFTIME('%s', time) / 300");
                $data = $data->get();
            } finally {
                $connection->disconnect();
            }

            foreach ($data as $entry) {
                $time = Carbon::createFromFormat("Y-m-d H:i:s", $entry->time);
                $time->year($now->year);
                $time->month($now->month);
                $time->day($now->day);
                $result["total"] += $entry->count;
                if ($time->isBefore($now)) {
                    $result["until_now"] += $entry->count;
                }
            }

            Cache::put($cache_key, $result, now()->addMinutes(5));
        }

        $result = [
            "status" => 200,
            "error" => false,
            "data" => [
                "date" => "$year-$month-$day",
                "time" => $now->format("H:i:s"),
                "total" => $result["total"],
                "until_now" => $result["until_now"],
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

    public function getFPMStatus()
    {
        $status = \fpm_get_status();
        return response()->json($status);
    }
}