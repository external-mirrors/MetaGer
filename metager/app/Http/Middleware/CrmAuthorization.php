<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets suma-crm, and nobody else, use /api/blocklist (docs/blocklist-api.md).
 *
 * Modelled on EventAuthorization, including the lesson in its comment: the
 * file is config/metager/metager.php, so the key is `metager.metager.*` with
 * the segment doubled. Read as `metager.crm.admin_token` it would resolve to
 * null and refuse every caller — Tests\Feature\BlocklistApiTest spells the
 * real path out.
 *
 * An unset or empty token refuses everything rather than matching an empty
 * header. The comparison is hash_equals, so its timing says nothing about
 * how much of a guess was right.
 *
 * These are server-to-server calls with no cookies and no Origin, which is why
 * nothing here looks at either: the bearer token is the credential, and a
 * browser never attaches one on its own, so there is nothing for a cross-site
 * request to ride on. The controllers' same-origin checks
 * (Concerns\HandlesKeyCheckout::sameOrigin and its siblings) guard the
 * cookie-carrying forms and are not involved.
 *
 * The request is also marked as wanting JSON, so validation errors, 404s and
 * the like answer in the contract's shapes even for a client that forgot the
 * Accept header, instead of a redirect or an HTML page.
 */
class CrmAuthorization
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set("Accept", "application/json");

        $expected = config("metager.metager.crm.admin_token");
        $given = $request->bearerToken();

        if (!is_string($expected) || $expected === "" || !is_string($given) || !hash_equals($expected, $given)) {
            return response()->json(["error" => "Unauthorized"], 401);
        }

        return $next($request);
    }
}
