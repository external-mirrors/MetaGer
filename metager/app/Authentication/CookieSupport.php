<?php

namespace App\Authentication;

use Illuminate\Http\Request;

/**
 * Whether this visitor's key is riding in the URL because their cookie jar
 * won't hold it — and the one query parameter used to say so on purpose.
 *
 * Two questions, kept apart because they answer to different audiences and
 * getting them backwards produces a false alarm:
 *
 *   {@see keyMissingCookie()}   drives *propagation* (carrying `key` forward
 *                               into every link and form). Deliberately
 *                               lenient: true for anyone whose key arrived by
 *                               query without a matching cookie, including a
 *                               visitor who just clicked a shared or
 *                               bookmarked `?key=...` link on a browser that
 *                               accepts cookies perfectly well. Carrying the
 *                               key forward for them is harmless — it is what
 *                               resources/views/parts/searchbar.blade.php and
 *                               {@see \App\Landing\KeymanagerLinks::accountForVisitor()}
 *                               already do today, just inconsistently.
 *
 *   {@see justAuthenticatedWithoutCookie()}   drives the *notice*. Much
 *                               narrower: only true one hop after this app's
 *                               own sign-in/key-creation/settings-sync flow
 *                               queued a cookie and it still is not there.
 *                               Without the marker, the shared-link visitor
 *                               above would also trip it and be told —
 *                               wrongly — that their browser is blocking
 *                               cookies.
 *
 * The key cookie is only ever queued once a key is already in hand
 * (LoginController::signIn, KeyCreationController::submit,
 * AccountController::show, SettingsController::loadSettings) — never
 * speculatively for an anonymous visitor. Detection therefore never needs a
 * probe cookie of its own: the redirect that follows queuing already carries
 * the marker, and the next request either has the cookie or it does not.
 */
final class CookieSupport
{
    /**
     * The one-time marker appended to the redirect that follows a fresh
     * `Cookie::queue('key', ...)`. Never carried forward beyond that one hop —
     * it is not in the propagation allowlist in CookieCarryingUrlGenerator —
     * so it cannot linger and mark unrelated pages as "just authenticated".
     */
    public const MARKER = "key_check";

    /**
     * `$request->query("key")`, not `$request->filled("key")`: the latter
     * also matches a `key` in the POST body, e.g. the login form's own
     * submission. There is nothing to carry forward in that case — the
     * value lives in a request body that will not be replayed, not in a URL
     * that will — and treating that as "cookie-blind" fed a null key into
     * `carryIntoUrl()`, producing a bare trailing `?` on any link built from
     * within that same request (surfaced by `KeymanagerLinks::voucher()`
     * during a login POST, `http_build_query` silently drops a null value).
     *
     * Never true for the webextension — see {@see cookieTakenByExtension()}.
     */
    public static function keyMissingCookie(Request $request): bool
    {
        return !empty($request->query("key"))
            && $request->cookie("key") === null
            && !self::cookieTakenByExtension($request);
    }

    /**
     * Excludes header-authenticated requests too, not just header-carried
     * keys in general: the webextension deliberately deletes the browser's
     * `key` cookie once it has captured it and sends the key as a `key`
     * header on every request from then on — a *working* persistent login,
     * just not a cookie-backed one. Without this exclusion, the first
     * extension-managed request after a fresh sign-in — cookie gone, header
     * not obviously distinguishable from "the browser dropped it" — would
     * wrongly tell an extension user their browser is blocking cookies.
     * `keyMissingCookie()` already excludes them for propagation, since
     * `filled('key')` never sees a header; this mirrors that exclusion for
     * the notice, which needs it spelled out explicitly because it checks
     * the cookie's absence directly rather than going through `filled()`.
     *
     * The `key` header alone does not cover the extension's handoff from
     * "deleted the fresh cookie" to "sending the header instead": a request
     * in between has neither. {@see cookieTakenByExtension()} closes that.
     */
    public static function justAuthenticatedWithoutCookie(Request $request): bool
    {
        return $request->query(self::MARKER) === "1"
            && $request->cookie("key") === null
            && !$request->hasHeader("key")
            && !self::cookieTakenByExtension($request);
    }

