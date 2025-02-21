<?php

namespace App\Providers;

use App\Models\Authorization\Authorization;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Authorization\TokenAuthorization;
use Illuminate\Support\ServiceProvider;
use Request;
use Cookie;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $key = $this->parseKey();
        // Check if Authorization is done through Token or through Key
        $tokens = Request::header("tokens");

        if ($tokens === null) {
            $tokens = Cookie::get("tokens");
        }

        $decitokens = Request::header("decitokens");
        if ($decitokens === null) {
            $decitokens = Cookie::get("decitokens");
        }

        $tokenauthorization = null;
        if (Request::hasHeader("tokenauthorization")) {
            $tokenauthorization = Request::header("tokenauthorization");
        } else if (Cookie::has("tokenauthorization")) {
            $tokenauthorization = Cookie::get("tokenauthorization");
        }

        $payment_id = Request::input("anonymous-token-payment-id", null);
        if ($payment_id === null) {
            $payment_id = Cookie::get("anonymous-token-payment-id");
        }
        if ($payment_id === null) {
            $payment_id = Request::header("anonymous-token-payment-id");
        }

        $payment_uid = Request::input("anonymous-token-payment-uid", null);
        if ($payment_uid === null) {
            $payment_uid = Cookie::get("anonymous-token-payment-uid");
        }
        if ($payment_uid === null) {
            $payment_uid = Request::header("anonymous-token-payment-uid");
        }

        if ($key === "" && ($tokens !== null || $decitokens !== null || $tokenauthorization !== null)) {
            $this->app->singleton(Authorization::class, function ($app) use ($tokens, $tokenauthorization, $decitokens, $payment_id, $payment_uid) {
                return new TokenAuthorization(tokenString: $tokens, decitokenString: $decitokens, tokenauthorization: $tokenauthorization, payment_id: $payment_id, payment_uid: $payment_uid);
            });
        } else {
            $this->app->singleton(Authorization::class, function ($app) use ($key) {
                return new KeyAuthorization($key);
            });
        }
    }

    private function parseKey(): string
    {
        $key = "";
        if (Cookie::has('key')) {
            $key = Cookie::get('key');
        }
        if (Request::hasHeader("key")) {
            $key = Request::header("key");
        }
        if (Request::filled('key')) {
            $key = Request::input('key');
        }
        return $key;
    }



    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}