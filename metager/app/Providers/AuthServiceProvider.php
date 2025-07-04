<?php

namespace App\Providers;

use App\Authentication\KeyUser;
use Auth;
use Cookie;
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

        Auth::viaRequest("key", function ($request) {
            $key = $this->parseKey($request);
            if ($key !== "") {
                return null;
            }
            return new KeyUser($key);
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