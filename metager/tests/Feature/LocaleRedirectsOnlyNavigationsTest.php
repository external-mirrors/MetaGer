<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * `LocalizationRedirect` may relocate a page load. It may not relocate a
 * `fetch()`.
 *
 * Several of its rules answer with a redirect to the *other domain*
 * (metager.de <-> metager.org). A browser following that on a navigation is
 * the entire point of the middleware. A script's `fetch()` cannot follow it at
 * all: our own CSP is `connect-src 'self'`, so the request fails outright and
 * the caller sees a rejected promise where it expected an answer.
 *
 * That combination took the start page's search box out of service. Its
 * suggest URL had lost its locale prefix, so the middleware re-detected the
 * locale and sent it cross-origin; the submit handler waited on that request
 * and never got to submit. Fixing the URL fixed that instance. This is the
 * rule that stops the next one: a subresource has no business being moved to a
 * different locale, because the locale its page resolved is already baked into
 * the URL that page generated.
 *
 * A crawler on the start page is the cheapest rule to observe — it is the one
 * redirect that needs neither a specific host nor a cookie
 * (`verifyPathLocaleNeeded()` deliberately pushes crawlers onto a
 * locale-prefixed URL so they index one canonical form).
 */
class LocaleRedirectsOnlyNavigationsTest extends TestCase
{
    private const CRAWLER = "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";

    /**
     * The behaviour being protected, not just tolerated: page loads still get
     * their redirect. A guard that switched it off everywhere would pass the
     * test below and quietly cost us canonical URLs.
     */
    public function testANavigationIsStillRedirected(): void
    {
        $response = $this->withHeaders([
            "User-Agent" => self::CRAWLER,
            "Sec-Fetch-Mode" => "navigate",
        ])->get("/");

        $response->assertRedirect();
    }

    /**
     * No `Sec-Fetch-*` at all — an older browser, curl, a crawler — is treated
     * as a navigation. Assuming otherwise would silently drop the redirect for
     * every client that predates the header, including the crawlers this rule
     * exists for.
     */
    public function testARequestWithoutFetchMetadataIsTreatedAsANavigation(): void
    {
        $response = $this->withHeaders(["User-Agent" => self::CRAWLER])->get("/");

        $response->assertRedirect();
    }

    #[DataProvider("nonNavigationHeaders")]
    public function testANonNavigationRequestIsAnsweredRatherThanRedirected(array $headers): void
    {
        $response = $this->withHeaders(["User-Agent" => self::CRAWLER] + $headers)->get("/");

        $this->assertFalse(
            $response->isRedirect(),
            "A non-navigation request was answered with a redirect to "
                . $response->headers->get("location")
                . ". A fetch() cannot follow that across origins under connect-src 'self'."
        );
    }

    public static function nonNavigationHeaders(): array
    {
        return [
            "fetch/XHR" => [["Sec-Fetch-Mode" => "cors"]],
            "same-origin fetch" => [["Sec-Fetch-Mode" => "same-origin"]],
            "subresource" => [["Sec-Fetch-Mode" => "no-cors"]],
            // The fallbacks for clients that send no Sec-Fetch-* headers.
            "legacy XHR header" => [["X-Requested-With" => "XMLHttpRequest"]],
            "JSON client" => [["Accept" => "application/json"]],
        ];
    }
}
