<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * What the startpage says about caching, and what it gives a browser to
 * revalidate with.
 *
 * The page has two entirely different bodies — the landing page and the search
 * bar — picked by the key guard, and until now it declared none of that itself.
 * What went out was Symfony's conservative default (`no-cache, private`,
 * because nothing set a directive and there is no Last-Modified) and no
 * validator at all: correct, and the worst possible trade. `no-cache` forbids
 * reusing a stored copy without asking, and with no ETag there was nothing to
 * ask with — so the busiest page in the product was a full render and a full
 * transfer, every single time, for a body that is byte-identical for every
 * anonymous visitor in a locale.
 *
 * {@see \App\Http\Middleware\HttpCache::revalidatable()} carries the reasoning,
 * including why the validator is a hash of the body rather than the enumerated
 * tuple the result page uses.
 */
class StartpageCacheHeadersTest extends TestCase
{
    private const KEY = "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456";

    private function keyserverKnows(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/key/*" => Http::response(["key" => self::KEY, "charge" => 42.0]),
            "*" => Http::response(""),
        ]);
    }

    /**
     * Asserted directive by directive rather than as one string: Symfony's
     * ResponseHeaderBag sorts what it emits, so the header on the wire reads
     * `must-revalidate, no-cache, private`. The order carries no meaning and
     * pinning it would only break the next time Symfony changes its mind.
     */
    public function testTheStartpageIsPrivateAndAlwaysRevalidated(): void
    {
        $cacheControl = $this->get("/")->assertOk()->headers->get("Cache-Control");

        foreach (["private", "no-cache", "must-revalidate"] as $directive) {
            $this->assertStringContainsString($directive, $cacheControl);
        }
    }

    /**
     * Without this the `no-cache` above is a promise nobody can keep: a
     * conditional request needs something to be conditional on.
     */
    public function testTheStartpageShipsAValidator(): void
    {
        $etag = $this->get("/")->assertOk()->headers->get("ETag");

        $this->assertNotNull($etag, "The startpage has nothing to revalidate against.");
        $this->assertMatchesRegularExpression('/^"[0-9a-f]{40}"$/', $etag);
    }

    /**
     * Every transport the key can arrive on has to be named, or a stored copy
     * of one visitor's page can answer another's request. `Accept-Language` and
     * `Cookie` are appended afterwards by ResolveLocale::declareVariance().
     */
    public function testTheStartpageDeclaresWhatItVariesOn(): void
    {
        $vary = $this->get("/")->assertOk()->headers->get("Vary");

        foreach (["Cookie", "Key", "Anonymous-Token-Key", "Accept-Language"] as $header) {
            $this->assertStringContainsString($header, $vary);
        }
    }

    /** The point of the whole exercise: a matching validator costs a 304, not a page. */
    public function testAMatchingValidatorIsAnswered304(): void
    {
        $etag = $this->get("/")->assertOk()->headers->get("ETag");

        $second = $this->withHeader("If-None-Match", $etag)->get("/");

        $second->assertStatus(304);
        $this->assertSame("", $second->getContent());
    }

    /**
     * nginx weakens an ETag when it gzips the response, so what comes back is
     * `W/"…"` and not what we sent. Comparing strictly — which is what
     * Symfony's own `isNotModified()` does — means a page that never once
     * revalidates in production while passing every test here.
     */
    public function testAWeakenedValidatorStillMatches(): void
    {
        $etag = $this->get("/")->assertOk()->headers->get("ETag");

        $this->withHeader("If-None-Match", 'W/' . $etag)->get("/")->assertStatus(304);
    }

    /**
     * The validator *is* the body, on both of the two pages this URL serves.
     *
     * Stated as an invariant per response rather than as "signing in changes
     * the ETag", which is what it replaces: two requests inside one test method
     * share an application, and the key guard memoises its answer for the
     * lifetime of one (see KeyAuthGuardTest) — so the second request was
     * answered by the first request's guard and both pages came out anonymous.
     * The invariant is the stronger statement anyway: if the ETag is the hash
     * of the body, then no two different bodies can ever share one, and that
     * covers the balance and the theme as well as the login state.
     */
    public function testTheValidatorIsTheHashOfTheLandingPage(): void
    {
        $response = $this->get("/")->assertOk();

        $response->assertSee("searchbar-replacement", false);
        $this->assertSame('"' . sha1($response->getContent()) . '"', $response->headers->get("ETag"));
    }

    /** The same invariant on the other body this URL can serve. */
    public function testTheValidatorIsTheHashOfTheSignedInPage(): void
    {
        $this->keyserverKnows();

        $response = $this->withUnencryptedCookie("key", self::KEY)->get("/")->assertOk();

        $response->assertSee('id="eingabe"', false);
        $this->assertSame('"' . sha1($response->getContent()) . '"', $response->headers->get("ETag"));
    }
}
