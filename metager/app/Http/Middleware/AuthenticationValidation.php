<?php

namespace App\Http\Middleware;

use App\Models\Authorization\Authorization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $whitelisted_ips = explode(",", config("metager.metager.unauth_whitelist"));
        if (!app(Authorization::class)->canDoAuthenticatedSearch() && !in_array($request->ip(), $whitelisted_ips)) {
            $parameters = [];
            if ($request->filled("eingabe")) {
                $parameters["eingabe"] = $request->input("eingabe");
            }
            return redirect(route("startpage", $parameters));
        }
        return $next($request);
    }
}
