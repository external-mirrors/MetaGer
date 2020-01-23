<?php

namespace App;

use Illuminate\Support\Facades\Redis;

class CacheHelper
{

    /**
     * MetaGer uses a pretty slow harddrive for the configured cache
     * That's why we have some processes running to write cache to disk in parallel
     */
    public static function put($key, $value, $timeSeconds)
    {
        $cacherItem = [
            'timeSeconds' => $timeSeconds,
            'key' => $key,
            'value' => $value,
        ];
        Redis::rpush(\App\Console\Commands\RequestCacher::CACHER_QUEUE, base64_encode(serialize($cacherItem)));

    }
}
