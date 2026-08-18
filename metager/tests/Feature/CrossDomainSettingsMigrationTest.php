<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Changing language across the domain boundary must not cost the user their
 * key.
 *
 * metager.de and metager.org are separate origins with separate cookie jars,
 * so `LocalizationRedirect` hands everything over explicitly: it builds a
 * signed `load-settings` URL on the target domain carrying the current
 * settings, and `SettingsController::loadSettings()` writes them back as
 * cookies there.
 *
 * "Everything" was collected by pattern — `*_setting_*` and `*_engine_*` — and
 * the key cookie matches neither. So a user switching language got their
 * search settings across intact and arrived signed out, with the credential
 * that pays for their searches left behind on a domain they were just
 * redirected away from. `loadSettings()` has always had a branch for `key`,
 * and `SettingsController::index()`'s own migration link has always sent it,
 * which is what makes this an omission rather than a policy.
 */
class CrossDomainSettingsMigrationTest extends TestCase
{
    public function testTheKeyIsCarriedToTheOtherDomain(): void
    {
        $location = $this->migrationRedirectLocation();

        parse_str((string) parse_url($location, PHP_URL_QUERY), $params);

        $this->assertSame(
            "the-users-key",
            $params["key"] ?? null,
            "The key was not handed over, so this language switch signs the user out."
        );
    }

    /**
     * The rest of the migration still works — this is a addition, not a
     * replacement for what was already being carried.
     */
    public function testSearchSettingsAreStillCarried(): void
    {
        $location = $this->migrationRedirectLocation();

        parse_str((string) parse_url($location, PHP_URL_QUERY), $params);

        $this->assertSame("example.org", $params["web_setting_sitesearch"] ?? null);
        $this->assertSame("en_US", $params["web_setting_m"] ?? null);
        $this->assertArrayHasKey("signature", $params, "An unsigned link is ignored by loadSettings().");
    }

    /**
     * And the far end accepts what was sent: the key comes back out as a
     * cookie on the new domain. Asserting only on the URL would pass just as
     * happily if `loadSettings()` quietly dropped the parameter again.
     */
    public function testTheOtherDomainSetsTheKeyCookie(): void
    {
        $response = $this->withHeaders(["Sec-Fetch-Mode" => "navigate"])
            ->get($this->migrationRedirectLocation());

        // Plain, not encrypted: bootstrap/app.php removes EncryptCookies from these groups.
        $response->assertPlainCookie("key", "the-users-key");
    }

    /**
     * A German user carrying an explicit `en-US` language setting on
     * metager.de — the case that sends someone to metager.org and therefore
     * across the cookie-jar boundary.
     */
    private function migrationRedirectLocation(): string
    {
        $response = $this->withUnencryptedCookies([
            "web_setting_m" => "en_US",
            "key" => "the-users-key",
            "web_setting_sitesearch" => "example.org",
        ])->withHeaders(["Sec-Fetch-Mode" => "navigate"])->get("http://metager.de/");

        $response->assertRedirect();
        $location = $response->headers->get("location");

        $this->assertStringContainsString(
            "metager.org",
            (string) $location,
            "Expected to be sent to the other domain; the rest of this test has nothing to observe otherwise."
        );

        return (string) $location;
    }
}
