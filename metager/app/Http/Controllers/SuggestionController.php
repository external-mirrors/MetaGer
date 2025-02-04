<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Authorization\Authorization;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Result;
use App\SearchSettings;
use App\Suggestions;
use Cache;
use Crypt;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SuggestionController extends Controller
{
    const CACHE_DURATION_HOURS = 6;

    public function suggest(Request $request)
    {
        $query = $request->input("query");

        // Do not generate Suggestions if User turned them off        
        $settings = app(SearchSettings::class);
        if (in_array($settings->suggestions, [null, "off"])) {
            return response()->json([], 200, ["Cache-Control" => "no-cache, private"]);
        }

        /**
         * Delay implementation to prevent unnecessary suggestion requests while typing.
         * The response will block for some time and any other following request will
         * prevent the first one to be executed
         */
        $delay = 0.6;
        $cache_key = $request->ip() . $request->userAgent();
        if (app(Authorization::class) instanceof KeyAuthorization) {
            $cache_key .= app(Authorization::class)->getToken();
        }
        $cache_key = "suggest:" . md5($cache_key);

        Redis::rpush($cache_key, $query);
        Redis::del($cache_key);
        $delay_result = Redis::blpop($cache_key, $delay);
        if ($delay_result !== null) {
            return response()->json(["error" => "Aborted because of newer request"], 423);
        }


        $suggestion_provider = $settings->suggestions;


        $cache_key = "suggestion:cache:$suggestion_provider:$query";
        if (Cache::has($cache_key)) {
            response()->json(Cache::get($cache_key), 200, ["Cache-Control" => "max-age=7200"]);
        } else {
            $suggestions = Suggestions::fromProviderName($suggestion_provider, $query);
            $suggestions->fetch();

            $suggestion_response = $suggestions->toJSON();
            Cache::put($cache_key, $suggestion_response, now()->addDay());

            return response()->json($suggestion_response, 200, ["Cache-Control" => "max-age=7200"]);
        }
    }

    private function verifySignature(Request $request): bool
    {
        $key = $request->header("MetaGer-Key", "");
        try {
            $expiration = Crypt::decrypt($key);
            if (now()->isAfter($expiration)) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}