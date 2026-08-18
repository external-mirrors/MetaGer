<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The interface language is its own setting, in its own cookie, and nobody is
 * moved between domains for it.
 *
 * Three things used to be one. `web_setting_m` was the web fokus's market
 * filter *and* the interface language *and*, through `LocalizationRedirect`,
 * the reason a user was redirected to the other domain — so choosing to read
 * MetaGer in Spanish narrowed every search to Spain, and choosing a Spanish
 * market changed the interface. Crossing the domain boundary is what made that
 * expensive rather than merely wrong: separate origins, separate cookie jars,
 * and a signed hand-off URL carrying the user's whole settings jar (and, for a
 * while, not their key — see `CrossDomainSettingsMigrationTest`).
 *
 * Now `mg_locale` holds the language, `web_setting_m` holds the market, and
 * both domains serve every locale. `LocaleResolutionTest` covers the decision
 * itself; this covers what follows from it — that nothing redirects, that the
 * old cookie is migrated exactly once, and that the two settings have stopped
 * moving each other.
 */
class InterfaceLocaleCookieTest extends TestCase
{
    /** A page load, which is the only kind of request that is ever redirected. */
    private function navigate(string $url, array $cookies = [], ?string $acceptLanguage = null)
    {
        $headers = ["Sec-Fetch-Mode" => "navigate"];
        if ($acceptLanguage !== null) {
            $headers["Accept-Language"] = $acceptLanguage;
        }

        return $this->withUnencryptedCookies($cookies)->withHeaders($headers)->get($url);
    }

    /**
     * The redirect this whole change exists to remove. A German user on
     * metager.org was sent to metager.de, through a signed settings hand-off,
     * for no reason but the name of the domain.
     */
    public function testAGermanUserOnTheOtherDomainStaysThere(): void
    {
        $response = $this->navigate("http://metager.org/", [], "de-DE,de;q=0.9");

        $response->assertOk();
    }

    /** And the mirror image: metager.de serves English to an English browser. */
    public function testAnEnglishUserOnTheGermanDomainStaysThere(): void
    {
        $response = $this->navigate("http://metager.de/", [], "en-US,en;q=0.9");

        $response->assertOk();
    }

    /**
     * A stored language does not move anybody either — not to the other
     * domain, and not onto a prefixed URL. It changes what the unprefixed URL
     * renders, which is what a default is.
     */
    public function testAStoredLanguageDoesNotRedirect(): void
    {
        $response = $this->navigate("http://metager.de/", ["mg_locale" => "es-ES"], "de-DE,de;q=0.9");

        $response->assertOk();
        $this->assertSame("es-ES", app("config")->get("app.locale"), "the page should have rendered Spanish");
    }

    /**
     * The one-time move onto the new cookie: a browser whose language is still
     * in `web_setting_m` gets `mg_locale` written from it, and keeps the old
     * cookie — which is a perfectly good market filter and stays one.
     */
    public function testTheOldCookieIsMigratedOnceAndLeftInPlace(): void
    {
        $response = $this->navigate("http://metager.org/", ["web_setting_m" => "fr_FR"], "en-US,en;q=0.9");

        $response->assertPlainCookie("mg_locale", "fr-FR");
        $this->assertNull(
            $this->cookieFrom($response, "web_setting_m"),
            "The market filter must be left exactly as it is; only its second job ends."
        );
    }

    /** A browser that already has the new cookie is not written to again. */
    public function testAMigratedBrowserIsLeftAlone(): void
    {
        $response = $this->navigate(
            "http://metager.org/",
            ["mg_locale" => "en-US", "web_setting_m" => "fr_FR"],
            "en-US,en;q=0.9",
        );

        $this->assertNull($this->cookieFrom($response, "mg_locale"), "nothing to migrate, nothing to write");
    }

    /** The language selector writes the language, and not the search filter. */
    public function testTheLanguageSelectorWritesOnlyTheLanguage(): void
    {
        $response = $this->navigate("http://metager.org/es-ES/lang?switch=1", [], "en-US,en;q=0.9");

        $response->assertPlainCookie("mg_locale", "es-ES");
        $this->assertNull(
            $this->cookieFrom($response, "web_setting_m"),
            "Reading MetaGer in Spanish is not the same statement as searching Spain."
        );
    }

    /**
     * And the reverse direction, which is the one the user sees: changing the
     * market must leave the interface alone. It also pins `mg_locale`, so that
     * the value being written cannot afterwards be mistaken for a language by
     * the migration branch above.
     */
    public function testChangingTheMarketDoesNotChangeTheInterface(): void
    {
        $response = $this->withUnencryptedCookies([])
            ->withHeaders(["Accept-Language" => "de-DE,de;q=0.9"])
            ->post("http://metager.de/meta/settings/ef", ["focus" => "web", "m" => "fr_FR"]);

        $response->assertRedirect();
        $this->assertSame("fr_FR", $this->cookieFrom($response, "web_setting_m"), "the market was not stored");
        $this->assertSame("de-DE", $this->cookieFrom($response, "mg_locale"), "the interface language was not pinned");
    }

    /**
     * An unprefixed URL now renders whatever the cookie and the header ask
     * for, so a shared cache keyed on the URL alone would hand one visitor's
     * language to the next.
     */
    public function testResponsesSayTheyDependOnTheRequest(): void
    {
        $vary = strtolower((string) $this->navigate("http://metager.org/", [], "de-DE,de;q=0.9")->headers->get("Vary"));

        $this->assertStringContainsString("accept-language", $vary);
        $this->assertStringContainsString("cookie", $vary);
    }

    /** The value of a cookie this response sets, or `null` if it sets none. */
    private function cookieFrom($response, string $name): ?string
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie->getValue();
            }
        }

        return null;
    }
}
