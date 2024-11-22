<?php

namespace App\Http\Controllers;

use App\Models\Authorization\AnonymousTokenPayment;
use Cache;
use Cookie;
use Illuminate\Contracts\Cache\LockTimeoutException;
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
     */
    public function cost(Request $request)
    {
        $payment_id_paramater = "anonymous_token_payment_id";
        if (!$request->filled($payment_id_paramater) || !uuid_is_valid($request->input($payment_id_paramater))) {
            abort(400);
        }
        $payment_id = $request->input($payment_id_paramater);
        $payment = AnonymousTokenPayment::UNPUBLISH($payment_id);

        if (is_null($payment)) {
            $payment = new AnonymousTokenPayment(0, [], [], $payment_id, null, null);
        }

        Cookie::flushQueuedCookies();
        return response($payment->toJSON(), 200, ["Content_Type" => "application/json", "Cache-Control" => "no-store"]);
    }

    public function pay(Request $request)
    {
        $payment_json = $request->json();
        $payment = AnonymousTokenPayment::fromJSON($request->getContent());
        if ($payment === null) {
            abort(400);
        }

        if (is_null($payment->key)) {
            // Check validity of submitted tokens
            $lock_key = "anonymous_payment:payment_lock:" . $payment_json->get("payment_id", "") . ":" . $payment_json->get("payment_uid", "");
            $lock = Cache::lock($lock_key, 3);
            try {
                $lock->block(3);
                $payment->receive(0);
                $tokens_valid = $payment->checkTokens();

                if ($payment->cost > $payment->getAvailableTokenCount()) {
                    // Either not enough tokens supplied or some of the token were invalid
                    Cookie::flushQueuedCookies();
                    return response($payment->toJSON(), 402, ["Content-Type" => "application/json", "Cache-Control" => "no-store"]);
                } elseif ($tokens_valid === false) {
                    Cookie::flushQueuedCookies();
                    return response($payment->toJSON(), 503, ["Content-Type" => "application/json", "Cache-Control" => "no-store"]);
                }
                $payment->send();
                Cookie::flushQueuedCookies();
                return response($payment->toJSON(), 202, ["Content_Type" => "application/json", "Cache-Control" => "no-store"]);
            } catch (LockTimeoutException $e) {
                abort(400);
            } finally {
                $lock->release();
            }
        } else {
            $lock_key = "anonymous_payment:payment_lock:" . $payment_json->get("payment_id", "") . ":" . $payment_json->get("payment_uid", "");
            $lock = Cache::lock($lock_key, 3);
            try {
                $payment->send();
                Cookie::flushQueuedCookies();
                return response($payment->toJSON(), 202, ["Content_Type" => "application/json", "Cache-Control" => "no-store"]);
            } catch (LockTimeoutException $e) {
                abort(400);
            } finally {
                $lock->release();
            }
        }
    }

    /**
     * Sets the cost for a request using anonymous token
     * @param int $cost - The cost of the request
     * @return void
     */
    public static function SET_COST(int $cost, string $payment_id): string
    {
        $redis_client = self::GET_REDIS_CLIENT();

        $request_id = uniqid('', true);

        $redis_key = self::ANONYMOUS_TOKEN_CACHE_PREFIX . ":" . self::ANONYMOUS_TOKEN_CACHE_COST . ":" . $payment_id;
        if (!uuid_is_valid($payment_id) || $redis_client->exists($redis_key) === 1) {
            throw new \ErrorException("$payment_id is not a valid payment id.");
        }
        $redis_client->rpush($redis_key, $cost);
        $redis_client->expire($redis_key, 600);
        return $request_id;
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
            return json_decode($payment[1], true);
        }
    }

    private static function GET_REDIS_CLIENT(): Connection
    {
        return Redis::connection(config("cache.stores.redis.connection"));
    }
}
