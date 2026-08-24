<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // config/metager/metager.php, so the key is metager.metager.* — the
        // nested directory doubles the segment. Read as `metager.event_authorization`
        // this resolved to null, and since bearerToken() returns a string whenever
        // the header is present, no caller could ever match: every external event
        // push was answered with a 401. The keymanager posts these with fetch(),
        // which does not reject on a 4xx, so the failure was completely silent and
        // the browser extension simply stopped receiving KeyChanged.
        $expected = config('metager.metager.event_authorization');

        $authorization = $request->bearerToken();
        if ($expected === null || $authorization !== $expected) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
