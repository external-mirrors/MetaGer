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

        /**
         * The one-time move onto `mg_locale`.
         *
         * A browser that arrives with a language in `web_setting_m` and no
         * `mg_locale` gets the new cookie written here, from the locale just
         * resolved. `web_setting_m` is left exactly as it is — it is a
         * perfectly good market filter and stays one; what ends is its second
         * job. After this request `LocaleContext::cookieLocale()` finds the new
         * cookie and never looks at the old one again.
         */
        if ($this->needsCookieMigration($request)) {
            $context->persistCookie();
        }

        $request = $context->stripLocalePrefix($request);

        // Rebinding 'request' is what tells the URL generator which root it is
        // generating against — Laravel registers a rebinding hook for exactly
        // that. Without it, `route()` would keep formatting against the URL the
        // prefix was still part of.
        app()->instance("request", $request);

        $response = $next($request);

        $this->declareVariance($response);

        return $response;
    }

    /** A browser whose language is still in the old cookie, and only there. */
    private function needsCookieMigration(Request $request): bool
    {
        if ($request->cookie(LocaleContext::cookieName()) !== null) {
            return false;
        }

        return $request->cookie("web_setting_m") !== null
            || $request->header("web_setting_m") !== null;
    }

    /**
     * Say out loud that the page depends on the request headers, because now
     * it really does.
     *
     * An unprefixed URL used to render one language per domain; it now renders
     * whatever `mg_locale` and `Accept-Language` ask for, so a shared cache
     * that keyed on the URL alone would hand one visitor's language to the
     * next. `Accept-Language` was already declared on the redirects that read
     * it (`LocalizationRedirect::verifyPathLocaleNeeded()`); this is the same
     * statement for the responses that read it, plus the cookie that now
     * outranks it.
     *
     * Appended rather than assigned: several routes set `Vary` of their own,
     * and Symfony's `setVary` would replace it.
     */
    private function declareVariance(Response $response): void
    {
        $existing = array_filter(array_map(
            "trim",
            explode(",", (string) $response->headers->get("Vary")),
        ));

        foreach (["Accept-Language", "Cookie"] as $header) {
            if (!in_array(strtolower($header), array_map("strtolower", $existing), true)) {
                $existing[] = $header;
            }
        }

        $response->headers->set("Vary", implode(", ", $existing));
    }
}
