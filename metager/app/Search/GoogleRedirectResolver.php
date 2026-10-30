<?php

namespace App\Search;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Log;

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
 * The token cannot be decoded locally (it is a protobuf wrapping ciphertext),
 * so it is resolved the only way there is: MetaGer asks Google itself, once,
 * without following the redirect, and reads `Location`. Google learns that
 * *MetaGer's* server resolved a link, which it learned anyway when Serper ran
 * the search; it learns nothing about the user. Cookies are not kept.
 *
 * `/url?q=…` and `/url?url=…`, the older spelling, carry the address in
 * plain text and are unwrapped without a request.
 *
 * ## A redirect that cannot be resolved is dropped, not shown
 *
 * Falling back to the Google link would quietly reintroduce the leak whenever
 * Google is slow or rate-limits us, which is when nobody would be looking. A
 * result page one entry shorter is the cheaper failure.
 */
class GoogleRedirectResolver
{
    /** Per lookup; they run in parallel, so this is roughly the added latency at worst. */
    public const TIMEOUT_SECONDS = 1.5;

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
     * Null for every redirect that could not be resolved to an http(s) URL
     * outside Google's redirect machinery. Entries that are not redirects at
     * all come back unchanged.
     *
     * @param string[] $urls
     * @return array<string, ?string>
     */
    public function resolve(array $urls): array
    {
        $resolved = [];
        $toFetch = [];

        foreach (array_unique($urls) as $url) {
            if (!self::isRedirect($url)) {
                $resolved[$url] = $url;
                continue;
            }

            $plain = self::unwrapLocally($url);
            if ($plain !== null) {
                $resolved[$url] = $plain;
                continue;
            }

            $toFetch[] = $url;
        }

        if (empty($toFetch)) {
            return $resolved;
        }

        $responses = Http::pool(fn(Pool $pool) => array_map(
            fn(string $url) => $pool->as($url)
                ->withoutRedirecting()
                ->connectTimeout(self::TIMEOUT_SECONDS)
                ->timeout(self::TIMEOUT_SECONDS)
                ->withHeaders(["Accept" => "text/html"])
                ->get($url),
            $toFetch
        ));

        $failed = 0;
        foreach ($toFetch as $url) {
            $response = $responses[$url] ?? null;
            $target = $response instanceof Response && $response->redirect()
                ? self::acceptableTarget($response->header("Location"))
                : null;

            if ($target === null) {
                $failed++;
            }
            $resolved[$url] = $target;
        }

        if ($failed > 0) {
            Log::warning(sprintf("Could not resolve %d of %d Google redirect links; the results carrying them were dropped.", $failed, count($toFetch)));
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
     * nor Google's "unusual traffic" page, which is where a rate-limited
     * lookup is sent instead of the result.
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
