<?php

namespace App\Support;

/**
 * Every hostname this one application answers to, in one place.
 *
 * The same code runs on metager.de, metager.org, metager3.de, every
 * `*.review.metager.de` preview and two `.onion` addresses — and `config("app.url")`
 * names exactly one of them. Anywhere a decision turns on "is this host one of
 * ours" the answer cannot come from `app.url` alone:
 *
 *  - {@see \App\Http\Middleware\TrustHosts} — the set Symfony validates the
 *    incoming `Host` against, so a forged header on a shared load balancer
 *    cannot steer a generated absolute URL onto someone else's domain.
 *  - {@see \App\Http\Controllers\ChargeController} — the browser origin it
 *    forwards to the keymanager as `return_origin`, which the keymanager then
 *    builds a payment provider's return URLs from. A value that is not ours has
 *    no business being the target of a post-payment redirect.
 *  - {@see isOnion()} — VR Payment (Wero) rejects a `.onion` return URL
 *    outright, so that provider is offered only off an onion address
 *    ({@see \App\Landing\ChargeEligibility}).
 *
 * The production `ingress.hosts` list in `.gitlab/production.yaml` is the other
 * half of this — a host that terminates there but is missing here is trusted by
 * the cluster and rejected by the app. The regionals (`metager.es`, `.nl`,
 * `.co.uk`, `metager2.de`, the `www.` forms) 301 at the ingress to a canonical
 * host before a request ever reaches PHP, so they are deliberately *not* listed
 * here: nothing this class guards ever sees them.
 */
final class AppHosts
{
    /**
     * The canonical hosts, exact-match. `*.review.metager.de` is a pattern and
     * lives in {@see REVIEW_SUFFIX} instead.
     */
    public const CANONICAL = [
        "metager.de",
        "metager.org",
        "metager3.de",
    ];

    /**
     * Both onion services: the v3 address in use and the retired v2 one, which
     * `LocalizationRedirect` still 301s from — a request can arrive on it.
     */
    public const ONION = [
        "metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion",
        "b7cxf4dkdsko6ah2.onion",
    ];

    /** Preview deployments: `<branch-slug>.review.metager.de`. */
    private const REVIEW_SUFFIX = ".review.metager.de";

    /**
     * Whether `$host` — a bare hostname, no scheme, no port — is one this
     * application serves.
     *
     * `localhost` and `nginx` are included: a local `docker compose` stack and
     * its feature tests reach the app under both, and neither is a domain
     * anyone could point at a victim.
     */
    public static function isOurs(?string $host): bool
    {
        if (!is_string($host) || $host === "") {
            return false;
        }

        $host = strtolower($host);

        return in_array($host, self::CANONICAL, true)
            || in_array($host, self::ONION, true)
            || str_ends_with($host, self::REVIEW_SUFFIX)
            || $host === "localhost"
            || $host === "127.0.0.1"
            || $host === "nginx";
    }

    /** Whether `$host` is one of the onion services. */
    public static function isOnion(?string $host): bool
    {
        return is_string($host) && in_array(strtolower($host), self::ONION, true);
    }

    /**
     * The scheme-and-host this request came in on, if it is one of ours — the
     * value the checkout issuers forward to the keymanager as `return_origin`,
     * which a payment provider's post-payment redirect is then built from.
     *
     * `TrustHosts` has already rejected a `Host` that is not on
     * {@see trustedPatterns()} by the time any of those issuers run, so this is
     * a second guard rather than the only one — and the place the fallback to
     * `null` (the keymanager then uses its own configured MetaGer URL) is
     * expressed.
     */
    public static function currentOrigin(\Illuminate\Http\Request $request): ?string
    {
        return self::isOurs($request->getHost()) ? $request->getSchemeAndHttpHost() : null;
    }

    /**
     * The origin for a link that leaves this browser — one a visitor copies out
     * of the page and hands to somebody else (a campaign's public redemption
     * link, {@see \App\Http\Controllers\CampaignController}).
     *
     * Not `config("app.url")`, which is the mistake this replaces.
     * `config("metager.metager.keymanager.server")` defaults to `app.url . "/keys"`,
     * so `app.url` is the address this application reaches the *keymanager* on —
     * in the compose stack `http://nginx:8080`, a name that resolves inside the
     * Docker network and nowhere else. A shareable link built from it was a link
     * nobody could open, which is exactly what the campaign page handed out.
     *
     * The visitor's own origin is right instead, with two exceptions:
     *
     *  - a host that is not ours ({@see isOurs()}) — `TrustHosts` has already
     *    rejected one by the time this runs, so this is a second guard;
     *  - an onion address, deliberately: the link is for a third party, and
     *    handing them an address only Tor can open is worse for them than the
     *    clearnet one. The keymanager makes the same call the same way for the
     *    printed voucher cards (`redeem_base` in its `routes/api.js`).
     *
     * Both fall back to `app.url` — the canonical host in every deployed
     * environment, and the best guess left when the request cannot supply one.
     */
    public static function shareableOrigin(\Illuminate\Http\Request $request): string
    {
        $host = $request->getHost();

        if (self::isOurs($host) && !self::isOnion($host)) {
            return $request->getSchemeAndHttpHost();
        }

        return rtrim((string) config("app.url"), "/");
    }

    /**
     * The `Host`-header patterns for `Request::setTrustedHosts()` — anchored
     * regular expressions, which is the shape that middleware wants.
     *
     * @return list<string>
     */
    public static function trustedPatterns(): array
    {
        $exact = array_map(
            static fn (string $host): string => "^" . preg_quote($host, "#") . "$",
            [...self::CANONICAL, ...self::ONION, "localhost", "127.0.0.1", "nginx"]
        );

        // Any single left-most label, then the fixed review suffix.
        $exact[] = "^[a-z0-9-]+" . preg_quote(self::REVIEW_SUFFIX, "#") . "$";

        return $exact;
    }
}
