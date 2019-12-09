<?php

namespace App;

class CacheHelper
{

    /**
     * MetaGer uses a pretty slow harddrive for the configured cache
     * That's why we have some processes running to write cache to disk in parallel
     */
    public static function put($key, $value, $timeSeconds)
    {
        $cacherItem = [
            'timeSeconds' => $time,
            'key' => $key,
            'value' => $value,
        ];
        Redis::rpush(\App\Console\Commands\RequestCacher::CACHER_QUEUE, json_encode($cacherItem));

    }
}
