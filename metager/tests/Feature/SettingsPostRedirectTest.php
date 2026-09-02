<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Characterization tests for the settings page's POST handlers, for a
 * cookie-*having* visitor — the baseline behaviour that
 * `SettingsUrlCarryTest` (settings-in-URL for cookie-blind visitors) must
 * not disturb.
 *
 * Each of `SettingsController`'s browser (non-JSON) handlers follows the
 * same shape: queue or forget a cookie, then `redirect()` to `route('settings', …)`
 * with a `#anchor`. This pins the exact redirect target and the exact
 * cookie queued/forgotten for each handler, so a later refactor that adds
 * URL-carrying on top cannot silently change either.
 */
class SettingsPostRedirectTest extends TestCase
{
    /**
     * `pixabay`, in the `bilder` fokus: enabled by default, not
     * hardcoded-disabled, and free (`cost => 0` in Pixabay.php) — unlike
     * `web`'s own engines, which all cost tokens an anonymous test request
     * has none of, so they start PAYMENT_REQUIRED-disabled and never reach
     * `disableSearchEngine`'s `!disabled` guard.
     */
    private const ENABLED_ENGINE = "pixabay";
    private const ENABLED_ENGINE_FOKUS = "bilder";

    /** `mojeek` is `disabledByDefault` (app/Models/parserSkripte/Mojeek.php), so it starts off. */
    private const DISABLED_BY_DEFAULT_ENGINE = "mojeek";

