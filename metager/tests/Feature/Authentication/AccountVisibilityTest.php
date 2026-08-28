<?php

namespace Tests\Feature\Authentication;

use App\Authentication\KeyUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A signed-in visitor can tell they are signed in, and which key they are on,
 * without opening /keys.
 *
 * Support gets regular calls from people who lost the `key` cookie, did not
 * realise they had ever "logged in", and made a second key — splitting their
 * token balance. These assertions pin the cues that answer that:
 *
 *   - one account pill, top right, on every page, carrying the mark, the key
 *     code and the balance;
 *   - a real account block at the top of the site menu, not a navigation row;
 *   - a signed-out startpage that leads with *log in*;
 *   - and the states that must NOT render an identity — the webextension, and a
 *     legacy key whose canonical form we cannot resolve.
 *
 * The result page is covered in
 * {@see \Tests\Feature\Search\AdvertisingRemovedTest::testTheResultPageMarksNoResultAsAPartnershop}
 * — a full search render is expensive and there is already one there.
 */
class AccountVisibilityTest extends TestCase
{
    /** A canonical UUID key, so the fingerprint is stable — see KeyUser::getKeyFingerprint. */
    private const KEY = "aaaaaaaa-bbbb-cccc-dddd-eeeeee123456";

    private function signInAs(string $key, float $charge, bool $temporary = false): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("")]);
        Cache::put("keyserver:key:" . $key, ["key" => $key, "charge" => $charge], now()->addMinutes(10));

        $user = new KeyUser($key);
        $user->temporary = $temporary;
        $this->be($user, "key");
    }

    public function testSignedInStartpageShowsTheAccountEverywhereItShould(): void
    {
        $this->signInAs(self::KEY, 142.0);

        $response = $this->get("/")->assertOk();

        // The pill: mark, key code, balance. 0x123456 = 1193046; % 12 = 6; * 30 = 180.
        $response->assertSee('id="account-pill"', false);
        $response->assertSee("account-pill--full", false);
        $response->assertSee("--account-mark-hue:180", false);
        $response->assertSee("123456", false);
        $response->assertSee("142", false);

        // The menu block, and the logout hook the breadcrumb and the
        // webextension both key off.
        $response->assertSee('class="sidebar-account"', false);
        $response->assertSee('id="sidebar-key-remove"', false);
        $response->assertSee(__("account.sidebar.manage"), false);
    }

    /**
     * The search bar carries nothing but the search.
     *
     * The key indicator inside it is gone from every page — see finding 13 in
     * the design review: on the result page it could only ever be green, and on
     * the startpage the pill says the same thing with room to say it properly.
     * #key-link went with it, and so did the dead Yahoo ad loader that read it.
     */
    public function testTheSearchBarNoLongerCarriesAKeyIndicator(): void
    {
        $this->signInAs(self::KEY, 142.0);

        $response = $this->get("/")->assertOk();

        $response->assertSee("startpage-searchbar", false);
        $response->assertDontSee('id="search-key"', false);
        $response->assertDontSee('id="key-link"', false);
        $response->assertDontSee("searchbar-img-key", false);
    }

    /**
     * An exhausted key can only be told about it here: every route to
     * /meta/meta.ger3 runs AuthenticationValidation, whose unauthorised branches
     * all redirect back to the startpage. So the warning has to land before the
     * search, and there must be exactly one of it — the old #startpage-quicklinks
     * row rendered alongside a search bar claiming a healthy balance.
     */
    public function testAnExhaustedKeyIsWarnedOnceOnTheStartpage(): void
    {
        $this->signInAs(self::KEY, 0.0);

        $response = $this->get("/")->assertOk();

        $response->assertSee('id="account-empty-alert"', false);
        $response->assertSeeText(__("account.empty.message"));
        $response->assertSee("account-pill--empty", false);
        $response->assertDontSee('id="startpage-quicklinks"', false);
    }

    /**
     * The webextension. It sends an `anonymous-token-key` header instead of the
     * key, so we hold neither an identity nor a balance — and saying so is the
     * arrangement working, not failing. A mark here would be drawn from a token
     * that rotates several times a day.
     */
    public function testAWebextensionVisitorIsShownAsAnonymousWithNoMarkAndNoBalance(): void
    {
        $this->signInAs("aaaaaaaa-bbbb-cccc-dddd-eeeeee999999", 142.0, temporary: true);

        $response = $this->get("/")->assertOk();

        $response->assertSee("account-pill--anonymous", false);
        $response->assertSee("account-mark--anonymous", false);
        $response->assertSeeText(__("account.pill.anonymous"));
        $response->assertDontSee("--account-mark-hue", false);
        $response->assertDontSee("999999", false);
        $response->assertSeeText(__("account.sidebar.anonymous_hint"));
        // Nothing to log out of: we never held the key.
        $response->assertDontSee('id="sidebar-key-remove"', false);

        // The one thing this visitor *can* be offered: the extension's own
        // settings, where the account it is holding for them actually is.
        // Rendered hidden, because without the extension nothing would happen —
        // its content script is what reveals it and answers the click.
        $response->assertSee('id="account-extension-settings" hidden>', false);

        // And the pill goes to the same place. It leads to /keys/key/enter in
        // every other state, which is exactly the page this visitor must not be
        // sent to: they have no key to enter, and entering one here would hand
        // us the identity the anonymous token exists to keep from us.
        $response->assertSee('data-extension-settings', false);
        $response->assertSee('/keys/help/anonymous-token', false);
        $response->assertDontSee('/keys/key/enter', false);
    }

    /**
     * The other side of the pill's destination: a visitor whose key we do hold
     * is managed on the website, and there is nothing about their pill for the
     * extension to take over.
     */
    public function testTheAccountPillLeadsToKeyManagementForAnOrdinaryVisitor(): void
    {
        $this->signInAs(self::KEY, 142.0);

        $response = $this->get("/")->assertOk();

        $response->assertSee('id="account-pill"', false);
        $response->assertSee('/keys/key/enter', false);
        $response->assertDontSee('data-extension-settings', false);
    }

    /**
     * The settings page used to carry the account itself: the full key in a
     * copy field, the balance, and its own logout button. All three now live in
     * the menu, which that page has like every other — and one logout link is
     * worth more than three, because each one needs the webextension to
     * intercept it (contentScripts/metagerPage.js) or it clears a cookie the
     * extension is not using and leaves the user signed in.
     */
    public function testTheSettingsPageDefersToTheMenuForTheAccount(): void
    {
        $this->signInAs(self::KEY, 142.0);

        $response = $this->get("/meta/settings?focus=web")->assertOk();

        $response->assertDontSee('id="remove-key"', false);
        $response->assertDontSee('id="metager-key"', false);
        $response->assertDontSee(self::KEY, false);

        // What replaces it, on the same page.
        $response->assertSee('id="account-pill"', false);
        $response->assertSee('id="sidebar-key-remove"', false);
    }

    /**
     * And so the settings page has no use for the master key at all.
     *
     * That matters on the other side of the wire: the webextension used to put
     * the real key on every `/meta/settings` request and strip the anonymous
     * token from it, because the account section could not be drawn without it
     * (MASTER_KEY_ROUTES, build/js/RequestTargets.js). It no longer does, so
     * this page is now reached with a temporary key like any other page — and
     * everything on it still has to work.
     *
     * The failure this guards against is quiet in both repositories. Adding
     * anything here that reads `Auth::guard("key")` for an identity, or
     * $authorization->key, renders blank or wrong for every extension user, and
     * looks perfectly fine in a browser that keeps its key in a cookie.
     */
    public function testTheSettingsPageWorksWithoutTheMasterKey(): void
    {
        $this->signInAs("aaaaaaaa-bbbb-cccc-dddd-eeeeee999999", 142.0, temporary: true);

        $response = $this->get("/meta/settings?focus=web")->assertOk();

        // The page itself: the engine pills and the blacklist are what people
        // come here for, and neither depends on who is asking.
        $response->assertSee(__("settings.header.2"), false);
        $response->assertSee(__("settings.header.4"), false);

        // The account, in the state the extension puts it in: no identity, no
        // balance, and the menu offering the extension's own popup instead of
        // a logout that would clear a cookie the extension is not using.
        $response->assertSee("account-pill--anonymous", false);
        $response->assertSeeText(__("account.pill.anonymous"));
        $response->assertSee('id="account-extension-settings" hidden>', false);
        $response->assertDontSee('id="sidebar-key-remove"', false);
    }

    public function testANonUuidKeyIsShownWithoutAnUnstableFingerprint(): void
    {
        // The keyserver folds a legacy non-UUID key into a UUID; when we cannot
        // resolve that canonical form the fingerprint would change between
        // requests, so it is dropped rather than shown. The balance is known,
        // so the pill still carries it.
        $this->signInAs("legacy-key-string", 999.0);

        $response = $this->get("/")->assertOk();

        $response->assertSee('id="account-pill"', false);
        $response->assertSee("account-mark--anonymous", false);
        $response->assertSeeText(__("account.pill.signed_in"));
        $response->assertSee("999", false);
        $response->assertDontSee("--account-mark-hue", false);
    }

    public function testSignedOutStartpageLeadsWithLogIn(): void
    {
        $response = $this->get("/")->assertOk();

        $response->assertSee("startpage-login-btn", false);
        $response->assertSee("/keys/key/enter", false);
        $response->assertSeeText(__("index.searchbar-replacement.have_key"));
        $response->assertSeeText(__("index.searchbar-replacement.first_time"));

        // The breadcrumb rewrites these three in place rather than revealing a
        // hidden element — see resources/js/accountBreadcrumb.js.
        $response->assertSee("data-hook-line", false);
        $response->assertSee("data-helper-line", false);
        $response->assertSee("data-login-button", false);
        $response->assertSee("data-welcome-back-button", false);

        // No account anywhere: no pill, and the menu block makes the offer.
        $response->assertDontSee('id="account-pill"', false);
        $response->assertSeeText(__("account.sidebar.logged_out"));
        $response->assertSee("sidebar-opener", false);
        $response->assertDontSee("sidebar-opener--account", false);
    }
}
