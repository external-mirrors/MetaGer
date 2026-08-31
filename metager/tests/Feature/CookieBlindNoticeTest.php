<?php

namespace Tests\Feature;

use App\Authentication\CookieSupport;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The "your browser is blocking cookies" notice, on the two pages a visitor
 * lands on right after authenticating — the startpage (via `redirect_success`,
 * the more important of the two: it's where the startpage's own "have a key"
 * button sends people back to) and the account page (the default
 * login/key-creation destination).
 *
 * Both are gated by the same `CookieSupport::justAuthenticatedWithoutCookie()`,
 * fed into a `$cookieNotice` view variable deliberately not named
 * `$warning`/`$info` — see the comment at each call site for why. They render
 * it differently, though: the account page uses the shared block in
 * `layouts/staticPages.blade.php`, but the startpage renders its own copy
 * (parts/cookie-notice.blade.php, included a second time) positioned inside
 * `#search-wrapper`'s own grid instead. That page is sized to the viewport
 * (100dvh) with a fixed-position nav cluster aligned to it by a reserved
 * band; a block-level alert dropped in front of that layout in normal flow
 * pushed the whole thing down by the alert's height without moving the
 * cluster, which stayed pinned to the actual viewport top — the two visibly
 * overlapped.
 */
class CookieBlindNoticeTest extends TestCase
{
    /**
     * A valid-shaped UUID v4 (KeyIssuer::isKey()'s format), not just a
     * plausible-looking string: AccountController::keyOf() validates the
     * format before treating a query key as real, and the "queue cookie and
     * redirect" branch this suite is pinning only fires when that succeeds.
     */
    private const KEY = "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456";

    /**
     * Matches AccountPageTest::keyserverKnows() — both pages resolve the
     * visitor as signed in the moment `key` is in the query, and both then
     * need this to render (the startpage's account-pill, the account page's
     * balance and orders).
     */
    private function keyserverKnows(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*/logincode" => Http::response(["key" => self::KEY, "code" => "123456"]),
            "*/api/json/key/*" => Http::response([
                "key" => self::KEY,
                "charge" => 42.0,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => 42.0, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]);
    }

    // ── The startpage ─────────────────────────────────────────────────────

    public function testTheStartpageShowsTheNoticeAfterAMarkedCookielessLanding(): void
    {
        $this->keyserverKnows();

        $response = $this->get("/?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1")->assertOk();

        $response->assertSeeText(trans("login.no_cookies_notice"));
    }

    /**
     * Regression test for the overlap: the notice has to render inside
     * `#search-wrapper-notices`, not as a block dropped in front of the
     * whole `#search-content` layout — see this class's docblock. A crude
     * string-order check rather than a DOM query, matching this codebase's
     * other markup-order assertions (e.g. AccountAppCallbackTest).
     */
    public function testTheStartpageNoticeRendersInsideTheSearchWrapperNotSurroundingIt(): void
    {
        $this->keyserverKnows();

        $html = $this->get("/?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1")
            ->assertOk()
            ->getContent();

        $wrapperPos = strpos($html, 'id="search-wrapper-notices"');
        // e(), not the raw translation: {{ }} escapes it, so "isn't" is
        // literally "isn&#039;t" in the response body.
        $noticePos = strpos($html, e(trans("login.no_cookies_notice")));

        $this->assertNotFalse($wrapperPos, "the startpage lost #search-wrapper-notices");
        $this->assertNotFalse($noticePos, "the notice did not render");
        $this->assertGreaterThan($wrapperPos, $noticePos, "the notice must render after #search-wrapper-notices opens");
    }

    /**
     * The false-positive this guards against: a visitor who followed a
     * shared or bookmarked `?key=...` link, on a browser whose cookies work
     * fine, must not be told otherwise just because this one request has no
     * cookie yet.
     */
    public function testTheStartpageDoesNotShowTheNoticeWithoutTheMarker(): void
    {
        $this->keyserverKnows();

        $response = $this->get("/?key=" . self::KEY)->assertOk();

        $response->assertDontSeeText(trans("login.no_cookies_notice"));
    }

    public function testTheStartpageDoesNotShowTheNoticeWhenTheCookieDidArrive(): void
    {
        $this->keyserverKnows();

        $response = $this->withCookie("key", self::KEY)
            ->get("/?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1")
            ->assertOk();

        $response->assertDontSeeText(trans("login.no_cookies_notice"));
    }

    /**
     * The startpage sets no Cache-Control of its own — worth pinning
     * explicitly, because a page that might now embed a key in a link must
     * never be shared-cacheable, and it would be easy to assume that needed
     * a deliberate fix here. It doesn't: Symfony's ResponseHeaderBag
     * computes `no-cache, private` for any response that never set a cache
     * directive of its own (ResponseHeaderBag::computeCacheControlValue(),
     * "conservative by default") — the same protection every other page
     * that carries the key gets, without this page needing to do anything.
     */
    public function testTheCarryingStartpageResponseIsNeverSharedCacheable(): void
    {
        $this->keyserverKnows();

        $response = $this->get("/?key=" . self::KEY)->assertOk();

        $response->assertHeader("Cache-Control", "no-cache, private");
    }

    // ── The account page ─────────────────────────────────────────────────

    /**
     * The exact shape of a real cookie-blind landing: `key` authenticates
     * this request (there is no cookie to do it), and the marker is already
     * there too — LoginController's redirect sets both in one go via
     * withKeyCheck(). This is also the regression test for a real bug found
     * while writing it: AccountController::show()'s own "key arrived by
     * query, queue the cookie and redirect to strip it" branch used to fire
     * on *every* request that had `key` in the query, including this
     * redirect's own target — which is itself `/konto?key=...`. Without the
     * `!= "1"` marker check added alongside it, a visitor whose cookie never
     * sticks would loop between this route and itself forever instead of
     * ever seeing the page.
     */
    public function testTheAccountPageRendersWithTheNoticeInsteadOfLooping(): void
    {
        $this->keyserverKnows();

        $response = $this->get("/konto?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1")
            ->assertOk();

        $response->assertSeeText(trans("login.no_cookies_notice"));
    }

    public function testTheAccountPageDoesNotShowTheNoticeWithoutTheMarker(): void
    {
        $this->keyserverKnows();

        // No marker: the "key arrived by query, queue the cookie and
        // redirect" branch fires instead of rendering — a real login/
        // key-creation redirect always sets the marker together with the
        // key, so this is the "old bookmark, no idea about cookies yet"
        // case, not the cookie-blind one.
        $this->get("/konto?key=" . self::KEY)->assertRedirect();
    }

    /** Once the cookie is confirmed working, the notice never shows. */
    public function testTheAccountPageDoesNotShowTheNoticeWhenTheCookieDidArrive(): void
    {
        $this->keyserverKnows();

        $response = $this->withCookie("key", self::KEY)
            ->get("/konto?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1")
            ->assertOk();

        $response->assertDontSeeText(trans("login.no_cookies_notice"));
    }
}
