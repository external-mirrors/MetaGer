<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * Settings-in-URL for a cookie-blind visitor: a setting changed on
 * /meta/settings with no cookie support has to keep taking effect after the
 * response that changed it — carried in the redirect, and from there into
 * every generated same-origin link and form, the same way
 * `App\Authentication\CookieSupport::keyMissingCookie()` already does for
 * `key` (`CookieCarryingUrlGeneratorTest`, `CookieBlindNoticeTest`).
 *
 * `SettingsPostRedirectTest` pins the cookie-*having* baseline this must not
 * disturb; `SettingsCarryTest` covers the underlying `App\Http\SettingsCarry`
 * predicate directly. This file is about the visible, end-to-end behaviour:
 * dispatching requests with no cookie jar at all.
 */
class SettingsUrlCarryTest extends TestCase
{
    use FakesSearchEngines;

    /** Same choice as SettingsPostRedirectTest, and for the same reason: free, enabled by default, real fokus. */
    private const ENGINE = "pixabay";
    private const ENGINE_FOKUS = "bilder";

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function locationOf($response): string
    {
        return (string) $response->headers->get("Location");
    }

    // ── A setting survives navigation ───────────────────────────────────

    #[Test]
    public function a_global_setting_survives_into_the_redirect_and_the_next_page(): void
    {
        $response = $this->post("/meta/settings/es", ["focus" => "web", "dark_mode" => "dark"]);
        $response->assertRedirect();
        $location = $this->locationOf($response);
        $this->assertStringContainsString("dark_mode=dark", $location);

        $page = $this->get($location)->assertOk();
        $page->assertSee('name="dark_mode"', false);
        // The market-filter form's action carries it forward too, not just
        // the URL this response happened to redirect to.
        $page->assertSee("dark_mode=dark", false);
    }

    #[Test]
    public function an_engine_toggle_survives_into_the_redirect_and_the_next_page(): void
    {
        $response = $this->post("/meta/settings/de", ["suma" => self::ENGINE, "focus" => self::ENGINE_FOKUS]);
        $response->assertRedirect();
        $location = $this->locationOf($response);
        $cookieName = self::ENGINE_FOKUS . "_engine_" . self::ENGINE;
        $this->assertStringContainsString("$cookieName=off", $location);

        $page = $this->get($location)->assertOk();
        $page->assertSee("$cookieName=off", false);
    }

    #[Test]
    public function a_filter_survives_into_the_redirect_and_the_next_page(): void
    {
        $response = $this->post("/meta/settings/ef", ["focus" => "web", "m" => "fr_FR"]);
        $response->assertRedirect();
        $location = $this->locationOf($response);
        $this->assertStringContainsString("web_setting_m=fr_FR", $location);

        $page = $this->get($location)->assertOk();
        $page->assertSee("web_setting_m=fr_FR", false);
    }

    #[Test]
    public function a_blacklist_survives_into_the_redirect_and_the_next_page(): void
    {
        $response = $this->post("/meta/settings/nb", ["focus" => "web", "blacklist" => "blocked.test"]);
        $response->assertRedirect();
        $location = $this->locationOf($response);
        $this->assertStringContainsString("web_blpage=blocked.test", $location);

        $page = $this->get($location)->assertOk();
        $page->assertSee("blocked.test", false);
    }

    /**
     * The nav logo (`layouts/subPages.blade.php`) is built through
     * `LaravelLocalization::getLocalizedURL()`, which never goes through
     * `route()`/`to()` — it needs `CookieSupport::carryIntoUrl()` called on
     * it explicitly (see that method's docblock). This is the regression
     * test for that second call site, not just the `CookieCarryingUrlGenerator`
     * one `CookieCarryingUrlGeneratorTest` already covers directly.
     */
    #[Test]
    public function a_setting_is_carried_into_the_nav_logo_link_too(): void
    {
        $page = $this->get("/meta/settings?focus=web&dark_mode=dark")->assertOk();

        $page->assertSee('id="subpage-logo"', false);
        $page->assertSee("dark_mode=dark", false);
    }

    /**
     * The startpage's own search form (`parts/searchbar.blade.php`) is
     * `method="GET"`: submitting it replaces `action`'s query string
     * outright rather than merging with it, unlike a `route()` link — so a
     * carried setting needs an explicit hidden input here, the same way
     * `key` already has one just above it in that partial.
     */
    /**
     * The signed-out startpage is the landing page and does not render the
     * search form at all (`StartpageLandingTest`), so this needs a signed-in
     * visitor to reach it — `actingAsSearchUser()`'s own KeyUser sign-in,
     * matching `StartpageLandingTest::signIn()`'s pattern.
     */
    #[Test]
    public function a_setting_is_carried_as_a_hidden_input_on_the_startpage_search_form(): void
    {
        $this->actingAsSearchUser();

        $page = $this->get("/?dark_mode=dark")->assertOk();

        $page->assertSee('id="searchForm"', false);
        $page->assertSee('name="dark_mode" value="dark"', false);
    }

