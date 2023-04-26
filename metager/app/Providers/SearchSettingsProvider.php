<?php

namespace App\Providers;

use App\Models\Configuration\Searchengines;
use App\SearchSettings;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class SearchSettingsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SearchSettings::class, function ($app) {
            return new SearchSettings();
        });

        $this->app->singleton(Searchengines::class, function (Application $app) {
            return new Searchengines();
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

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [SearchSettings::class, Searchengines::class];
    }
}