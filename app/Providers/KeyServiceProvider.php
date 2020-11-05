<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Request;
use Cookie;
use App\Models\Key;

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
        if(Cookie::has('key')) {
            $key = Cookie::get('key');
        }
        if(Request::filled('key')) {
            $key = Request::input('key');
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
