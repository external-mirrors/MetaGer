<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use Queue;
use Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        /**
         * metager.org is our english Domain
         * We will change the Locale to en
         */
        $host = Request::header("X_Forwarded_Host", "");
        if (empty($host)) {
            $host = Request::header("Host", "");
        }

        if (stripos($host, "metager.org") !== false) {
            \App::setLocale('en');
        }
        if (stripos($host, "metager.es") !== false) {
            \App::setLocale('es');
        }

        \Prometheus\Storage\Redis::setDefaultOptions(
            [
                'host' => config("database.redis.default.host"),
                'port' => intval(config("database.redis.default.port")),
                'password' => config("database.redis.default.password"),
                'timeout' => 0.1, // in seconds
                'read_timeout' => '10', // in seconds
                'persistent_connections' => false
            ]
        );

        Queue::before(function (JobProcessing $event) {
        });
        Queue::after(function (JobProcessed $event) {
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
