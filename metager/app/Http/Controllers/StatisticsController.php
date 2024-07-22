<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;


class StatisticsController extends Controller
{
    public function pageLoad(Request $request)
    {
        $params = [
            "idsite" => config("metager.matomo.site_id"),
            "token_auth" => config("metager.matomo.token_auth"),
            "rand" => md5(microtime(true)),
            "rec" => "1",
            "send_image" => "0",
            "cip" => $request->ip(),
            "_id" => substr(md5($request->ip() . now()->format("Y-m-d")), 0, 16)
        ];
        $http_params = $request->all();

        // Useragent
        $params["ua"] = $request->userAgent();
        // Accept-Language
        $params["lang"] = $request->header("Accept-Language");
        $params = array_merge($http_params, $params);   // Merge arrays keeping our serverside defined options if key is set multiple times

        self::LOG_STATISTICS($params);
    }

    public static function LOG_STATISTICS(array $params)
    {
        if (!config("metager.matomo.enabled") || config("metager.matomo.url") === null)
            return;

        $params = array_merge($params, [
            "idsite" => config("metager.matomo.site_id"),
            "token_auth" => config("metager.matomo.token_auth"),
            "rand" => md5(microtime(true)),
            "rec" => "1",
            "send_image" => "0",
            "cip" => \Request::ip(),
            "_id" => substr(md5(\Request::ip() . now()->format("Y-m-d")), 0, 16)
        ]);

        $url = config("metager.matomo.url") . "/matomo.php?" . http_build_query($params);

        // Submit fetch job to worker
        $hash = hash("sha256", \json_encode($params));
        $mission = [
            "resulthash" => $hash,
            "url" => $url,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => null,
            "password" => null,
            "cacheDuration" => 0,
            "name" => "Matomo"
        ];
        $mission = json_encode($mission);

        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    }

}
