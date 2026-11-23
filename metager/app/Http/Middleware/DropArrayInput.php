<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Removes every query, form and cookie field that arrived as an array.
 *
 * PHP parses `name[]=x` (and `name[a][b]=x`) into an array wherever it
 * appears: in the query string, in a form-encoded body and in the Cookie
 * header. Nothing MetaGer generates ever sends one — every field it reads is
 * a string — but every controller was free to assume so, and a scanner that
 * sent them found sixteen places that answered 500 (`trim()`, `substr()`,
 * `parse_url()`, `htmlspecialchars()`, a cookie name, an array offset:
 * GlitchTip METAGER-1AE … METAGER-1BA). Fixing each call site the way
 * METAGER-18L and METAGER-19V were would only move the next one along.
 *
 * Such a field is treated as absent, which is what those per-site fixes
 * chose. A JSON body is left alone: there arrays are structure on purpose,
 * and Laravel reads it from its own bag, not from `$request->request`.
 */
class DropArrayInput
{
    public function handle(Request $request, Closure $next): Response
    {
        self::dropArrays($request->query);
        self::dropArrays($request->cookies);
        if (!$request->isJson()) {
            self::dropArrays($request->request);
        }

        return $next($request);
    }

    private static function dropArrays(\Symfony\Component\HttpFoundation\ParameterBag $bag): void
    {
        foreach ($bag->all() as $name => $value) {
            if (is_array($value)) {
                $bag->remove($name);
            }
        }
    }
}
