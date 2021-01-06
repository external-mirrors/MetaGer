<?php

namespace App\Models;

use Cache;
use Illuminate\Support\Facades\Redis;
use Log;
use Request;
use LaravelLocalization;

class Adgoal
{
    const COUNTRIES = ["af","al","dz","um","as","vi","ad","ao","ai","ag","ar","am","aw","az","au","eg","gq","et","bs",
        "bh","bd","bb","be","bz","bj","bm","bt","bo","ba","bw","bv","br","vg","io","bn","bg","bf","bi","cl","cn","ck",
        "cr","ci","dk","de","dm","do","dj","ec","sv","er","ee","eu","fk","fo","fj","fi","fr","gf","pf","tf","ga","gm",
        "ge","gh","gi","gd","gr","gb","uk","gl","gp","gu","gt","gn","gw","gy","ht","hm","hn","hk","in","id","iq","ir",
        "ie","is","il","it","jm","sj","jp","ye","jo","yu","ky","kh","cm","ca","cv","kz","qa","ke","kg","ki","cc","co",
        "km","cg","cd","hr","cu","kw","la","ls","lv","lb","lr","ly","li","lt","lu","mo","mg","mw","my","mv","ml","mt",
        "mp","ma","mh","mq","mr","mu","yt","mk","mx","fm","md","mc","mn","ms","mz","mm","na","nr","np","nc","nz","ni",
        "nl","an","ne","ng","nu","kp","nf","no","om","tp","at","pk","pw","ps","pa","pg","py","pe","ph","pn","pl","pt",
        "pr","re","rw","ro","ru","st","sb","zm","ws","sm","sa","se","ch","sn","sc","sl","zw","sg","sk","si","so","es",
        "lk","sh","kn","lc","pm","vc","sd","sr","za","kr","sz","sy","tj","tw","tz","th","tg","to","tt","td","cz","tn",
        "tm","tc","tv","tr","us","ug","ua","xx","hu","uy","uz","vu","va","ve","ae","vn","wf","cx","by","eh","ww","zr","cf","cy",];

    public static function startAdgoal(&$results)
    {
        $publicKey = getenv('adgoal_public');
        $privateKey = getenv('adgoal_private');
        if ($publicKey === false) {
            return true;
        }
        $linkList = "";
        foreach ($results as $result) {
            if (!$result->new) {
                continue;
            }
            $link = $result->link;
            if (strpos($link, "http") !== 0) {
                $link = "http://" . $link;
            }
            $linkList .= $link . ",";
        }
    
        $linkList = rtrim($linkList, ",");
    
        # Hashwert
        $hash = md5($linkList . $privateKey);
    
        $link = "https://xf.gdprvalidate.de/v4/check";
    
        # Which country to use
        # Will be de for metager.de and en for metager.org
        $country = "de";
        if (LaravelLocalization::getCurrentLocale() === "en") {
            $country = "us";
        }
        $preferredLanguage = Request::getPreferredLanguage();
        if (!empty($preferredLanguage)) {
            if (str_contains($preferredLanguage, "_")) {
                $preferredLanguage = substr($preferredLanguage, stripos($preferredLanguage, "_")+1);
            } elseif (str_contains($preferredLanguage, "-")) {
                $preferredLanguage = substr($preferredLanguage, stripos($preferredLanguage, "-")+1);
            }

            $preferredLanguage = strtolower($preferredLanguage);

            if (in_array($preferredLanguage, self::COUNTRIES)) {
                $country = $preferredLanguage;
            }
        }
    
        $postfields = [
                "key" => $publicKey,
                "panel" => "ZMkW9eSKJS",
                "member" => "338b9Bnm",
                "signature" => $hash,
                "links" => $linkList,
                "country" => $country,
            ];
    
        // Submit fetch job to worker
        $mission = [
                "resulthash" => $hash,
                "url" => $link,
                "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                "username" => null,
                "password" => null,
                "headers" => [
                    "Content-Type" => "application/x-www-form-urlencoded"
                ],
                "cacheDuration" => 60,
                "name" => "Adgoal",
                "curlopts" => [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => \http_build_query($postfields)
                ]
            ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    
        return $hash;
    }
    
    public static function parseAdgoal(&$results, $hash, $waitForResult)
    {
        # Wait for result
        $startTime = microtime(true);
        $answer = null;
    
        # Hash is true if Adgoal request wasn't started in the first place
        if ($hash === true) {
            return true;
        }
            
        if ($waitForResult) {
            while (microtime(true) - $startTime < 5) {
                $answer = Cache::get($hash);
                if ($answer === null) {
                    usleep(50 * 1000);
                } else {
                    break;
                }
            }
        } else {
            $answer = Cache::get($hash);
        }
        if ($answer === null) {
            return false;
        }
            
        try {
            $answer = json_decode($answer, true);

            foreach ($answer as $partnershop) {
                $targetUrl = $partnershop["url"];
    
                $tld = $partnershop["tld"];
                $targetHost = parse_url($targetUrl, PHP_URL_HOST);

                /*
                    Adgoal sometimes returns affiliate Links for every URL
                    That's why we check if the corresponding TLD matches the orginial URL
                */
                if($targetHost !== false && stripos($targetHost, $tld) === false){
                    continue;
                }

                foreach ($results as $result) {
                    if ($result->link === $targetUrl && !$result->partnershop) {
                        # Ein Advertiser gefunden
                        if ($result->image !== "") {
                            $result->logo = $partnershop["logo"];
                        } else {
                            $result->image = $partnershop["logo"];
                        }
    
                        # Den Link hinzufügen:
                        $result->link = $partnershop["click_url"];
                        $result->partnershop = true;
                        $result->changed = true;
                    }
                }
            }
        } catch (\ErrorException $e) {
            Log::error($e->getMessage());
        } finally {
            $requestTime = microtime(true) - $startTime;
            \App\PrometheusExporter::Duration($requestTime, "adgoal");
        }
        return true;
    }
}
