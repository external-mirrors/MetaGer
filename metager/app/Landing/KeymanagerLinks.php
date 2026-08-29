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
 * What is left here is the key flow itself — creating a key, signing out,
 * redeeming a voucher, and the account page. `/preise`, `/agb` and the two help
 * pages used to be built here too and are MetaGer routes now, so their call
 * sites name a route like every other page does.
 *
 * Those are the only links on the page that a *URL* has to be built for
 * rather than a route named: `URL::formatPathUsing` in AppServiceProvider puts
 * the locale prefix on everything `route()` and `url()` produce, but there is
 * no route to name for them, so LaravelLocalization::getLocalizedURL does it.
 *
 * {@see login()} is the exception and is here anyway. The sign-in page *is* a
 * MetaGer route now, so it needs none of that — but it is the one destination
 * in the key flow that every call site reaches through the same two markers
 * ({@see appCallback()}), and splitting it off would mean the callers had to
 * remember them. Which link goes where, in one file, is the point of the file.
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

    /**
     * Signing in with a key that already exists — MetaGer's own `/anmelden`.
     *
     * This used to be `/keys/key/enter`, and that URL still answers: it is
     * where the form posts to, and it is where a visitor who *already* has a
     * key is sent ({@see dashboard()}). What moved here is the page.
     *
     * `$redirectSuccess` rides along as a query parameter and the page puts it
     * back on the form, because it is the keymanager's POST handler that acts
     * on it — the visitor is not signed in until that request, so nothing on
     * this side could honour it.
     */
    public static function login(?string $redirectSuccess = null, ?Request $request = null): string
    {
        $query = self::appCallback($request);

        if ($redirectSuccess !== null) {
            $query["redirect_success"] = $redirectSuccess;
        }

        return route("login", $query);
    }

    /**
     * Where the sign-in form posts to.
     *
     * The page moved and the handler did not, for three reasons that are all
     * the keymanager's alone: the six-digit login code lives in its Redis
     * keyspace, a campaign voucher typed into the key field is normalised by
     * its CampaignVoucher, and a QR code inside an uploaded image is decoded
     * with Jimp. None of the three has an API this side could call, and moving
     * them is a different piece of work from moving a page.
     *
     * Same origin, so nginx's `form-action 'self'` covers it.
     */
    public static function submitKey(): string
    {
        return self::url("/key/enter");
    }

    /**
     * Where the sign-in page asks what a key is worth, ending in a slash so the
     * key itself can be appended.
     *
     * The one place the browser talks to the keymanager directly rather than
     * through App\Authentication\KeyUser: this question is asked *before* the
     * visitor is signed in, about a key this side has never seen, and the
     * answer only decides whether to show a confirmation. `GET /api/json/key/:key`
     * needs no bearer token for exactly that reason: it answers about a key the
     * caller already holds, and says only what it is worth.
     *
     * It is not rate limited. `keyIpLimitMiddleware`, which sits on that route
     * and reads like one, is an IP allowlist for the handful of keys named in
     * the keymanager's `key_ip_limits` config — it lets every other key through
     * untouched. The only brake is a deliberate 250 ms delay on unauthorized
     * callers.
     */
    public static function keyApi(): string
    {
        // Der Schrägstrich wird angehängt und nicht mitgegeben:
        // LaravelLocalization::getLocalizedURL normalisiert einen abschließenden
        // Schrägstrich weg, und die Seite hängt den Schlüssel direkt an.
        return rtrim(self::url("/api/json/key"), "/") . "/";
    }

    /**
     * The account page — where a visitor who is already signed in goes.
     *
     * Still `/key/enter`, which sounds like the sign-in page and is not: given
     * a key, that route has always resolved it to its canonical form and
     * redirected to `/keys/key/<uuid>`. Only its no-key branch rendered a page,
     * and that branch now redirects here to {@see login()}.
     *
     * Named as the destination rather than as the path, because the path is the
     * part that is wrong. Going through it rather than building
     * `/keys/key/<uuid>` here is deliberate: the cookie may hold a legacy
     * non-UUID key, and the keymanager is the only party that can fold one into
     * the account it belongs to.
     */
    public static function dashboard(?Request $request = null): string
    {
        return self::url("/key/enter", self::appCallback($request));
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
