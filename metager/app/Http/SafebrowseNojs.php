<?php

namespace App\Http;

use Illuminate\Http\Request;

/**
 * The link into SafeBrowse's page view without JavaScript, and the `sid` that ties the results
 * opened from one results page to one SafeBrowse session.
 *
 * A browser without JavaScript follows a result link's plain href, so the href itself has to name
 * the target: `/proxy/nojs?url=<target>&sid=<sid>`. (The JavaScript path is untouched: it reads
 * `data-proxy-link`, parts/proxy-safebrowse-attribute, whose hash never reaches a server.)
 * SafeBrowse reuses a running session by its own cookie; when the browser blocks cookies it falls
 * back to `sid`, and only accepts it from a request with a same-origin Referer.
 *
 * `sid` is random and never derived from the key or the user: a key is shared by several people
 * (a family, an office), and a session shared through it would share its cookies, logins and
 * history. It stays the same for as long as the user keeps searching from the result page — the
 * search bar and the result page's own links carry it — so a new search does not start a second
 * session. A carried `sid` is only taken over when the request came from one of our own pages,
 * the same rule SafeBrowse applies: a result page URL passed on to someone else (it now carries
 * a `sid`) starts their own session instead of joining the sender's.
 */
class SafebrowseNojs
{
    /** What SafeBrowse accepts as a `sid` (app/nojs/router.js, SID_RE). */
    public const SID_PATTERN = '/^[A-Za-z0-9_-]{16,64}$/';

    /** Request attribute the `sid` is memoised in: one per rendered page, however many links. */
    private const ATTRIBUTE = 'safebrowse-nojs-sid';

    /** The `sid` of the page being rendered: the one it was reached with from our own page, or a new one. */
    public static function sid(Request $request): string
    {
        $sid = $request->attributes->get(self::ATTRIBUTE);
        if (is_string($sid)) {
            return $sid;
        }
        $carried = $request->query('sid');
        $sid = is_string($carried) && preg_match(self::SID_PATTERN, $carried) && self::fromOurOrigin($request)
            ? $carried
            : bin2hex(random_bytes(16));
        $request->attributes->set(self::ATTRIBUTE, $sid);
        return $sid;
    }

    /**
     * The href that opens $link in SafeBrowse without JavaScript.
     *
     * $key is passed only for a query login (see parts/proxy-safebrowse-attribute): a cookie
     * login reaches SafeBrowse by itself, a header login through the webextension, and neither
     * belongs in a URL.
     */
    public static function url(Request $request, string $link, ?string $key = null): string
    {
        $parameters = ['url' => $link, 'sid' => self::sid($request)];
        if ($key !== null && $key !== '') {
            $parameters['key'] = $key;
        }
        // The localized path, without the settings the URL generator carries into our own URLs
        // for a cookie-blind visitor (SettingsCarry): they mean nothing to SafeBrowse.
        $base = explode('?', \LaravelLocalization::getLocalizedUrl(null, '/proxy/nojs'), 2)[0];
        return $base . '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    /** Whether the Referer is one of our own pages (same host, which MetaGer and SafeBrowse share). */
    private static function fromOurOrigin(Request $request): bool
    {
        $referer = $request->headers->get('referer');
        if (!is_string($referer) || $referer === '') {
            return false;
        }
        $host = parse_url($referer, PHP_URL_HOST);
        if (!is_string($host)) {
            return false;
        }
        $port = parse_url($referer, PHP_URL_PORT);
        $refererHost = strtolower($host) . ($port !== null ? ':' . $port : '');
        return $refererHost === strtolower($request->getHttpHost());
    }
}
