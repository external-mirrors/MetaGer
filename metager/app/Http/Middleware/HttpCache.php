<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         * MGV Parameter is different for every search executed
         * Let the browser use the cached version if it can provide one for the specified mgv
         * This will happen if the browser restores opened tabs or the user opens a result page from history
         */
        if ($request->header("If-Modified-Since") !== null && $request->filled("mgv") && !$request->filled("out")) {
            return response("", 304, [
                "Cache-Control" => "no-cache, max-age=3600, must-revalidate, public",
                "Last-Modified" => gmdate("D, d M Y H:i:s T"),
            ]);
        }
        return $next($request);
    }
}