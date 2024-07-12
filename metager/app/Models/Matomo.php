<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use Request;

class Matomo
{
    static function PAGE_VISIT()
    {
        if (!config("metager.matomo.enabled") || config("metager.matomo.url") === null || request()->is("health-check/*"))
            return;
        if (request()->is("meta/meta.ger3") && request()->filled("mgv"))
            return;
        $params = [
            "idsite" => config("metager.matomo.site_id"),
            "token_auth" => config("metager.matomo.token_auth"),
            "rand" => md5(microtime(true)),
            "rec" => "1",
            "send_image" => "0",
            "cip" => request()->ip(),
        ];
        // Page URL
        $url = request()->getPathInfo();
        if (stripos($url, "/img") === 0 || stripos($url, "/meta/meta.ger3") === 0 || stripos($url, "/meta/loadMore") === 0 || preg_match("/\.css$/", $url) || preg_match("/csp-report$/", $url))
            return;
        $url = request()->schemeAndHttpHost() . preg_replace("/^\/[a-z]{2}-[A-Z]{2}/", "", $url);
        $params["url"] = $url;
        // Referer
        $params["urlref"] = request()->headers->get("referer");
        // Useragent
        $params["ua"] = request()->userAgent();

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