    /**
     * Whether a missing `key` cookie is the webextension's doing rather than
     * the browser's.
     *
     * The extension deletes every `key` cookie it sees and sends the key as a
     * header instead — but only once its header rule is installed, which
     * waits on a keyserver round trip, while the browser follows the
     * sign-in's redirect at once. Before extension 1.30 the cookie was even
     * deleted *before* that round trip started (TokenManager
     * `_load_key_from_browser`), so that redirect regularly arrived with
     * neither cookie nor `key` header. Older versions stay installed, so this
     * is not something the extension fix alone can retire.
     *
     * `Mg-Webext` is the signal: its rule is installed independently of any
     * sign-in and covers every top-level navigation. It is absent when the
     * user switched off settings storage in the extension; such a user falls
     * back to the cookie-blind handling, which still keeps them signed in.
     */
    private static function cookieTakenByExtension(Request $request): bool
    {
        return $request->hasHeader("Mg-Webext");
    }

    /**
     * `$url` with `key` and the marker added — filled in, not overwritten, so
     * calling this on a target that already carries its own `key` (as
     * SettingsController::loadSettings's redirect to the startpage does when
     * `key` was among the synced settings) is a no-op for that parameter.
     */
    public static function withKeyCheck(string $url, string $key): string
    {
        return self::mergeQuery($url, ["key" => $key, self::MARKER => "1"]);
    }

    /**
     * `$url` with `key` and any carried settings (see `App\Http\SettingsCarry`)
     * silently added, same-origin only — the shared implementation behind
     * {@see \App\Routing\CookieCarryingUrlGenerator::to()} and
     * {@see \App\Localization\MetaGerLocalization::getLocalizedURL()}.
     *
     * The second call site exists because `MetaGerLocalization` does not
     * build its answer through `route()`/`to()` at all — it assembles the
     * URL by hand from `LocaleContext` and a bare `parse_url()`, precisely to
     * avoid the double-prefixing and stale-mapping bugs documented on that
     * class — so it never passes through `CookieCarryingUrlGenerator` and
     * needs this called explicitly on its result. That is also why every
     * sidebar link, the logo, and the rest of the nav (all built via
     * `LaravelLocalization::getLocalizedURL(...)`) go through here rather
     * than `CookieCarryingUrlGenerator`.
     *
     * Takes `$request` explicitly rather than reading `request()` itself:
     * `MetaGerLocalization` is a singleton that can be constructed before
     * `ResolveLocale` has swapped in the real request (see its own
     * docblock), so its callers must each resolve a fresh request rather
     * than have this method quietly cache one.
     */
    public static function carryIntoUrl(string $url, Request $request): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host !== null && $host !== $request->getHost()) {
            return $url;
        }

        // Two independent sources, not one merged predicate:
        // keyMissingCookie() requires a key already in the query, which an
        // anonymous cookie-blind visitor never has, so it cannot gate
        // settings carrying too.
        $extra = app(\App\Http\SettingsCarry::class)->all();
        if (self::keyMissingCookie($request)) {
            $extra["key"] = $request->query("key");
        }
        if ($extra === []) {
            return $url;
        }

        return self::mergeQuery($url, $extra);
    }

    /**
     * Split by hand rather than through `parse_url()`/rebuilding one from its
     * parts, matching {@see \App\Landing\KeymanagerLinks::withoutKey()}: the
     * fragment has to survive after the query, not before it, and a URL
     * reassembled from parse_url()'s pieces is the version that drops an
     * empty path or a port.
     */
    private static function mergeQuery(string $url, array $extra): string
    {
        $fragment = "";
        if (($hash = strpos($url, "#")) !== false) {
            $fragment = substr($url, $hash);
            $url = substr($url, 0, $hash);
        }

        $query = [];
        $base = $url;
        if (($mark = strpos($url, "?")) !== false) {
            parse_str(substr($url, $mark + 1), $query);
            $base = substr($url, 0, $mark);
        }

        $query += $extra;

        return $base . "?" . http_build_query($query) . $fragment;
    }
}
