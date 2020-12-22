<?php

namespace App\Http\Controllers;

use Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class AdminSpamController extends Controller
{
    public function index()
    {
        $queries = $this->getQueries();

        $currentBans = $this->getBans();
        $loadedBans = Redis::lrange("spam", 0, -1);

        return view("admin.spam")
            ->with('title', "Spam Konfiguration - MetaGer")
            ->with('queries', $queries)
            ->with('bans', $currentBans)
            ->with('loadedBans', $loadedBans)
            ->with('darkcss', [mix('/css/spam.css')]);
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

        $bans[] = ["banned-until" => Carbon::now()->add($banTime)->format("Y-m-d H:i:s"), "regexp" => $banRegexp];

        \file_put_contents($file, json_encode($bans));

        return redirect(url('admin/spam'));
    }

    public function jsonQueries()
    {
        $queries = $this->getQueries();
        # JSON encoding will fail if invalid UTF-8 Characters are in this string
        # mb_convert_encoding will remove thise invalid characters for us
        $queries = mb_convert_encoding($queries, "UTF-8", "UTF-8");
        return response()->json($queries);
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

    private function getQueries()
    {
        $minuteToFetch = Carbon::now()->subMinutes(2);
        $logFile = storage_path("logs/metager/" . $minuteToFetch->format("Y/m/d") . ".log");

        $result = shell_exec("cat $logFile | grep " . $minuteToFetch->format("H:i:"));
        $result = explode(PHP_EOL, $result);

        $queries = array();

        foreach ($result as $line) {
            if ($query = \preg_match("/.*eingabe=(.*)$/", $line, $matches)) {
                $queries[] = $matches[1];
            }
        }
        return $queries;
    }

    public function getBans()
    {
        $file = \storage_path('logs/metager/ban.txt');
        $bans = [];

        if (file_exists($file)) {
            $tmpBans = json_decode(file_get_contents($file), true);
            if(!empty($tmpBans) && is_array($tmpBans)){
                foreach ($tmpBans as $ban) {
                    #dd($ban["banned-until"]);
                    $bannedUntil = Carbon::createFromFormat('Y-m-d H:i:s', $ban["banned-until"]);
                    if ($bannedUntil->isAfter(Carbon::now())) {
                        $bans[] = $ban;
                    }
                }
            }
        }

        file_put_contents($file, json_encode($bans));

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
