<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The settings page has to read the settings the way every other page does.
 *
 * A search setting reaches MetaGer three ways, and `SearchSettings::getSettingValue`
 * knows all three: a query parameter, a cookie, or a request header. The header is
 * how the webextension sends them — it keeps the settings in its own storage
 * precisely so the browser is not carrying them around, and attaches them per
 * request with a declarativeNetRequest rule. For those users `Cookie::get()` is
 * empty on every request, by design.
 *
 * So a page that reads a cookie directly is a page that is blank for every
 * extension user, and only for them. That is what the blacklist did: it was
 * stored, it was applied to their searches, and the box on this page came back
 * empty every time — which reads exactly like saving it had not worked.
 *
 * The engine toggles and the filters never had this problem: they go through
 * `Searchengines`, which asks `SearchSettings`.
 */
class SettingsPageHeaderSettingsTest extends TestCase
{
    /** The textarea the blacklist is rendered into. */
    private const FIELD = 'id="web-blacklist"';

    /**
     * Hosts that appear nowhere else on the page.
     *
     * The empty textarea carries `example.com`, `example2.com` and
     * `*.example3.com` as its placeholder, so asserting on those passes whether
     * the blacklist rendered or not — which is how the first version of this
     * file reported the bug as fixed.
     */
    private const BLOCKED = "blocked.test";
    private const ALSO_BLOCKED = "second-blocked.test";

    #[Test]
    public function shows_a_blacklist_that_arrived_as_a_request_header(): void
    {
        $response = $this->withHeader("web_blpage", self::BLOCKED . "," . self::ALSO_BLOCKED)
            ->get("/meta/settings?focus=web")
            ->assertOk();

        $response->assertSee(self::FIELD, false);
        $response->assertSee(self::BLOCKED, false);
        $response->assertSee(self::ALSO_BLOCKED, false);
    }

    #[Test]
    public function shows_a_blacklist_that_arrived_as_a_cookie(): void
    {
        // The other half of the same lookup, so fixing one cannot lose the other.
        $response = $this->withUnencryptedCookie("web_blpage", self::BLOCKED)
            ->get("/meta/settings?focus=web")
            ->assertOk();

        $response->assertSee(self::FIELD, false);
        $response->assertSee(self::BLOCKED, false);
    }

    #[Test]
    public function renders_a_wildcarded_entry_the_way_it_was_entered(): void
    {
        // `*.host` is parsed into a separate list and put back together for
        // display; a header has to travel the same path a cookie does.
        $response = $this->withHeader("web_blpage", "*." . self::BLOCKED)
            ->get("/meta/settings?focus=web")
            ->assertOk();

        $response->assertSee("*." . self::BLOCKED, false);
    }

    #[Test]
    public function marks_the_tab_of_another_fokus_that_has_a_blacklist(): void
    {
        // Only the open tab's pane is rendered, but every fokus is looked up:
        // the dot on a tab is how a fokus says it has been configured. So the
        // lookup cannot be the one `SearchSettings` already did in boot(),
        // which answers for the current fokus alone.
        $response = $this->withHeader("bilder_blpage", self::BLOCKED)
            ->get("/meta/settings?focus=web")
            ->assertOk();

        $response->assertSee("tab-dot", false);
    }

    #[Test]
    public function marks_no_tab_when_no_fokus_has_been_configured(): void
    {
        $this->get("/meta/settings?focus=web")
            ->assertOk()
            ->assertDontSee("tab-dot", false);
    }

    #[Test]
    public function counts_a_header_blacklist_as_a_setting_worth_resetting(): void
    {
        // The "reset all settings" row only renders when something is set, and
        // a blacklist is the one setting that can be the only one.
        $response = $this->withHeader("web_blpage", self::BLOCKED)
            ->get("/meta/settings?focus=web")
            ->assertOk();

        $response->assertSee(__("settings.reset"), false);
    }

