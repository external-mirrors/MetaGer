<?php

namespace App\Search;

/**
 * Takes Google's click-tracking redirects back out of an engine's answer.
 *
 * Serper hands back some results not as the page they are about but as
 * `https://www.google.com/goto?url=CAESggEB…`: an opaque, encrypted token that
 * only Google can turn into the real address, and does — by answering the
 * click with a 302 and a `__Secure-ENID` cookie on `.google.com`. Shown to the
 * user as is, every click on such a result is a request to Google carrying the
 * user's own IP and cookies, which is exactly the tracking MetaGer exists to
 * keep its users out of. It also defeats deduplication, since the same page
 * from Brave and from Serper no longer looks like the same page.
 *
 * `/url?q=…` and `/url?url=…`, the older spelling, carry the address in
 * plain text and are unwrapped here. `/goto` links are dropped.
 *
 * ## Why `/goto` is not asked for
 *
 * It used to be: MetaGer requested each one from Google, without following the
 * redirect, and read `Location`. Since 30 October 2026 Google lets only a few
 * of a search's links be opened right after it, and Serper now resolves the top
 * three itself before answering — so what still arrives as `/goto` is what
 * Google refuses to open. Production logs over the following night showed it:
 * across 3,798 searches, 19.5% of lookups succeeded, and in 80% of searches
 * exactly one did. That was one result per search bought with up to 1.5 s of
 * latency and six or seven requests to Google that it mostly turned away.
 *
 * Serper offers a `/resolve` endpoint for the rest, but it takes about thirty
 * seconds, which no result page can wait for.
 *
 * ## A redirect that cannot be read is dropped, not shown
 *
 * Falling back to the Google link would reintroduce the leak. A result page
 * a few entries shorter is the cheaper failure.
 */
class GoogleRedirectResolver
{
    /**
     * Whether $url is one of Google's redirect wrappers.
     */
    public static function isRedirect(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts) || !isset($parts["host"], $parts["path"])) {
            return false;
        }

        return self::isGoogleHost($parts["host"])
            && in_array($parts["path"], ["/goto", "/url"], true);
    }

    /**
     * Whether $url is any page on Google itself, redirect or not.
     */
    public static function pointsAtGoogle(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && self::isGoogleHost($host);
    }

    /**
     * The address behind each redirect in $urls, keyed by the redirect.
     *
     * Null for every redirect that does not name an http(s) URL outside
     * Google's redirect machinery in plain text — which includes every `/goto`
     * link. Entries that are not redirects at all come back unchanged. Never
     * makes a request.
     *
     * @param string[] $urls
     * @return array<string, ?string>
     */
    public function resolve(array $urls): array
    {
        $resolved = [];
        foreach (array_unique($urls) as $url) {
            $resolved[$url] = self::isRedirect($url) ? self::unwrapLocally($url) : $url;
        }

        return $resolved;
    }

    /**
     * `/url?q=https://…` names its target outright; `/goto?url=CAES…` does not.
     */
    private static function unwrapLocally(string $url): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        foreach (["q", "url"] as $parameter) {
            if (isset($query[$parameter]) && is_string($query[$parameter])) {
                $target = self::acceptableTarget($query[$parameter]);
                if ($target !== null) {
                    return $target;
                }
            }
        }

        return null;
    }

    /**
     * A target worth showing: absolute http(s), and neither another redirect
     * nor Google's "unusual traffic" page.
     */
    private static function acceptableTarget(?string $target): ?string
    {
        if (empty($target)) {
            return null;
        }

        $parts = parse_url($target);
        if (!is_array($parts) || !isset($parts["scheme"], $parts["host"])) {
            return null;
        }
        if (!in_array(strtolower($parts["scheme"]), ["http", "https"], true)) {
            return null;
        }
        if (self::isRedirect($target)) {
            return null;
        }
        if (self::isGoogleHost($parts["host"]) && str_starts_with($parts["path"] ?? "", "/sorry")) {
            return null;
        }

        return $target;
    }

    private static function isGoogleHost(string $host): bool
    {
        return preg_match('/^(www\.)?google\.[a-z]{2,3}(\.[a-z]{2})?$/i', $host) === 1;
    }
}