    #[Test]
    public function a_setting_is_carried_as_a_hidden_input_on_the_result_page_search_form(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $page = $this->get("/meta/meta.ger3?eingabe=test&focus=web&dark_mode=dark")->assertOk();

        $page->assertSee('id="searchForm"', false);
        $page->assertSee('name="dark_mode" value="dark"', false);
    }

    // ── Turning a setting back off ───────────────────────────────────────

    #[Test]
    public function an_engine_can_be_turned_back_on_while_carried(): void
    {
        $cookieName = self::ENGINE_FOKUS . "_engine_" . self::ENGINE;
        $response = $this->get("/meta/settings?focus=" . self::ENGINE_FOKUS . "&$cookieName=off")
            ->assertOk();
        // Confirm the pill really is being carried before testing its removal.
        $response->assertSee("$cookieName=off", false);

        $enable = $this->post("/meta/settings/ee?$cookieName=off", ["suma" => self::ENGINE, "focus" => self::ENGINE_FOKUS]);
        $enable->assertRedirect();
        $this->assertStringNotContainsString($cookieName, $this->locationOf($enable));
    }

    #[Test]
    public function a_global_setting_can_be_reset_while_carried(): void
    {
        $reset = $this->post("/meta/settings/es?dark_mode=dark", ["focus" => "web", "dark_mode" => "system"]);
        $reset->assertRedirect();
        $this->assertStringNotContainsString("dark_mode", $this->locationOf($reset));
    }

    #[Test]
    public function a_filter_can_be_reset_to_default_while_carried(): void
    {
        $reset = $this->post("/meta/settings/ef?web_setting_m=fr_FR", ["focus" => "web", "m" => ""]);
        $reset->assertRedirect();
        $this->assertStringNotContainsString("web_setting_m", $this->locationOf($reset));
    }

    #[Test]
    public function the_blacklist_can_be_cleared_while_carried(): void
    {
        $clear = $this->post("/meta/settings/cb?web_blpage=blocked.test", ["focus" => "web"]);
        $clear->assertRedirect();
        $this->assertStringNotContainsString("web_blpage", $this->locationOf($clear));
    }

    /**
     * `deleteSettings` and `clearBlacklist` must see settings that only ever
     * arrived by query, not just what happens to be in `Cookie::get()` —
     * the whole point of this feature for a cookie-blind visitor.
     */
    #[Test]
    public function reset_all_settings_drops_every_carried_setting_with_no_cookies_present(): void
    {
        $cookieName = "web_engine_bing";
        $reset = $this->post("/meta/settings/ds?$cookieName=off&web_setting_m=fr_FR", ["focus" => "web"]);

        $reset->assertRedirect();
        $location = $this->locationOf($reset);
        $this->assertStringNotContainsString($cookieName, $location);
        $this->assertStringNotContainsString("web_setting_m", $location);
    }

    #[Test]
    public function removing_one_setting_drops_it_from_the_redirect_with_no_cookie_present(): void
    {
        $remove = $this->post("/meta/settings/all-settings/removeOne?dark_mode=dark", [
            "key" => "dark_mode",
            "url" => "https://metager.de",
        ]);

        $remove->assertRedirect();
        $this->assertStringNotContainsString("dark_mode", $this->locationOf($remove));
    }

    // ── The backup/restore link ──────────────────────────────────────────

    /**
     * SettingsController::index()'s $cookieLink used to source only headers
     * and cookies, so a cookie-blind visitor's own settings — which live
     * only in the query — never appeared in their own backup link.
     */
    #[Test]
    public function the_backup_link_includes_a_setting_that_only_arrived_by_query(): void
    {
        $page = $this->get("/meta/settings?focus=web&dark_mode=dark")->assertOk();

        $page->assertSee("dark_mode", false);
        $page->assertSee("id=\"loadSettings\"", false);
    }

    // ── loadSettings() forwards to the startpage ────────────────────────

    #[Test]
    public function load_settings_forwards_a_query_supplied_setting_to_the_startpage_redirect(): void
    {
        $response = $this->get("/meta/settings/load-settings?dark_mode=dark")->assertRedirect();

        $this->assertStringContainsString("dark_mode=dark", $this->locationOf($response));
    }

    // ── Independence from unrelated cookie-having settings ──────────────

    /**
     * Checked against `route()`'s own output (the focus-tab links, built via
     * `route('settings', …)` in settings/index.blade.php), not the whole
     * page: the backup link deliberately includes every active setting —
     * cookie-sourced ones too, per `index()`'s own `$cookieLink` merge — so
     * asserting page-wide would conflate "is on the backup link" with "is
     * being carried into generated URLs", which is what this actually tests.
     */
    #[Test]
    public function a_setting_with_a_cookie_is_not_carried_while_another_without_one_is(): void
    {
        $request = \Illuminate\Http\Request::create("/meta/settings?focus=web&dark_mode=dark");
        $request->cookies->set("new_tab", "on");
        $this->app->instance('request', $request);

        $tabLink = route("settings", ["focus" => "bilder", "url" => ""]);

        $this->assertStringContainsString("dark_mode=dark", $tabLink);
        $this->assertStringNotContainsString("new_tab=on", $tabLink);
    }
}
