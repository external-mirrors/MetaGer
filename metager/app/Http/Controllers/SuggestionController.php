<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Authorization\Authorization;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Authorization\TokenAuthorization;
use App\Models\Result;
use App\SearchSettings;
use App\Suggestions;
use Cache;
use Carbon;
use Crypt;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Predis\Pipeline\Pipeline;

class SuggestionController extends Controller
{
    const CACHE_DURATION_HOURS = 6;
    const SUGGESTION_SETTINGS_SERVER_STORAGE_PREFIX = "suggestion:settings:";

    public function suggest(Request $request, string $key = null)
    {
        $query = $request->input("query");

        // Do not generate Suggestions if User turned them off        
        $settings = app(SearchSettings::class);
        if (empty($query) || in_array($settings->suggestion_provider, [null, "off"])) {
            return response()->json([], 200, ["Cache-Control" => "no-cache, private"]);
        }

        $suggestion_provider = $settings->suggestion_provider;

        $cache_key = "suggestion:cache:$suggestion_provider:$query";
        if (Cache::has($cache_key) && 1 == 0) { // ToDo reenable cache
            return response()->json(Cache::get($cache_key), 200, ["Cache-Control" => "max-age=7200"]);
        } else {
            $suggestions = Suggestions::fromProviderName($suggestion_provider, $query);
            $authorization = app(Authorization::class);
            $authorization->setCost($suggestions->cost);

            $start_time = now();
            if (!$authorization->canDoAuthenticatedSearch(true)) {
                return response()->json(["error" => "Payment Required", "cost" => $authorization->getCost()], 402);
            }

            $token_data = [];
            if ($authorization instanceof TokenAuthorization) {
                $token_data["tokens"] = $authorization->getToken()->tokens;
                $token_data["decitokens"] = $authorization->getToken()->decitokens;
            }
            try {
                switch ($this->delay($start_time)) {
                    case 402:   // Payment Required = Not enough or invalid Token
                        return response()->json(array_merge(["error" => "Payment Required", "cost" => $authorization->getCost()], $token_data), 402);
                    case 423:
                        return response()->json(["error" => "Aborted because of newer request"], 423);
                    case 200:
                        $authorization->makePayment($authorization->getCost());
                        if ($authorization instanceof TokenAuthorization) {
                            $token_data["tokens"] = $authorization->getToken()->tokens;
                            $token_data["decitokens"] = $authorization->getToken()->decitokens;
                        }
                        break;
                    default:
                        return response()->json(["error" => "Unexpected delay status code"], 500);
                }
                $suggestions->fetch();
            } catch (Exception $e) {
                return response()->json(array_merge(["error" => $e->getMessage()], $token_data), 500);
            }

            $suggestion_response = array_merge($suggestions->toJSON(), $token_data);
            Cache::put($cache_key, $suggestion_response, now()->addDay());

            return response()->json($suggestion_response, 200, ["Cache-Control" => "max-age=7200"]);
        }
    }

    public function tokenCost(Request $request)
    {
        $authorization = app(Authorization::class);
        $token_cost = 0;
        if ($authorization instanceof TokenAuthorization) {
            $settings = app(SearchSettings::class);
            if ($settings->suggestion_provider !== null) {
                $suggestions = Suggestions::fromProviderName($settings->suggestion_provider, "");
                $token_cost = $suggestions->cost;
            }
        }
        return response()->json(["tokencost" => $token_cost]);
    }

    public function cancelSuggest(Request $request)
    {
        $suggest_group = $this->generateSuggestCacheKey();
        $this->disableSuggestionGroup($suggest_group);
        $list = $this->getSuggestionGroupList($suggest_group);
        foreach ($list as $uuid) {
            $this->abortSuggestionGroupRequest($uuid, 423);
        }
        return response()->json(["status" => "ok"]);
    }

