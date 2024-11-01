<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class AnonymousToken extends Controller
{
    const ANONYMOUS_TOKEN_CACHE_PREFIX = "anonymous_token_payment_id";
    const ANONYMOUS_TOKEN_CACHE_COST = "cost";
    const ANONYMOUSE_TOKEN_CACHE_PAYMENT = "payment";

    /**
     * A client using anonymous token can use this route to retrieve the cost of a specific action
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function cost(Request $request)
    {
        $payment_id_paramater = "anonymous_token_payment_id";
        if (!$request->filled($payment_id_paramater) || !uuid_is_valid($request->input($payment_id_paramater))) {
            abort(400);
        }
        $payment_id = $request->input($payment_id_paramater);
        $cost = (int) self::GET_REDIS_CLIENT()->blpop(self::ANONYMOUS_TOKEN_CACHE_PREFIX . ":" . self::ANONYMOUS_TOKEN_CACHE_COST . ":" . $payment_id, 5);
        return response()->json(["cost" => $cost, "token" => ["token" => [], "decitoken" => []]]);
    }

    public function pay(Request $request)
    {

    }

    /**
     * Sets the cost for a request using anonymous token
     * @param int $cost - The cost of the request
     * @return void
     */
    public static function SET_COST(int $cost, string $payment_id)
    {
        $redis_client = self::GET_REDIS_CLIENT();
        $redis_key = self::ANONYMOUS_TOKEN_CACHE_PREFIX . ":" . self::ANONYMOUS_TOKEN_CACHE_COST . ":" . $payment_id;
        if (!uuid_is_valid($payment_id) || $redis_client->exists($redis_key) === 1) {
            throw new \ErrorException("$payment_id is not a valid payment id.");
        }
        $redis_client->rpush($redis_key, $cost);
        $redis_client->expire($redis_key, 600);
    }

    /**
     * Retrieves payment information which was previously inserted by the `pay` route
     * @param string $payment_id
     * @return array|null
     */
    public static function GET_PAYMENT(string $payment_id): array|null
    {
        $payment = self::GET_REDIS_CLIENT()->blpop(self::ANONYMOUS_TOKEN_CACHE_PREFIX . ":" . self::ANONYMOUSE_TOKEN_CACHE_PAYMENT . ":" . $payment_id, 30);
        if ($payment === null) {
            return null;
        } else {
            return json_decode($payment[1]);
        }
    }

    private static function GET_REDIS_CLIENT(): Connection
    {
        return Redis::connection(config("cache.stores.redis.connection"));
    }
}
