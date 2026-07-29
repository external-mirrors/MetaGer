<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Rejects cross-site state-changing requests, standing in for CSRF tokens.
 *
 * MetaGer cannot use Laravel's CSRF protection here: `VerifyCsrfToken` and `StartSession` are both
 * removed globally in bootstrap/app.php, deliberately — ordinary browsing sets no session cookie.
 * A CSRF token needs somewhere to live, so token-based protection would mean introducing a session
 * cookie for chat users, which is a privacy cost this route does not justify.
 *
 * What actually protects the chat route is layered:
 *
 *  1. The `key` cookie is SameSite=Lax (set by metager-keymanager), so a cross-site POST does not
 *     carry it at all — the attack of "make a victim's browser spend their key on my prompt" fails
 *     before reaching us.
 *  2. The header and query key paths need either JS (CORS-blocked cross-origin) or prior knowledge
 *     of the key, in which case the attacker doesn't need the victim's browser.
 *  3. This middleware, as defense in depth against a future change to either of the above.
 *
 * Deliberately permissive when neither header is present: that means a non-browser client such as
 * curl, which carries no ambient credentials and can only send what its operator chose to send. A
 * browser cannot be made to omit `Origin` on a cross-origin POST, so this does not create a bypass.
 */
class SameOriginRequest
{
    public function handle(Request $request, Closure $next)
    {
        $fetchSite = $request->header("Sec-Fetch-Site");
        if ($fetchSite !== null) {
            if (!in_array($fetchSite, ["same-origin", "same-site", "none"], true)) {
                abort(403, "Cross-site requests are not allowed for this endpoint.");
            }
            return $next($request);
        }

        $origin = $request->header("Origin");
        if ($origin !== null && parse_url($origin, PHP_URL_HOST) !== $request->getHost()) {
            abort(403, "Cross-site requests are not allowed for this endpoint.");
        }

        return $next($request);
    }
}
