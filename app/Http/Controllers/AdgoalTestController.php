<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use stdClass;
use Cache;
use Illuminate\Support\Facades\Redis;



class AdgoalTestController extends Controller
{
    public function index(Request $request){
        return view('admin.adgoal')
            ->with('title', "Admin Adgoal - MetaGer");
    }

    public function post(Request $request){
        $urls = explode("\r\n", $request->input("urls"));

        // Bring it in a form that's similar to metager results
        $mgUrls = array();

        foreach($urls as $url){
            $result = new stdClass();
            $result->link = $url;
            $result->new = true;
            $mgUrls[] = $result;
        }

        $resultHash = \App\Models\Adgoal::startAdgoal($mgUrls);
        Cache::forget($resultHash);

        $startTime = microtime(true);
        $answer = null;
        while (microtime(true) - $startTime < 5) {
            $answer = Cache::get($resultHash);
            if ($answer === null) {
                usleep(50 * 1000);
            } else {
                break;
            }
        }

        if(!empty($answer)){
            $answer = json_decode($answer, true);
            $answer = json_encode($answer, JSON_PRETTY_PRINT);
        }

        return view("admin.adgoal")
            ->with('title', 'Admin Adgoal - MetaGer')
            ->with('urls', $request->input("urls"))
            ->with('answer', $answer);
    }

    public function generateUrls(Request $request){
        $eingabe = $request->input('eingabe');
        if(empty($eingabe)){
            return redirect('admin/adgoal');
        }

        $url = route('resultpage', ["eingabe" => $eingabe, "out" => "api", "key" => config("metager.metager.keys.uni_mainz")]);
        $hash = md5($url);

        $mission = [
            "resulthash" => $hash,
            "url" => $url,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => null,
            "password" => null,
            "cacheDuration" => 60,
            "name" => "AdgoalGen",
        ];

        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

        $results = Redis::brpop($hash, 10);
        $linklist = array();
        if(!empty($results) && is_array($results) && sizeof($results) === 2){
            $results = $results[1];
            $results = \simplexml_load_string($results);
            foreach($results->entry as $entry){
                $linklist[] = $entry->link["href"]->__toString();
            }
        }

        return view("admin.adgoal")
            ->with('title', 'Admin Adgoal - MetaGer')
            ->with('urls', implode("\r\n", $linklist));
    }
}
