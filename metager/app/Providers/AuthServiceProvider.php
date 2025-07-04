<?php

namespace App\Providers;

use App\Authentication\KeyAuthGuard;
use App\Authentication\KeyUserProvider;
use Auth;
use Cookie;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Auth::provider('key-users', function (Application $app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...
            return new KeyUserProvider();

        });
        Auth::extend("key", function ($app, $name, array $config) {
            return new KeyAuthGuard(Auth::createUserProvider($config['provider']));
        });
    }

    private function parseKey(Request $request): string
    {
        $key = "";
        if (Cookie::has('key')) {
            $key = Cookie::get('key');
        }
        if ($request->hasHeader("key")) {
            $key = $request->header("key");
        }
        if ($request->filled('key')) {
            $key = $request->input('key');
        }
        return $key;
    }
}