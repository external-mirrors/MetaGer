<?php

namespace App\Providers;

use App\Localization;
use App\Models\Authorization\LogsAuthGuard;
use App\Models\Authorization\LogsUser;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(["app.locale" => "default"]);
        if (Request::getHost() !== "metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion" && (app()->environment("production") || app()->environment("development"))) {
            \URL::forceScheme("https");
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

        $this->app->bind(LogsUser::class, function ($app) {
            return new LogsUser();
        });
        Auth::provider("logs", function ($app, array $config) {
            return new LogsUserProvider($app->make(LogsUser::class));
        });

        Auth::extend('logs', function (Application $app, string $name, array $config) {
            return new LogsAuthGuard(Auth::createUserProvider($config['provider']), $app->make('request'));
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {

    }
}