<?php

namespace App\Http\Middleware;

use App\Localization\LocaleContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Decides the request's locale, and takes it out of the path.
 *
 * Registered globally, in `bootstrap/app.php`'s `$middleware->use([...])`,
 * because it has to run *before* route matching: everything downstream — the
 * router, `route()`, `$request->path()`, `LocalizationRedirect` — is entitled to
 * assume the locale is already known and the URL no longer mentions it.
 *
 * Last in that list on purpose. `TrustProxies` has to have run first or
 * `getHost()` reports the load balancer rather than the domain the user typed,
 * and the host is (still) one of the inputs to the decision.
 */
class ResolveLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = LocaleContext::resolve($request);

        app()->instance(LocaleContext::class, $context);
        $context->apply();

        $request = $context->stripLocalePrefix($request);

        // Rebinding 'request' is what tells the URL generator which root it is
        // generating against — Laravel registers a rebinding hook for exactly
        // that. Without it, `route()` would keep formatting against the URL the
        // prefix was still part of.
        app()->instance("request", $request);

        return $next($request);
    }
}
