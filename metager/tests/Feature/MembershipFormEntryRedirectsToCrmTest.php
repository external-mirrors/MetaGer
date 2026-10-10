<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * `GET /membership` (no `$application_id`) — the entry point every "become a
 * member" link on this site points at (the sidebar, the landing page, the
 * donation page's upsell, AccountController's own membershipUrl) — now sends
 * a brand-new visitor straight to suma-crm's own native application form
 * instead of rendering this app's legacy multi-step one.
 *
 * That redirect is all that is left of membership in this app. suma-crm
 * owns the application, its review, the welcome mail and the member portal;
 * suma-payments owns reminders and collecting the fee. The legacy multi-step
 * form, its admin review, its mails and the CiviCRM client behind them were
 * deleted — see MembershipCivicrmCronsRemovedTest for why leaving any of it
 * running against a frozen CiviCRM is not harmless.
 */
class MembershipFormEntryRedirectsToCrmTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(["metager.metager.crm.url" => "https://crm.example.com"]);
    }

    public function testAFreshVisitorIsRedirectedToSumaCrm(): void
    {
        $this->get("/de-DE/membership")
            ->assertRedirect("https://crm.example.com/mitglied-werden?lang=de-DE");
    }

    /**
     * suma-crm's own App\Localization\CrmLocale::resolve() mirrors this same
     * `?lang=` parameter and priority order specifically so a visitor
     * arriving from here resolves identically to one MetaGer never touched
     * at all — see that class's own docblock in the suma-crm repository.
     *
     * `de-AT`, not `en-US`: this route only exists for German-family
     * visitors at all (MembershipAdvertisingTest's own "a member is not
     * sent to a german form from an english page" covers the canonicalizing
     * redirect a non-German locale prefix gets sent through before this
     * controller ever runs) — `de-AT` is a real, fully-supported German
     * regional locale distinct from the site's own de-DE default, so it
     * reaches the controller without that redirect and still proves the
     * locale carried through is the one actually resolved for this request,
     * not a hardcoded default.
     */
    public function testTheLocaleFollowsTheUrlPrefix(): void
    {
        $this->get("/de-AT/membership")
            ->assertRedirect("https://crm.example.com/mitglied-werden?lang=de-AT");
    }

    /**
     * `CookieCarryingUrlGenerator` already carries `key` into every other
     * same-origin link for a cookie-blind visitor; this away() redirect
     * bypasses that generator entirely (see LoginController's own comment on
     * why redirect()->away() does), so the controller has to carry it by
     * hand instead.
     */
    public function testAKeyRidingInTheQueryIsCarriedAlong(): void
    {
        $this->get("/de-DE/membership?key=5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15")
            ->assertRedirect("https://crm.example.com/mitglied-werden?lang=de-DE&key=5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15");
    }

    /**
     * An already-recognised MetaGer user (via cookie, not just the URL) gets
     * the same treatment — keyOfVisitor() reads the same sources, in the same
     * order, as the key guard, so someone signed in when they click "become a member"
     * lands on suma-crm's form already tied to their own key.
     */
    public function testAKeyKnownOnlyByCookieIsCarriedAlong(): void
    {
        $this->withUnencryptedCookies(["key" => "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15"])
            ->get("/de-DE/membership")
            ->assertRedirect("https://crm.example.com/mitglied-werden?lang=de-DE&key=5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15");
    }

    /**
     * Applying from inside the MetaGer app: suma-crm hands the key back to
     * the app from its thanks page ({@see \App\Landing\AppCallback}), which
     * it can only do if the app's markers arrive there.
     */
    public function testTheAppCallbackMarkersAreCarriedAlong(): void
    {
        $this->get("/de-DE/membership?keystore=release&variant=fdroid")
            ->assertRedirect("https://crm.example.com/mitglied-werden?lang=de-DE&keystore=release&variant=fdroid");
    }

    /** Where the app's key-less flow starts: the landing page /keys redirects to. */
    public function testTheLandingPageMembershipLinksKeepTheAppCallbackMarkers(): void
    {
        $html = $this->get("/de-DE/?keystore=release&variant=fdroid")->assertOk()->getContent();

        preg_match_all('~href="([^"]*/membership[^"]*)"~', $html, $matches);
        $this->assertNotEmpty($matches[1]);
        foreach ($matches[1] as $href) {
            $this->assertStringContainsString("keystore=release&variant=fdroid", html_entity_decode($href), $href);
        }
    }

    /**
     * Resume links in mails the legacy form sent out, and bookmarked steps,
     * still point at `/membership/{id}`. The form behind them is gone, so
     * they land where a new application starts.
     */
    public function testALegacyResumeLinkIsRedirectedToSumaCrm(): void
    {
        $this->get("/de-DE/membership/0b6f1f4e-6f0e-4a8e-9d3c-2a1b5c7d9e0f?keystore=release&variant=fdroid")
            ->assertRedirect("https://crm.example.com/mitglied-werden?lang=de-DE&keystore=release&variant=fdroid");
    }

    /**
     * Every other legacy membership URL is gone, the PayPal webhook included:
     * suma-payments receives PayPal's webhooks now.
     */
    public function testTheLegacyFormEndpointsAreGone(): void
    {
        foreach (["membership_success", "membership_abort", "membership_paypal_authorized", "membership_paypal_cancelled", "membership_admin_overview", "membership_admin_accept", "membership_admin_deny", "membership_admin_reduction"] as $name) {
            $this->assertFalse(\Route::has($name), "route $name should be gone");
        }

        $uris = collect(\Route::getRoutes()->getRoutes())->map(fn($route) => $route->uri());
        $this->assertNotContains("membership/webhook/paypal", $uris);
        $this->assertNotContains("membership/token", $uris);
    }
}