    #[Test]
    public function offers_no_reset_when_nothing_is_configured(): void
    {
        $response = $this->get("/meta/settings?focus=web")->assertOk();

        $response->assertDontSee(__("settings.reset"), false);
        $response->assertDontSee(__("settings.resetAll"), false);
    }

    /**
     * The "reset every focus" button (`removeAllSettings`) went missing from
     * the settings page in a 2023 redesign — the route and handler stayed,
     * only the way in was gone. It sits next to the per-focus reset and
     * clears settings across every focus, so it renders under the same
     * "something is set" condition.
     */
    #[Test]
    public function renders_the_reset_every_focus_button_when_a_setting_is_set(): void
    {
        $response = $this->withHeader("web_blpage", self::BLOCKED)
            ->get("/meta/settings?focus=web")
            ->assertOk();

        $response->assertSee(__("settings.resetAll"), false);
        $response->assertSee(route("removeAllSettings"), false);
    }

    /**
     * A blacklist that lives only in another focus still counts — the button
     * is page-wide, and $settingActive now folds in every focus's
     * hasCustomSettings, not just the current one's engine/filter state.
     */
    #[Test]
    public function renders_the_reset_every_focus_button_for_a_setting_in_another_focus(): void
    {
        $response = $this->withHeader("nachrichten_blpage", self::BLOCKED)
            ->get("/meta/settings?focus=web")
            ->assertOk();

        $response->assertSee(__("settings.resetAll"), false);
    }

    /**
     * Deleting the last entry has to remove the setting, not empty it.
     *
     * An empty value is still a value: it keeps the "reset all settings" row on
     * a page with nothing set, and the webextension acts on removals, so a
     * setting it is never told to forget is one it keeps attaching to every
     * request — which is how the entry a user had just deleted came back on the
     * next page load.
     */
    #[Test]
    public function saving_an_empty_blacklist_removes_the_setting(): void
    {
        $response = $this->withHeader("web_blpage", self::BLOCKED)
            ->postJson("/meta/settings/nb", ["focus" => "web", "url" => "", "blacklist" => ""])
            ->assertOk();

        $this->assertContains("web_blpage", $response->json("remove"));
        $this->assertArrayNotHasKey("web_blpage", $response->json("set"));
    }

    #[Test]
    public function saving_a_blacklist_still_stores_the_entries(): void
    {
        $response = $this->postJson("/meta/settings/nb", [
            "focus" => "web",
            "url" => "",
            "blacklist" => self::BLOCKED,
        ])->assertOk();

        $this->assertSame(self::BLOCKED, $response->json("set.web_blpage"));
    }

    /**
     * Resetting the settings has to reach the blacklist too.
     *
     * The webextension asks for these endpoints as JSON and acts on the answer:
     * a setting it should drop appears as an expired cookie, which becomes a
     * `remove` entry it applies to its own storage. A setting the server never
     * queues a removal for is one the extension never hears about and keeps —
     * so "reset all settings" cleared every engine and filter and left the
     * blacklist in place, on every search, with nothing on the page still
     * offering to remove it.
     */
    #[Test]
    public function resetting_the_settings_removes_a_blacklist_sent_as_a_header(): void
    {
        $response = $this->withHeader("web_blpage", self::BLOCKED)
            ->postJson("/meta/settings/ds", ["focus" => "web", "url" => ""])
            ->assertOk();

        $this->assertContains("web_blpage", $response->json("remove"));
    }

    #[Test]
    public function resetting_the_settings_removes_a_blacklist_held_as_a_cookie(): void
    {
        // The browser's own path through the same method, so the merge above
        // cannot be what makes it work. `withCredentials`, because a JSON
        // request carries no cookies without it — the same rule the browser
        // applies to `fetch`, which is why the content script asks for
        // `credentials: "include"`.
        $response = $this->withUnencryptedCookie("web_blpage", self::BLOCKED)
            ->withCredentials()
            ->postJson("/meta/settings/ds", ["focus" => "web", "url" => ""])
            ->assertOk();

        $this->assertContains("web_blpage", $response->json("remove"));
    }
}
