<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
     *  - **The client version.** `mg-app` decides whether that link is rendered at all
     *    (ClientCapabilities::supportsSafebrowse), so a page stored before an app upgrade would
     *    keep pointing the upgraded app at the old proxy.
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
            self::asString($request->header('mg-app')),
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
        return "Cookie, Key, Anonymous-Token-Key, Mg-App";
    }

    /**
     * A rendered page that depends on who is asking: always revalidated, never
     * shared, and cheap to revalidate.
     *
     * The startpage is the case this exists for. It has two entirely different
     * bodies — the landing page and the search bar — chosen by the key guard,
     * and it had no cache headers of its own at all: what went out was
     * Symfony's conservative default (`no-cache, private`, because nothing set
     * `Cache-Control` and there is no `Last-Modified`) with no validator
     * anywhere. That is the worst of both ends. `no-cache` means a stored copy
     * may never be reused without asking, and with no ETag and no
     * `Last-Modified` there is nothing to ask *with* — so every single load of
     * the busiest page in the product is a full render and a full transfer,
     * and the conditional request that should have cost 304 bytes cannot be
     * made at all.
     *
     * **The ETag is the hash of the body, not of an enumerated tuple.** The
     * result page does enumerate ({@see resultPageEtag()}), and it can: its URL
     * carries the `mgv` that makes each search distinct. The startpage's URL is
     * `/` for everyone forever, and its body moves with the balance in the
     * sidebar, the exhausted-key alert, the locale, the theme, the tiles
     * setting and the asset build. An enumeration that misses one of those
     * serves somebody a page that is quietly wrong — the same failure this
     * class's own docblock records for `If-Modified-Since`, where one stale
     * entry became a permanent one. Hashing the body cannot be wrong by
     * construction.
     *
     * What that buys and what it does not: the render still happens, so this
     * saves the transfer and the client's re-parse, not the server's work. It
     * is worth having anyway — the landing page is byte-identical for every
     * anonymous visitor in a locale, so the common case revalidates to a 304.
     * Skipping the render too would mean enumerating, and that trade is not
     * this page's to take.
     *
     * `must-revalidate` next to `no-cache` is belt and braces: `no-cache`
     * already forbids reuse without revalidation, and `must-revalidate` says
     * what happens when we cannot be reached — serve an error, not the stale
     * balance.
     */
    public static function revalidatable(Request $request, SymfonyResponse $response): SymfonyResponse
    {
        $etag = '"' . sha1((string) $response->getContent()) . '"';

        $response->headers->set("Cache-Control", "private, no-cache, must-revalidate");
        $response->headers->set("ETag", $etag);
        // Assigned, not appended: ResolveLocale::declareVariance() runs after
        // this and adds Accept-Language and Cookie to whatever is here, so the
        // list that ends up on the wire is this one plus those two.
        $response->headers->set("Vary", self::resultPageVary());

        if ($request->isMethodCacheable() && self::matchesEtag($request->headers->get("If-None-Match"), $etag)) {
            $response->setStatusCode(SymfonyResponse::HTTP_NOT_MODIFIED);
            $response->setContent("");
            // A 304 carries no body, so nothing may describe one. Symfony's
            // prepare() strips these on send; doing it here too means the
            // response is already correct for anything that reads it before
            // then — a test, or another middleware on the way out.
            $response->headers->remove("Content-Type");
            $response->headers->remove("Content-Length");
        }

        return $response;
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
        // Vite::manifestHash() is null when no build is present. A missing manifest must not
        // break rendering; it only costs cache precision.
        return self::$assetVersion = Vite::manifestHash() ?? 'no-manifest';
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
