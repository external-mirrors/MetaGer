<?php

namespace App\Routing;

use App\Authentication\CookieSupport;
use Illuminate\Routing\UrlGenerator;

/**
 * Carries `key` into every generated same-origin URL when the visitor's key
 * arrived by query without a matching cookie — the counterpart, for query
 * strings, to `URL::formatPathUsing` in AppServiceProvider, which carries the
 * locale prefix into every generated path.
 *
 * There is no `formatQueryUsing` hook: `UrlGenerator::format()` never sees
 * the query string, only the path — `to()` builds it separately and appends
 * it after `format()` returns. So this overrides the two entry points every
 * internal link in the app actually goes through instead: `route()` (the
 * overwhelming majority — LinkBuilder, KeymanagerLinks, every blade
 * `route()` call, `redirect()->route()`) and `to()` (`url()`,
 * `redirect()->to()`, the handful of raw-path call sites). `asset()` is
 * untouched, same as it is by the locale hook: it builds asset URLs without
 * calling either.
 *
 * Only `key` is ever added — an allowlist of one, not "everything except a
 * blocklist". A blocklist would risk smearing unrelated request parameters
 * (the search query, admin/log params, the `key_check` marker itself) into
 * links that have nothing to do with them; growing this to cover settings
 * later means adding a name here, not redesigning the approach.
 *
 * Bound in place of the framework's own `UrlGenerator` in
 * AppServiceProvider::boot(), mirroring
 * `Illuminate\Routing\RoutingServiceProvider::registerUrlGenerator()`'s own
 * singleton factory with the class swapped. That provider's `extend('url',
 * ...)` call, which wires up the session/key resolvers `signedRoute()` needs,
 * still applies afterward — container extenders are keyed by abstract name
 * and are untouched by a later `singleton()` rebind of that same name.
 */
class CookieCarryingUrlGenerator extends UrlGenerator
{
    /**
     * Set for the duration of a `signedRoute()` call. It calls `$this->route()`
     * twice internally — once to compute the signature, once for the final
     * URL — and both must see the same, unmodified parameters. Left to carry
     * as normal, the key would end up baked into the signed payload itself,
     * travelling wherever that signed URL travels (an email, a shared link)
     * long after this visit is over.
     */
    private bool $suppressCarry = false;

    public function route($name, $parameters = [], $absolute = true)
    {
        if (!$this->suppressCarry && is_array($parameters) && CookieSupport::keyMissingCookie($this->request)) {
            $parameters += ["key" => $this->request->query("key")];
        }

        return parent::route($name, $parameters, $absolute);
    }

    public function signedRoute($name, $parameters = [], $expiration = null, $absolute = true)
    {
        $this->suppressCarry = true;

        try {
            return parent::signedRoute($name, $parameters, $expiration, $absolute);
        } finally {
            $this->suppressCarry = false;
        }
    }

    public function to($path, $extra = [], $secure = null)
    {
        $url = parent::to($path, $extra, $secure);

        if ($this->suppressCarry) {
            return $url;
        }

        // Post-processes the built result rather than pre-merging into
        // `$extra`: unlike `route()`'s `$parameters`, `to()`'s `$extra` means
        // positional path segments (`url('/foo', ['bar'])` => `/foo/bar`),
        // not query parameters — merging the key in there would corrupt the
        // path, not extend the query. Shared with MetaGerLocalization, which
        // needs the same same-origin carry but never goes through `to()` at
        // all — see CookieSupport::carryIntoUrl()'s docblock.
        return CookieSupport::carryIntoUrl($url, $this->request);
    }
}
