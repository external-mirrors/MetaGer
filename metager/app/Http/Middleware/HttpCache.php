<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpCache
{
    /**
     * Memoised for the lifetime of the process — the asset manifest only changes on deploy.
     * @var string|null
     */
    private static ?string $assetVersion = null;

    /**
     * Validator for a rendered result page.
     *
     * A result page is not a pure function of its URL. It depends on two other things, and a
     * cache entry has to be invalidated when either changes:
     *
     *  - **The caller's key.** The page embeds per-user markup — most visibly the SafeBrowse link,
     *    whose hash carries the key itself on a query login and omits it on a header or cookie
     *    login. Serving one user's rendered page to another would hand over their key.
     *  - **The deployed frontend.** Asset URLs are versioned (webpack mix `.version()`), so the
     *    bundle a page loads is baked into its HTML. A page held in cache pins the client to the
     *    bundle that was current when it was stored — which is how a client can keep running
     *    frontend code we replaced days ago, and keep reporting bugs we already fixed.
     *
     * The key is read from all four transports rather than through the guard: this runs as
     * middleware, before the guard resolves, and the raw tuple also distinguishes logins that
     * resolve to the same key through different sources — which render differently.
     */
    public static function resultPageEtag(Request $request): string
    {
        $parts = [
            self::asString($request->input('mgv')),
            self::asString($request->cookie('key')),
            self::asString($request->header('key')),
            self::asString($request->header('anonymous-token-key')),
            self::asString($request->query('key')),
            self::assetVersion(),
        ];
        // Hashed, so no key material ends up in a response header or an access log.
        return '"' . sha1(implode("\0", $parts)) . '"';
    }

    /** Cache-Control for a result page: cacheable, but never by a shared cache — it is per-user. */
    public static function resultPageCacheControl(bool $finished): string
    {
        return $finished
            ? "private, max-age=3600, must-revalidate"
            : "private, no-cache, must-revalidate";
    }

    /**
     * Headers that make a stored copy user-specific. Vary is belt and braces next to `private`:
     * it also stops a browser reusing one profile's page after the extension swaps its key.
     */
    public static function resultPageVary(): string
    {
        return "Cookie, Key, Anonymous-Token-Key";
    }

    private static function asString($value): string
    {
        return is_string($value) ? $value : '';
    }

    private static function assetVersion(): string
    {
        if (self::$assetVersion !== null) {
            return self::$assetVersion;
        }
        $manifest = public_path('mix-manifest.json');
        // A missing manifest must not break rendering; it only costs cache precision.
        return self::$assetVersion = is_readable($manifest) ? (string) md5_file($manifest) : 'no-manifest';
    }

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
         *
         * The 304 is conditional on the validator matching. It used to be returned whenever an
         * If-Modified-Since header was present at all, without comparing it to anything — so a
         * stored page revalidated as "still fresh" forever, no matter how much the key state or
         * the deployed frontend had moved on. That turns one stale entry into a permanent one.
         */
        if ($request->filled("mgv") && !$request->filled("out")) {
            $etag = self::resultPageEtag($request);
            if (self::matchesEtag($request->header('If-None-Match'), $etag)) {
                return response("", 304, [
                    "Cache-Control" => self::resultPageCacheControl(true),
                    "ETag" => $etag,
                    "Vary" => self::resultPageVary(),
                    "Last-Modified" => gmdate("D, d M Y H:i:s T"),
                ]);
            }
        }
        return $next($request);
    }

    /** @param string|null $ifNoneMatch Raw If-None-Match header — a comma-separated list. */
    private static function matchesEtag(?string $ifNoneMatch, string $etag): bool
    {
        if ($ifNoneMatch === null || $ifNoneMatch === '') {
            return false;
        }
        foreach (explode(',', $ifNoneMatch) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '*') {
                return true;
            }
            // Caches may revalidate with the weak form of a tag we issued strong.
            if (preg_replace('/^W\//', '', $candidate) === $etag) {
                return true;
            }
        }
        return false;
    }
}