    /**
     * Suggestions will load after a configured time to not
     * load them everytime a user enters a letter but rather
     * when he pauses/stops typing.
     * 
     * This function accounts for race conditions.
     * 
     * @return int Status code of response
     */
    private function delay(\Illuminate\Support\Carbon|null $start_time): int
    {
        if ($start_time === null) {
            $start_time = now();
        }
        $settings = app(SearchSettings::class);
        $authorization = app(Authorization::class);

        $uuid = \Request::header("number", microtime(true));
        $list = [];

        /**
         * Groups all suggest requests to prevent unnecessary requests
         * @var string
         */
        $cache_key = $this->generateSuggestCacheKey();
        if ($this->isSuggestionGroupDisabled($cache_key))
            return 423;
        // 
        $expiration = clone $start_time;
        $expiration->addMilliseconds($settings->suggestion_delay);
        $list = $this->addSuggestGroupRequest($cache_key, $uuid, $expiration);

        // Abort all but the newest request
        if (sizeof($list) > 0) {
            $newest = max($list);
            foreach ($list as $suggest_request) {
                if ($suggest_request !== $newest) {
                    $this->abortSuggestionGroupRequest($suggest_request, 423, $expiration);
                }
            }
        }

        $delay_result = Redis::blpop("suggest:delay:request:$uuid", now()->diffInMilliseconds($expiration, true) / 1000);
        if ($delay_result !== null) {
            return filter_var($delay_result[1], FILTER_VALIDATE_INT);
        } else {
            if ($authorization instanceof TokenAuthorization && sizeof($list) > 0) {
                // Abort all other requests that use this token because it will be used up by this request
                foreach ($list as $suggest_request) {
                    if ($suggest_request !== $newest) {
                        $this->abortSuggestionGroupRequest($suggest_request, 402, $expiration);
                    }
                }
            }
            return 200;
        }
    }


    private function getSuggestionGroupList($suggest_group): array
    {
        return Redis::lrange($suggest_group, 0, -1);
    }

    private function abortSuggestionGroupRequest($uuid, $status_code = 423, \Illuminate\Support\Carbon $expiration = null)
    {
        if ($expiration == null) {
            $expiration = now();
            $expiration->addMilliseconds(app(SearchSettings::class)->suggestion_delay);
        }
        $key = "suggest:delay:request:$uuid";
        Redis::rpush($key, $status_code);
        Redis::pexpireat($key, $expiration->getTimestampMs());
    }

    private function addSuggestGroupRequest($suggest_group, $uuid, \Illuminate\Support\Carbon $expiration = null): array
    {
        if ($expiration == null) {
            $expiration = now();
            $expiration->addMilliseconds(app(SearchSettings::class)->suggestion_delay);
        }
        $result = Redis::pipeline(function (Pipeline $pipe) use ($suggest_group, $uuid, $expiration) {
            $pipe->rpush($suggest_group, $uuid);
            $pipe->lrange($suggest_group, 0, -1);
            $pipe->pexpireat($suggest_group, $expiration->getTimestampMs());
        });

        // Abort all but the newest request
        return $result[1];
    }

    private function isSuggestionGroupDisabled($suggest_group): bool
    {
        return filter_var(Redis::get("suggest:group:block:$suggest_group"), FILTER_VALIDATE_BOOLEAN);
    }

    private function disableSuggestionGroup($suggest_group, \Illuminate\Support\Carbon $expiration = null)
    {
        if ($expiration == null) {
            $expiration = now();
            $expiration->addMilliseconds(app(SearchSettings::class)->suggestion_delay);
        }
        Redis::psetex("suggest:group:block:$suggest_group", intval(now()->diffInMilliseconds($expiration, true)), true);
    }

    /**
     * Generates a temporary grouping ID between suggest requests so that additional
     * requests can be aborted
     * @return string
     */
    private function generateSuggestCacheKey(): string
    {
        $authorization = app(Authorization::class);
        $suggest_request_id = \Request::header("id");
        if ($suggest_request_id !== null) {
            $cache_key = $suggest_request_id;
        } else if ($authorization instanceof KeyAuthorization) {
            $cache_key = $authorization->getToken();
        } else if ($authorization instanceof TokenAuthorization) {
            /**
             * @var \App\Models\Authorization\AnonymousTokenPayment
             */
            $token_payment = $authorization->getToken();
            $cache_key = "";
            foreach ($token_payment->tokens as $token) {
                $cache_key .= $token->token;
            }
            foreach ($token_payment->decitokens as $token) {
                $cache_key .= $token->token;
            }
        } else {
            $cache_key = \Request::ip() . \Request::userAgent();
        }
        $cache_key = md5($cache_key);
        return $cache_key;
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