    private function cookieFrom($response, string $name): ?string
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie->getValue();
            }
        }
        return null;
    }

    private function isForgotten($response, string $name): bool
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie->getExpiresTime() > 0 && $cookie->getExpiresTime() < time();
            }
        }
        return false;
    }

    #[Test]
    public function disabling_an_enabled_by_default_engine_queues_an_off_cookie(): void
    {
        $response = $this->post("/meta/settings/de", ["suma" => self::ENABLED_ENGINE, "focus" => self::ENABLED_ENGINE_FOKUS]);

        $response->assertRedirect();
        $this->assertStringContainsString("/meta/settings", $response->headers->get("Location"));
        $this->assertStringEndsWith("#" . self::ENABLED_ENGINE_FOKUS . "-engines", $response->headers->get("Location"));
        $this->assertSame("off", $this->cookieFrom($response, self::ENABLED_ENGINE_FOKUS . "_engine_" . self::ENABLED_ENGINE));
    }

    #[Test]
    public function enabling_a_disabled_by_default_engine_queues_an_on_cookie(): void
    {
        $response = $this->post("/meta/settings/ee", ["suma" => self::DISABLED_BY_DEFAULT_ENGINE, "focus" => "web"]);

        $response->assertRedirect();
        $this->assertStringEndsWith("#web-engines", $response->headers->get("Location"));
        $this->assertSame("on", $this->cookieFrom($response, "web_engine_" . self::DISABLED_BY_DEFAULT_ENGINE));
    }

    #[Test]
    public function enabling_an_already_off_engine_that_defaults_on_forgets_the_cookie(): void
    {
        // "off" cookie already present (as if it had been disabled before);
        // re-enabling it forgets the cookie rather than writing "on", since
        // "on" is already the default for this engine.
        $response = $this->withUnencryptedCookie(self::ENABLED_ENGINE_FOKUS . "_engine_" . self::ENABLED_ENGINE, "off")
            ->post("/meta/settings/ee", ["suma" => self::ENABLED_ENGINE, "focus" => self::ENABLED_ENGINE_FOKUS]);

        $response->assertRedirect();
        $this->assertTrue($this->isForgotten($response, self::ENABLED_ENGINE_FOKUS . "_engine_" . self::ENABLED_ENGINE));
    }

    #[Test]
    public function changing_the_market_filter_stores_it_and_redirects_to_the_filter_anchor(): void
    {
        $response = $this->post("/meta/settings/ef", ["focus" => "web", "m" => "fr_FR"]);

        $response->assertRedirect();
        $this->assertStringEndsWith("#web-filter", $response->headers->get("Location"));
        $this->assertSame("fr_FR", $this->cookieFrom($response, "web_setting_m"));
    }

    #[Test]
    public function resetting_a_filter_to_its_default_forgets_the_cookie(): void
    {
        $response = $this->withUnencryptedCookie("web_setting_m", "fr_FR")
            ->post("/meta/settings/ef", ["focus" => "web", "m" => ""]);

        $response->assertRedirect();
        $this->assertTrue($this->isForgotten($response, "web_setting_m"));
    }

    /**
     * Regression test for a bug in `enableFilter`, surfaced while adding
     * settings-in-URL carrying: it used to build its filter list from
     * `$request->except(["focus", "url"])` and then, for *any* key in that
     * list with an empty value, unconditionally forgot a cookie named
     * `{fokus}_setting_{key}` — with no check that `{key}` was an actual
     * filter's get-parameter first. Harmless as long as every extra request
     * parameter had a non-empty value, which held until this form's own
     * `action` URL could start carrying settings forward for a cookie-blind
     * visitor: nothing stops an unrelated, empty-valued query parameter
     * (accidental or crafted) from riding along next to `focus`/`url` and
     * being read as "reset this filter to its default". Fixed by only ever
     * reading the request for get-parameters `SearchEngineRegistry` actually
     * lists as filters.
     */
    #[Test]
    public function an_unrelated_empty_query_parameter_does_not_forget_an_unrelated_cookie(): void
    {
        $response = $this->post("/meta/settings/ef?junk=", ["focus" => "web", "m" => "fr_FR"]);

        $response->assertRedirect();
        $this->assertFalse($this->isForgotten($response, "web_setting_junk"));
        $this->assertSame("fr_FR", $this->cookieFrom($response, "web_setting_m"));
    }

    #[Test]
    public function changing_a_global_setting_redirects_to_more_settings(): void
    {
        $response = $this->post("/meta/settings/es", ["focus" => "web", "dark_mode" => "dark"]);

        $response->assertRedirect();
        $this->assertStringEndsWith("#more-settings", $response->headers->get("Location"));
        $this->assertSame("dark", $this->cookieFrom($response, "dark_mode"));
    }

    #[Test]
    public function resetting_a_global_setting_to_system_forgets_the_cookie(): void
    {
        $response = $this->withUnencryptedCookie("dark_mode", "dark")
            ->post("/meta/settings/es", ["focus" => "web", "dark_mode" => "system"]);

        $response->assertRedirect();
        $this->assertTrue($this->isForgotten($response, "dark_mode"));
    }

    #[Test]
    public function saving_a_blacklist_stores_the_joined_hostnames(): void
    {
        $response = $this->post("/meta/settings/nb", ["focus" => "web", "blacklist" => "blocked.test\nsecond-blocked.test"]);

        $response->assertRedirect();
        $this->assertStringEndsWith("#web-bl", $response->headers->get("Location"));
        $this->assertSame("blocked.test,second-blocked.test", $this->cookieFrom($response, "web_blpage"));
    }

    #[Test]
    public function saving_an_empty_blacklist_forgets_the_cookie(): void
    {
        $response = $this->withUnencryptedCookie("web_blpage", "blocked.test")
            ->post("/meta/settings/nb", ["focus" => "web", "blacklist" => ""]);

        $response->assertRedirect();
        $this->assertTrue($this->isForgotten($response, "web_blpage"));
    }

    #[Test]
    public function deleting_the_blacklist_cookie_by_key_forgets_it(): void
    {
        $response = $this->withUnencryptedCookie("web_blpage", "blocked.test")
            ->post("/meta/settings/db", ["focus" => "web", "cookieKey" => "web_blpage"]);

        $response->assertRedirect();
        $this->assertStringEndsWith("#web-bl", $response->headers->get("Location"));
        $this->assertTrue($this->isForgotten($response, "web_blpage"));
    }

    #[Test]
    public function clearing_the_blacklist_forgets_every_matching_cookie(): void
    {
        $response = $this->withUnencryptedCookie("web_blpage", "blocked.test")
            ->post("/meta/settings/cb", ["focus" => "web"]);

        $response->assertRedirect();
        $this->assertTrue($this->isForgotten($response, "web_blpage"));
    }

    #[Test]
    public function deleting_all_settings_forgets_engine_setting_and_global_cookies(): void
    {
        $response = $this->withUnencryptedCookies([
            "web_engine_" . self::ENABLED_ENGINE => "off",
            "web_setting_m" => "fr_FR",
            "tips" => "off",
        ])->post("/meta/settings/ds", ["focus" => "web"]);

        $response->assertRedirect();
        $this->assertStringNotContainsString("#", $response->headers->get("Location"));
        $this->assertTrue($this->isForgotten($response, "web_engine_" . self::ENABLED_ENGINE));
        $this->assertTrue($this->isForgotten($response, "web_setting_m"));
        $this->assertTrue($this->isForgotten($response, "tips"));
    }

    #[Test]
    public function removing_one_setting_by_key_forgets_it(): void
    {
        $response = $this->withUnencryptedCookie("tips", "off")
            ->post("/meta/settings/all-settings/removeOne", ["key" => "tips", "url" => "https://metager.de"]);

        $response->assertRedirect("https://metager.de");
        $this->assertTrue($this->isForgotten($response, "tips"));
    }

    /**
     * "Reset all settings" forgets every setting, in every fokus — global
     * settings, engine toggles, filters and blacklists alike. It used to
     * loop only `SearchSettings::user_settings`, which `boot()` fills one
     * `getSettingValue()` call at a time with global settings and the
     * *current* fokus's blacklist, so engine toggles (tracked on
     * `Searchengines`) and any other fokus's settings were silently left in
     * place. `SettingsUrlCarryTest` covers the same widening for the
     * cookie-blind (query-carried) case.
     */
    #[Test]
    public function removing_all_settings_forgets_every_fokus_setting(): void
    {
        $response = $this->withUnencryptedCookies([
            "tips" => "off",
            "web_engine_" . self::DISABLED_BY_DEFAULT_ENGINE => "on",
            "web_setting_m" => "fr_FR",
            "nachrichten_engine_" . self::DISABLED_BY_DEFAULT_ENGINE => "on",
            "bilder_blpage" => "blocked.test",
        ])->post("/meta/settings/all-settings/removeAll", ["url" => "https://metager.de"]);

        $response->assertRedirect("https://metager.de");
        $this->assertTrue($this->isForgotten($response, "tips"));
        $this->assertTrue($this->isForgotten($response, "web_engine_" . self::DISABLED_BY_DEFAULT_ENGINE));
        $this->assertTrue($this->isForgotten($response, "web_setting_m"));
        // A fokus other than the request's — the case the old user_settings
        // loop could never reach.
        $this->assertTrue($this->isForgotten($response, "nachrichten_engine_" . self::DISABLED_BY_DEFAULT_ENGINE));
        $this->assertTrue($this->isForgotten($response, "bilder_blpage"));
    }

    /**
     * The auth cookie is literally named `key` and `key` is one of
     * `SettingsSchema::globalSettingKeys()`, so a naive "forget every valid
     * setting" loop would log the visitor out. `removeAllSettings` must
     * leave it alone, the same way `deleteSettings` does.
     */
    #[Test]
    public function removing_all_settings_does_not_forget_the_auth_key(): void
    {
        $response = $this->withUnencryptedCookies([
            "key" => "aaaaaaaa-bbbb-4ccc-9ddd-eeeeee123456",
            "tips" => "off",
        ])->post("/meta/settings/all-settings/removeAll", ["url" => "https://metager.de"]);

        $response->assertRedirect("https://metager.de");
        $this->assertTrue($this->isForgotten($response, "tips"));
        $this->assertFalse($this->isForgotten($response, "key"));
    }
}
