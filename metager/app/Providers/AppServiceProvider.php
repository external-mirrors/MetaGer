<?php

namespace App\Providers;

use App\Localization;
use App\Models\Authorization\LogsAuthGuard;
use App\Models\Authorization\LogsUser;
use App\Models\Logs\LogsAccountProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
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

        // Emit root-relative asset URLs, the way mix() did before Vite replaced it.
        //
        // Vite::asset() otherwise runs through asset(), which builds an absolute URL from the
        // current request. The same application answers on metager.de, metager3.de and the
        // .onion address above, so an absolute URL only ever adds a host that has to match —
        // and a scheme that has to match too, which is why the forceScheme() call above exists
        // at all. A root-relative path cannot get either wrong.
        Vite::createAssetPathsUsing(fn(string $path): string => "/" . ltrim($path, "/"));
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
        $this->app->singleton(LogsAccountProvider::class, function ($app) {
            return new LogsAccountProvider();
        });
        Auth::provider("logs", function ($app, array $config) {
            return new LogsUserProvider($app->make(LogsUser::class));
        });

        Auth::extend('logs', function (Application $app, string $name, array $config) {
            return new LogsAuthGuard(Auth::createUserProvider($config['provider']), $app->make(\Illuminate\Http\Request::class));
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {

    }
}