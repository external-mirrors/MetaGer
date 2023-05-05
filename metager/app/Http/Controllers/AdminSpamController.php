<?php

namespace App\Http\Controllers;

use App\QueryLogger;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Log;
use PDO;

class AdminSpamController extends Controller
{
    public function index()
    {
        $since = now()->subMinutes(3);
        $queries = $this->getQueries($since);
        $latest = now();
        if (sizeof($queries) > 0) {
            $latest = clone $queries[sizeof($queries) - 1]->time;
        }


        $currentBans = $this->getBans();
        $loadedBans = Redis::lrange("spam", 0, -1);

        return view("admin.spam")
            ->with('title', "Spam Konfiguration - MetaGer")
            ->with('queries', $queries)
            ->with('latest', $latest)
            ->with('bans', $currentBans)
            ->with('loadedBans', $loadedBans)
            ->with('js', [mix('js/admin/spam.js')])
            ->with('css', [
                mix('/css/admin/spam/style.css')
            ])
            ->with('darkcss', [mix('/css/admin/spam/dark.css')]);
    }

    public function ban(Request $request)
    {
        $banTime = $request->input('ban-time');
        $banRegexp = $request->input('regexp');

        $file = storage_path('logs/metager/ban.txt');

        $bans = [];
        if (file_exists($file)) {
            $bans = json_decode(file_get_contents($file), true);
        }

        $bans[] = ["banned-until" => $banTime . " 00:00:00", "regexp" => $banRegexp];

        \file_put_contents($file, json_encode($bans));

        return redirect(url('admin/spam'));
    }

    public function jsonQueries(Request $request)
    {
        if (!$request->filled("since")) {
            abort(404);
        } else {
            $since = Carbon::createFromFormat("Y-m-d H:i:s", $request->input("since"));
        }
        $queries = $this->getQueries($since);

        $latest = now();
        if (sizeof($queries) > 0) {
            $latest = clone $queries[sizeof($queries) - 1]->time;
        }

        $result = [
            "latest" => $latest->format("Y-m-d H:i:s"),
            "queries" => $queries,
        ];


        # JSON encoding will fail if invalid UTF-8 Characters are in this string
        # mb_convert_encoding will remove thise invalid characters for us
        return response()->json($result);
    }

    public function queryregexp(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $queries = $data["queries"];
        $regexps = [$data["regexp"]];

        $bans = $this->getBans();
        foreach ($bans as $ban) {
            $regexps[] = $ban["regexp"];
        }

        $resultData = [];

        foreach ($queries as $query) {
            $matches = false;
            foreach ($regexps as $regexp) {
                try {
                    if (preg_match($regexp, $query)) {
                        $matches = true;
                    }
                } catch (\Exception $e) {
                    // Exceptions are expected when no valid regexp is given
                }
            }
            $resultData[] = [
                "query" => $query,
                "matches" => $matches,
            ];
        }

        # JSON encoding will fail if invalid UTF-8 Characters are in this string
        # mb_convert_encoding will remove thise invalid characters for us
        $resultData = mb_convert_encoding($resultData, "UTF-8", "UTF-8");
        return response()->json($resultData);
    }

    private function getQueries(Carbon $since)
    {
        $query_logger = \app()->make(QueryLogger::class);
        $queries = $query_logger->getLogsSince($since);
        # Parse the Time
        foreach ($queries as $index => $query) {
            $time = Carbon::createFromFormat("Y-m-d H:i:s O", $query->time);
            $queries[$index]->time = $time;
            $queries[$index]->time_string = $time->isToday() ? $time->format("H:i:s") : $time->format("d.m.Y H:i:s");
            $expiration = clone $time;
            $expiration->addMinutes(3);
            $queries[$index]->expiration = $expiration;
            $queries[$index]->expiration_timestamp = $expiration->timestamp;
        }

        return $queries;
    }

    public function getBans()
    {
        $file = \storage_path('logs/metager/ban.txt');
        $bans = [];

        if (file_exists($file)) {
            $tmpBans = json_decode(file_get_contents($file), true);
            if (!empty($tmpBans) && is_array($tmpBans)) {
                foreach ($tmpBans as $ban) {
                    #dd($ban["banned-until"]);
                    $bannedUntil = Carbon::createFromFormat('Y-m-d H:i:s', $ban["banned-until"]);
                    if ($bannedUntil->isAfter(Carbon::now())) {
                        $bans[] = $ban;
                    }
                }
            }
        }

        return $bans;
    }

    public function deleteRegexp(Request $request)
    {
        $file = \storage_path('logs/metager/ban.txt');
        $bans = [];

        if (file_exists($file)) {
            $bans = json_decode(file_get_contents($file), true);
        }

        $regexpToDelete = $request->input('regexp');
        $newBans = [];

        foreach ($bans as $ban) {
            if ($ban["regexp"] !== $regexpToDelete) {
                $newBans[] = $ban;
            }
        }

        file_put_contents($file, json_encode($newBans));
        return redirect(url('admin/spam'));
    }
}