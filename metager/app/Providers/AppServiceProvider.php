<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use \Mcamara\LaravelLocalization\Facades\LaravelLocalization;
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
            LaravelLocalization::setLocale('en');
        } else if (stripos($host, "metager.es") !== false) {
            \App::setLocale('es');
            LaravelLocalization::setLocale('es');
        } else {
            \App::setLocale('de');
            LaravelLocalization::setLocale();
        }

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
