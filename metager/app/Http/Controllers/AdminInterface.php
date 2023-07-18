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
use DB;

class AdminInterface extends Controller
{

    public function count(Request $request)
    {
        try {
            if ($request->filled("start")) {
                $start = Carbon::createFromFormat("Y-m-d H:i:s", $request->input("start") . " 00:00:00");
            }
            if ($request->filled("end")) {
                $end = Carbon::createFromFormat("Y-m-d H:i:s", $request->input("end") . " 00:00:00");
            } else {
                $end = Carbon::createMidnightDate();
            }
        } catch (\Exception $e) {
            $start = null;
            $end = null;
        }
        if (empty($start) || empty($end) || $start->isAfter($end) || $end->isBefore($start)) {
            $end = Carbon::createMidnightDate();
            $start = clone $end;
            $start->subDays(28);
        }

        if ($request->input('out', 'web') === "web") {
            $interface = $request->input('interface', 'all');
            return view('admin.count')
                ->with('title', 'Suchanfragen - MetaGer')
                ->with('start', $start)
                ->with('end', $end)
                ->with("days", $start->diffInDays($end))
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

        $connection = DB::connection("logs");
        $log_summary = $connection
            ->table("logs")
            ->select(DB::raw('to_timestamp(floor(EXTRACT(epoch FROM time) / EXTRACT(epoch FROM interval \'5 min\')) * EXTRACT(epoch FROM interval \'5 min\')) as timestamp, count(*)'))
            ->whereRaw("(time at time zone 'UTC') between '" . $date->format("Y-m-d") . " 00:00:00' and '" . $date->format("Y-m-d") . " 23:59:59'");
        if ($interface === "none-german") {
            $log_summary = $log_summary->where("locale", "!=", "de");
        } else if ($interface == "none-german-english") {
            $log_summary = $log_summary->whereNotIn("locale", ["de", "en"]);
        } else if ($interface !== "all") {
            $log_summary = $log_summary->where("locale", "=", $interface);
        }
        $log_summary = $log_summary->groupBy("timestamp")
            ->orderBy("timestamp")
            ->get();

        $result = [
            "total" => 0,
            "until_now" => 0
        ];

        $now = Carbon::now();
        foreach ($log_summary as $entry) {
            $time = Carbon::createFromFormat("Y-m-d H:i:sO", $entry->timestamp);
            $time->day(1)->year($now->year)->month($now->month)->day($now->day);
            $result["total"] += $entry->count;
            if ($time->isBefore($now)) {
                $result["until_now"] += $entry->count;
            }
        }

        if ($date->isToday()) {
            $result["total"] = null;
        }

        $result = [
            "status" => 200,
            "error" => false,
            "data" => [
                "date" => $now->format("Y-m-d"),
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