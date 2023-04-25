<?php

namespace App\Providers;

use App\Localization;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->routesAreCached()) {
            // Apply the correct locale configuration
            // This is also done in RouteServiceProvider if routes are not cached
            Localization::setLocale();
        }

        \Prometheus\Storage\Redis::setDefaultOptions(
            [
                'host' => config("database.redis.default.host"),
                'port' => config("database.redis.default.port"),
                'password' => config("database.redis.default.password"),
                'timeout' => 0.1,
                // in seconds
                'read_timeout' => '10',
                // in seconds
                'persistent_connections' => false
            ]
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}