<?php

namespace App\Providers;

use App\MetaGer;
use App\Models\Verification\HumanVerification;
use App\QueryLogger;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use App\QueryTimer;
use App\Searchengines;
use App\SearchSettings;

class MetaGerProvider extends ServiceProvider
{
    /**
     * @var float $start_time
     */
    private $start_time;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->start_time = microtime(true);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * @param App $app
         */
        $this->app->singleton(MetaGer::class, function ($app) {
            $app->make(QueryLogger::class);
            return new MetaGer();
        });

        $this->app->singleton(QueryLogger::class, function ($app) {
            return new QueryLogger();
        });

        $this->app->singleton(QueryTimer::class, function ($app) {
            return new QueryTimer();
        });

        $this->app->singleton(HumanVerification::class, function ($app) {
            return new HumanVerification();
        });
    }

// public function provides()
// {
//     return [
//         MetaGer::class,
//         QueryLogger::class,
//         QueryTimer::class,
//     ];
// }
}