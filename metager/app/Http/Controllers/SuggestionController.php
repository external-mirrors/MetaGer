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
    const SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX = "suggestion:settings:";

    public function suggest(Request $request, string $key = null)
    {
        $query = $request->input("query");

        // Do not generate Suggestions if User turned them off        
        $settings = app(SearchSettings::class);
        if (in_array($settings->suggestion_provider, [null, "off"])) {
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


        $suggestion_provider = $settings->suggestion_provider;


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

    /**
     * Stores user settings regarding suggestions serverside
     * Those are used when using suggestions in the address bar
     * 
     * Conditions:
     * 1. Request is authorized using a key
     * 2. User enabled location bar suggestions in settings
     * 
     * @return bool - True if settings were updated and false if any precondition is not met
     */
    public static function UPDATE_SERVER_SETTINGS(): bool
    {
        $search_settings = app(SearchSettings::class);
        $authorization = app(Authorization::class);

        if (!($authorization instanceof KeyAuthorization) || empty($authorization->getToken()) || $authorization->availableTokens <= 0) {
            return false;
        }

        if ($search_settings->suggestion_locationbar !== true) {
            if (Cache::has(self::SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX . $authorization->getToken())) {
                Cache::forget(self::SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX . $authorization->getToken());
            }
            return false;
        }

        $settings = [
            "suggestion_provider" => $search_settings->suggestion_provider,
            "suggestion_delay" => $search_settings->suggestion_delay,
            "suggestion_locationbar" => $search_settings->suggestion_locationbar
        ];

        Cache::put(self::SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX . $authorization->getToken(), $settings, now()->addMonth());
        return true;
    }

    public static function LOAD_SERVER_SETTINGS(): array|null
    {
        $search_settings = app(SearchSettings::class);
        $authorization = app(Authorization::class);

        if (!($authorization instanceof KeyAuthorization) || empty($authorization->getToken())) {
            return null;
        }

        if ($search_settings->suggestion_locationbar !== true) {
            if (Cache::has(self::SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX . $authorization->getToken())) {
                Cache::forget(self::SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX . $authorization->getToken());
            }
            return null;
        }
        $settings = Cache::get(self::SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX . $authorization->getToken());
        if ($settings !== null) {
            Cache::put(self::SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX . $authorization->getToken(), $settings, now()->addMonth());
        } else if ($authorization->availableTokens <= 0) {
            return [
                "suggestion_locationbar" => false
            ];
        }
        return $settings;
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