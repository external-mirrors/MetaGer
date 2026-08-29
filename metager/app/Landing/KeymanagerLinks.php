<?php

namespace App\Landing;

use App\Localization;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * The `/keys` links the startpage points at, in one place.
 *
 * `/keys` is not a MetaGer route: nginx proxies it to the keymanager service,
 * which still owns everything about a key — creating one, entering one, paying
 * for one. What it no longer owns is the page that explains any of that, and
 * the explaining page is the one that carries most of these links. So they were
 * about to be spelled out across five blades, which is exactly the shape that
 * makes the next migration step expensive.
 *
 * What is left here is the key flow itself — creating a key, entering one,
 * signing out, redeeming a voucher. `/preise`, `/agb` and the two help pages
 * used to be built here too and are MetaGer routes now, so their call sites
 * name a route like every other page does.
 *
 * These are the only links on the page that a *URL* has to be built for
 * rather than a route named: `URL::formatPathUsing` in AppServiceProvider puts
 * the locale prefix on everything `route()` and `url()` produce, but there is
 * no route to name here, so LaravelLocalization::getLocalizedURL does it.
 */
final class KeymanagerLinks
{
    /**
     * The MetaGer app opens the landing page in a Custom Tab and appends these,
     * so the key it is about to be handed can find its way back into the app
     * (docs/10-open-decisions.md#d52 in app-en). The keymanager re-emits them on
     * every in-`/keys` link for the same reason; now that the page a visitor
     * reads first is served from here, this side has to re-emit them too, or
     * the two links out of it drop the callback and the key silently never
     * reaches the app.
     *
     * Only ever re-emitted, never acted on — routes/key.js in the keymanager
     * validates both against its own allowlists before either reaches a
     * redirect target.
     *
     * @return array<string, string>
     */
    public static function appCallback(?Request $request = null): array
    {
        $request ??= request();

        $keystore = $request->query("keystore");
        if (!is_string($keystore) || trim($keystore) === "") {
            return [];
        }

        $callback = ["keystore" => $keystore];

        $variant = $request->query("variant");
        if (is_string($variant) && trim($variant) !== "") {
            $callback["variant"] = $variant;
        }

        return $callback;
    }

    /** @param array<string, string> $query */
    private static function url(string $path, array $query = [], string $fragment = ""): string
    {
        $url = LaravelLocalization::getLocalizedURL(null, "/keys" . $path);

        if ($query !== []) {
            $url .= "?" . http_build_query($query);
        }

        return $url . $fragment;
    }

    /**
     * Creating a key. `#second-nav` is the keymanager's own anchor, kept from
     * the page this one replaces.
     */
    public static function create(?Request $request = null): string
    {
        return self::url("/key/create", self::appCallback($request), "#second-nav");
    }

    /** Signing in with a key that already exists. */
    public static function enter(?string $redirectSuccess = null, ?Request $request = null): string
    {
        $query = self::appCallback($request);

        if ($redirectSuccess !== null) {
            $query["redirect_success"] = $redirectSuccess;
        }

        return self::url("/key/enter", $query);
    }

    /**
     * Signing out, and coming back to the page the user is standing on.
     *
     * The return URL has to have `key` taken out of it first, and that is the
     * whole reason this method exists rather than the blade building the link.
     * Entering a key redirects to `…/?key=<uuid>` — routes/key.js puts it there
     * so the guard picks the key up on the very next request — and
     * resources/js/utility.js then rewrites it back out of the address bar. The
     * sidebar, though, was rendered from the URL as it arrived, so its logout
     * link still carried the parameter. Signing out cleared the cookie and
     * bounced straight back to a URL that still held the credential, and
     * `KeyAuthGuard` reads the query string ahead of the cookie: the visitor
     * landed signed in again, on a URL that looked clean. Only a second,
     * unassisted load of the page finally logged them out.
     *
     * The keymanager strips it a second time (pass/app/LogoutRedirect.js),
     * because `/key/remove` also falls back to the Referer when no `url` is
     * given, and a Referer is not ours to sanitise.
     */
    public static function remove(?string $returnTo = null): string
    {
        $returnTo ??= Localization::currentFullUrl();

        return self::url("/key/remove", ["url" => self::withoutKey($returnTo)]);
    }

    /**
     * `$url` minus its `key` parameter.
     *
     * Split by hand rather than through `parse_url()`/`http_build_url()`: the
     * value comes from the request we are serving, so it is already a URL, and
     * reassembling one from eight optional parts is the step that drops a port
     * or an empty path.
     */
    private static function withoutKey(string $url): string
    {
        $fragment = "";
        if (($hash = strpos($url, "#")) !== false) {
            $fragment = substr($url, $hash);
            $url = substr($url, 0, $hash);
        }

        $mark = strpos($url, "?");
        if ($mark === false) {
            return $url . $fragment;
        }

        parse_str(substr($url, $mark + 1), $query);
        unset($query["key"]);

        return substr($url, 0, $mark)
            . ($query === [] ? "" : "?" . http_build_query($query))
            . $fragment;
    }

    /**
     * Redeeming a voucher code — the campaign pages, which stay here.
     *
     * The last of the reader-facing `/keys` paths still linked from a MetaGer
     * page: the key FAQ explains what to do with a promotional card. It moves
     * in step 3 with the rest of the key flow.
     */
    public static function voucher(): string
    {
        return self::url("/c");
    }
}
