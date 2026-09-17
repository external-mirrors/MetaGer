<?php

namespace Tests\Feature;

use App\Models\Membership\MembershipApplication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * `GET /membership` (no `$application_id`) — the entry point every "become a
 * member" link on this site points at (the sidebar, the landing page, the
 * donation page's upsell, AccountController's own membershipUrl) — now sends
 * a brand-new visitor straight to suma-crm's own native application form
 * instead of rendering this app's legacy multi-step one.
 *
 * An `$application_id` (a resume link an earlier email sent out, or a
 * bookmarked step) is untouched by this and keeps rendering the legacy form
 * exactly as before — see MembershipKeyTest/MembershipCrmHandoverTest for
 * that path's own coverage.
 */
class MembershipFormEntryRedirectsToCrmTest extends TestCase
{
    use DatabaseTransactions;

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
     * reaches contactData() without that redirect and still proves the
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
     * the same treatment — the same keyOfVisitor() check
     * submitMembershipForm() itself uses to decide whether a visitor already
     * has a key, so someone signed in when they click "become a member"
     * lands on suma-crm's form already tied to their own key.
     */
    public function testAKeyKnownOnlyByCookieIsCarriedAlong(): void
    {
        $this->withUnencryptedCookies(["key" => "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15"])
            ->get("/de-DE/membership")
            ->assertRedirect("https://crm.example.com/mitglied-werden?lang=de-DE&key=5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15");
    }

    public function testAnInFlightApplicationStillRendersTheLegacyForm(): void
    {
        $application = MembershipApplication::create(["locale" => "de-DE"]);

        $this->get("/de-DE/membership/{$application->id}")
            ->assertOk()
            ->assertDontSee("crm.example.com", false);
    }
}
