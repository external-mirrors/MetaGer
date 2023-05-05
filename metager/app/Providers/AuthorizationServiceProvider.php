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
        // Check if Authorization is done through Token or through Key
        $tokens = Request::header(("tokens"));
        if ($tokens === null) {
            $tokens = Cookie::get("tokens");
        }
        if ($tokens === null && Cookie::has("tokenauthorization") && !Cookie::has("key")) {
            $tokens = Cookie::get("tokenauthorization");
        }
        if ($tokens !== null) {
            $this->app->singleton(Authorization::class, function ($app) use ($tokens) {
                return new TokenAuthorization($tokens);
            });
        } else {
            $key = "";
            if (Cookie::has('key')) {
                $key = Cookie::get('key');
            }
            if (Request::filled('key')) {
                $key = Request::input('key');
            }
            $this->app->singleton(Authorization::class, function ($app) use ($key) {
                return new KeyAuthorization($key);
            });
        }
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