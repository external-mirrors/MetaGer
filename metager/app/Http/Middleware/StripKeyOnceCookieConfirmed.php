<?php

namespace App\Http\Middleware;

use App\Authentication\CookieSupport;
use App\Landing\AppCallback;
use App\Localization;
use Closure;
use Illuminate\Http\Request;

/**
 * The clean half of the one-hop key check.
 *
 * `CookieSupport::withKeyCheck()` puts `key` and the one-time marker on the
 * URL a visitor is about to land on and see in their address bar, because at
 * the moment that redirect is built there is no way yet to know whether the
 * cookie just queued alongside it will actually survive the round trip — that
 * is only knowable once the *next* request arrives. For the common case, a
 * browser that keeps the cookie fine, that next request is this one: the
 * marker is present and the cookie already is too. Bounce once more, quietly,
 * to the same page without `key` or the marker, so the page the visitor
 * actually settles on — and might bookmark, share, or see in a Referer header
 * — never carries their credential. A cookie-blind visitor's cookie genuinely
 * is not there yet, so none of this fires for them: the request falls
 * through unchanged, and `CookieSupport::justAuthenticatedWithoutCookie()`
 * shows the notice and lets the key ride on, exactly as before this existed.
 *
 * Global ("web" group), not route middleware: the marker can land on any of
 * several destinations — the startpage, the account page, an arbitrary
 * same-origin `redirect_success` — decided by whichever caller of
 * `withKeyCheck()` built this redirect, not by a route this middleware could
 * be attached to instead.
 *
 * Excluded: an app handback (`AppCallback::isHandback()`).
 * `AccountController::show()`'s own custom-tab branch needs the actual key
 * value to build the verified App Link the key travels back on, whether or
 * not the cookie survived — bouncing it away before that branch ever runs
 * would mean the app never gets its key back.
 */
class StripKeyOnceCookieConfirmed
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->query(CookieSupport::MARKER) !== "1"
            || $request->query("key") === null
            || $request->cookie("key") === null
            || AppCallback::isHandback($request)) {
            return $next($request);
        }

        $params = $request->query();
        unset($params["key"], $params[CookieSupport::MARKER]);

        $url = Localization::currentUrl();
        if ($params !== []) {
            $url .= "?" . http_build_query($params);
        }

        // Same privacy header the branches that first put `key` here already
        // set: the request this middleware is answering still names it, even
        // though the response points away from it.
        return redirect($url)->header("Cache-Control", "no-store, private");
    }
}
