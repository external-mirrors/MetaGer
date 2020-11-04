<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Request;
use Cookie;

class KeyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $key = "";
        if(Cookie::get('key')) {
            $key = Cookie::get('key');
        }
        if(isset($request->key)) {
            $key = $request->key;
        }
        $this->app->singleton(Key::class, function ($app) use ($key) {
            return new Key($key);
        });
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
