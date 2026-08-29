<?php

namespace App\Landing;

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
 * They are also the only links on the page that a *URL* has to be built for
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

    /** What a key costs, and the payment methods section of that page. */
    public static function cost(): string
    {
        return self::url("/cost");
    }

    public static function paymentMethods(): string
    {
        return self::url("/cost", [], "#payment-methods");
    }

    /** What an anonymous token is — the page the account pill also falls back to. */
    public static function anonymousToken(): string
    {
        return self::url("/help/anonymous-token");
    }
}
