<?php

namespace Tests\Unit;

use App\Authentication\CookieSupport;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Authentication\CookieSupport.
 *
 * Deliberately extends PHPUnit's TestCase rather than Tests\TestCase: every
 * method under test is static and reads only the Request handed to it, so
 * booting the framework would only make the Unit suite slow for no gain.
 */
class CookieSupportTest extends TestCase
{
    private const KEY = "aaaaaaaa-bbbb-cccc-dddd-eeeeee123456";

    // ── keyMissingCookie(): drives propagation ───────────────────────────

    public function testTrueWhenKeyIsOnlyInTheQuery(): void
    {
        $request = Request::create("/?key=" . self::KEY);

        $this->assertTrue(CookieSupport::keyMissingCookie($request));
    }

    public function testFalseWhenTheCookieIsAlsoPresent(): void
    {
        $request = Request::create("/?key=" . self::KEY);
        $request->cookies->set("key", self::KEY);

        $this->assertFalse(CookieSupport::keyMissingCookie($request));
    }

    public function testFalseWhenNoKeyIsPresentAtAll(): void
    {
        $this->assertFalse(CookieSupport::keyMissingCookie(Request::create("/")));
    }

    /**
     * The webextension sends `key` as a header on every request and never has
     * a cookie. A header-only key must not be classified as cookie-blind —
     * that would carry it into every link and form on every page an
     * extension user visits, for no reason: their persistent login already
     * works.
     */
    public function testFalseWhenTheKeyIsOnlyInAHeaderNotTheQuery(): void
    {
        $request = Request::create("/");
        $request->headers->set("key", self::KEY);

        $this->assertFalse(CookieSupport::keyMissingCookie($request));
    }

    // ── justAuthenticatedWithoutCookie(): drives the notice ──────────────

    public function testTrueWithTheMarkerAndNoCookie(): void
    {
        $request = Request::create("/?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1");

        $this->assertTrue(CookieSupport::justAuthenticatedWithoutCookie($request));
    }

    /**
     * A visitor who simply clicked a shared or bookmarked `?key=...` link —
     * on a browser whose cookies work perfectly well — must never be told
     * their browser is blocking cookies. The marker is what tells the two
     * cases apart; without it, this would be indistinguishable from a fresh
     * cookie-blind sign-in.
     */
    public function testFalseWithoutTheMarkerEvenWithoutACookie(): void
    {
        $request = Request::create("/?key=" . self::KEY);

        $this->assertFalse(CookieSupport::justAuthenticatedWithoutCookie($request));
    }

    public function testFalseWithTheMarkerWhenTheCookieDidArrive(): void
    {
        $request = Request::create("/?key=" . self::KEY . "&" . CookieSupport::MARKER . "=1");
        $request->cookies->set("key", self::KEY);

        $this->assertFalse(CookieSupport::justAuthenticatedWithoutCookie($request));
    }

    /**
     * The webextension deletes the cookie once it has captured it and sends
     * the key as a header from then on — a working, just not cookie-backed,
     * persistent login. Without this exclusion, its first request after a
     * fresh sign-in (cookie gone, header not yet distinguishable from "the
     * browser dropped it") would wrongly show the notice to an extension
     * user whose login is actually fine.
     */
    public function testFalseWithTheMarkerWhenAHeaderKeyIsPresent(): void
    {
        $request = Request::create("/?" . CookieSupport::MARKER . "=1");
        $request->headers->set("key", self::KEY);

        $this->assertFalse(CookieSupport::justAuthenticatedWithoutCookie($request));
    }

    // ── withKeyCheck() ─────────────────────────────────────────────────

    public function testAddsKeyAndTheMarkerToAPlainUrl(): void
    {
        $url = CookieSupport::withKeyCheck("https://metager.de/konto", self::KEY);

        $this->assertStringContainsString("key=" . self::KEY, $url);
        $this->assertStringContainsString(CookieSupport::MARKER . "=1", $url);
    }

    /** KeyCreationController's redirect target ends in `#charge`. */
    public function testKeepsAFragmentAfterTheQuery(): void
    {
        $url = CookieSupport::withKeyCheck("https://metager.de/konto#charge", self::KEY);

        $this->assertStringEndsWith("#charge", $url);
        $this->assertStringContainsString("key=" . self::KEY, $url);
    }

    /**
     * SettingsController::loadSettings's redirect target can already carry
     * `key` (when it was among the synced settings) — the existing value
     * must survive untouched, not be overwritten by whatever the caller
     * passes in.
     */
    public function testDoesNotOverwriteAKeyAlreadyOnTheUrl(): void
    {
        $url = CookieSupport::withKeyCheck("https://metager.de/?key=existing", "different");

        $this->assertStringContainsString("key=existing", $url);
        $this->assertStringNotContainsString("key=different", $url);
    }

    public function testPreservesExistingQueryParameters(): void
    {
        $url = CookieSupport::withKeyCheck("https://metager.de/?eingabe=test", self::KEY);

        $this->assertStringContainsString("eingabe=test", $url);
        $this->assertStringContainsString("key=" . self::KEY, $url);
    }
}
