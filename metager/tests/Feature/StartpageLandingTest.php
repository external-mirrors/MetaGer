<?php

namespace Tests\Feature;

use App\Authentication\KeyUser;
use App\Landing\KeymanagerLinks;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The startpage in its two states, after the keymanager's landing page moved
 * here.
 *
 * `/keys` used to serve a full marketing page — hero, "how it works", five
 * benefit cards — from the keymanager service, while `/` served a 420px card
 * that said "log in" and four story sections about the association. The two
 * were explaining the same product to the same people out of two codebases, and
 * only one of them was on the domain anyone types.
 *
 * So the split is no longer by service, it is by whether the visitor has a key:
 *
 *   signed out — the landing page, then who runs MetaGer, then install;
 *   signed in  — a search engine, then who runs MetaGer, then install.
 *
 * Both halves are pinned here, and so is the boundary between them: the test
 * that matters most is {@see testTheSignedInStartpageIsStillASearchEngine},
 * because the way this change goes wrong later is that the marketing quietly
 * leaks into the page people use every day.
 *
 * The `/keys` links left are the key flow only — creating and entering a key.
 * Prices and the two help pages moved here in the second migration step.
 * Those that remain still carry the MetaGer
 * app's callback markers — see {@see App\Landing\KeymanagerLinks}.
 */
class StartpageLandingTest extends TestCase
{
    /** A canonical UUID key, so KeyUser::getKeyFingerprint is stable. */
    private const KEY = "aaaaaaaa-bbbb-cccc-dddd-eeeeee123456";

    private function signIn(float $charge = 142.0): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("")]);
        Cache::put(
            "keyserver:key:" . self::KEY,
            ["key" => self::KEY, "charge" => $charge],
            now()->addMinutes(10)
        );

        $this->be(new KeyUser(self::KEY), "key");
    }

    // ── Signed out: the landing page ─────────────────────────────────────────

    public function testTheSignedOutStartpageIsTheLandingPage(): void
    {
        $response = $this->get("/")->assertOk();

        // The hero, in the order it reads: brand line, the claim, the promise,
        // the four things MetaGer does not do.
        $response->assertSee('id="landing-hero"', false);
        $response->assertSeeText(__("index.searchbar-replacement.tagline"));
        $response->assertSeeText(__("index.landing.title"));
        $response->assertSeeText(__("index.landing.description"));
        $response->assertSeeText(__("index.landing.advantages.ads"));
        $response->assertSeeText(__("index.landing.advantages.tracking"));
        $response->assertSeeText(__("index.landing.advantages.logging"));
        $response->assertSeeText(__("index.landing.advantages.compromise"));

        // The three steps, which are the only place the site answers "why do I
        // need a key" and "what does it cost".
        $response->assertSee('id="how-it-works"', false);
        $response->assertSeeText(__("index.landing.howitworks.steps.0.heading"));
        $response->assertSeeText(__("index.landing.howitworks.steps.1.heading"));
        $response->assertSeeText(__("index.landing.howitworks.steps.2.heading"));

        // The five benefit cards.
        foreach (["browsing", "ads", "logging", "compromise", "efficiency"] as $benefit) {
            $response->assertSeeText(__("index.landing.benefits.$benefit.heading"));
        }

        // And the two bands both states share.
        $response->assertSee('id="landing-org"', false);
        $response->assertSee('id="landing-install"', false);
    }

    /**
     * The membership note existed in the keymanager's de and en catalogues and
     * was never rendered: the include that would have printed it had been
     * dropped, and the only way to notice was to read the JSON. Members of
     * SUMA-EV search without paying again, which is worth a line on the page
     * that explains paying.
     *
     * German only, because a membership is: {@see MembershipAdvertisingTest}
     * has that rule and both halves of it. A plain `/` in a feature test is
     * English — the host is `localhost` — so the locale has to be named here.
     */
    public function testTheMembershipNoteIsActuallyRendered(): void
    {
        $response = $this->get("/de-DE/")->assertOk();

        $response->assertSee("landing-steps__membership", false);
        $response->assertSee(route("membership_form"), false);
    }

    /**
     * Log in stays the primary action and creating a key stays a link, because
     * most people who land here have used MetaGer before and only lost the
     * cookie — a second key splits their token balance.
     *
     * The three data-* hooks are what resources/js/accountBreadcrumb.js rewrites
     * in place when this browser has rendered a signed-in page before. All three
     * have to sit inside #searchbar-replacement, which is the element that
     * module looks up.
     */
    public function testTheSignedOutStartpageStillLeadsWithLogIn(): void
    {
        $response = $this->get("/")->assertOk();

        $response->assertSee("startpage-login-btn", false);
        $response->assertSeeText(__("index.searchbar-replacement.have_key"));
        $response->assertSeeText(__("index.searchbar-replacement.first_time"));

        $response->assertSee('id="searchbar-replacement"', false);
        $response->assertSee("data-hook-line", false);
        $response->assertSee("data-helper-line", false);
        $response->assertSee("data-login-button", false);
        $response->assertSee("data-welcome-back-button", false);

        // No account: no pill, and no search box either — the search bar is only
        // included once a key is present.
        $response->assertDontSee('id="account-pill"', false);
        $response->assertDontSee('id="eingabe"', false);
    }

    /**
     * The retired furniture. #story-privacy said in four vague lines what three
     * benefit cards now say concretely, and #scroll-links was a row of four
     * icons jumping between sections that no longer exist — a link to a missing
     * anchor scrolls nowhere and reports nothing.
     */
    public function testTheStorySectionsTheBenefitCardsReplacedAreGone(): void
    {
        $response = $this->get("/")->assertOk();

        $response->assertDontSee('id="story-privacy"', false);
        $response->assertDontSee('id="scroll-links"', false);
        $response->assertDontSee('id="story-container"', false);
        $response->assertDontSeeText(__("mg-story.privacy.title"));

        // What did survive, in the compact band: the association, the source
        // code, the green power — same copy, same buttons.
        $response->assertSeeText(__("mg-story.ngo.title"));
        $response->assertSeeText(__("mg-story.diversity.title"));
        $response->assertSeeText(__("mg-story.eco.title"));

        // The two membership links in the association card survived too, but
        // only on the German interface — the form they lead to exists in no
        // other language ({@see MembershipAdvertisingTest}).
        $this->get("/de-DE/")->assertOk()->assertSeeText(__("mg-story.btn-member-advantage", locale: "de"));
    }

    // ── Signed in: still a search engine ─────────────────────────────────────

    /**
     * The one that matters. A visitor with a key has already bought the thing;
     * putting the pitch back above their search bar is how this change gets
     * undone by accident.
     */
    public function testTheSignedInStartpageIsStillASearchEngine(): void
    {
        $this->signIn();

        $response = $this->get("/")->assertOk();

        $response->assertSee("startpage-searchbar", false);
        $response->assertSee('id="foki-switcher"', false);
        $response->assertSee('id="tiles"', false);
        $response->assertSee('id="account-pill"', false);

        $response->assertDontSee('id="landing-hero"', false);
        $response->assertDontSee('id="how-it-works"', false);
        $response->assertDontSee('id="landing-benefits"', false);
        $response->assertDontSeeText(__("index.landing.title"));
    }

    /**
     * What a signed-in visitor does still get below the search: the association
     * that funds MetaGer, and the extension that keeps them signed in. Those two
     * are for them more than for anyone.
     */
    public function testTheSignedInStartpageKeepsTheOrganisationAndInstallBands(): void
    {
        $this->signIn();

        $response = $this->get("/")->assertOk();

        $response->assertSee('id="landing-org"', false);
        $response->assertSee('id="landing-install"', false);
        $response->assertSeeText(__("mg-story.ngo.title"));
        $response->assertSeeText(__("mg-story.plugin.title"));
    }

    // ── The links back into the keymanager ───────────────────────────────────

    /**
     * Neither of the two account links goes to the keymanager any more.
     *
     * Both pages have moved here — `/anmelden` first, `/schluessel-erstellen`
     * with the second step — and both old paths still answer only to redirect.
     * A link for a signed-out visitor pointing at either would be one hop that
     * exists for no reason, and `/keys` itself would be worse than that: it is
     * the landing page that redirects back to *this* one, so the visitor would
     * come back where they clicked from.
     */
    public function testTheAccountLinksNoLongerLeaveMetaGer(): void
    {
        $response = $this->get("/")->assertOk();

        $response->assertSee("/schluessel-erstellen", false);
        $response->assertSee("/anmelden", false);
        $response->assertDontSee("/keys/key/create", false);
        $response->assertDontSee('href="/keys"', false);
    }

    /**
     * The site menu is on this page, and the keymanager's own page did not have
     * one. A visitor arriving from the app's Custom Tab who opens the menu and
     * signs in there instead of using the button in the hero must not lose the
     * handback — so the menu's two account links go through the same builder.
     *
     * "Create a key" in the menu used to point at `/keys`, which was the landing
     * page and is now this page: the old target sent people back where they
     * came from.
     */
    public function testTheMenuAccountLinksKeepTheCallback(): void
    {
        $response = $this->get("/?keystore=fdroid")->assertOk();

        $response->assertSee("/schluessel-erstellen?keystore=fdroid", false);
        $response->assertSee("/anmelden?keystore=fdroid", false);

        // Every link into the key flow on the page, not just the hero's two.
        preg_match_all(
            '~href="[^"]*?(/keys/|/anmelden|/schluessel-erstellen)[^"]*"~',
            $response->getContent(),
            $matches
        );
        $this->assertNotEmpty($matches[0]);
        foreach ($matches[0] as $href) {
            if (str_contains($href, "/keys/c")) {
                continue; // Die Gutscheinseite ist nicht Teil der Rückgabe.
            }
            $this->assertStringContainsString("keystore=fdroid", $href, $href);
        }
    }

    public function testTheKeysLinksCarryTheLocalePrefix(): void
    {
        $response = $this->get("/de-DE/")->assertOk();

        $response->assertSee("/de-DE/schluessel-erstellen", false);
        $response->assertSee("/de-DE/anmelden", false);
        // Preise und die Token-Erklärung sind seit dem zweiten Umzugsschritt
        // MetaGer-Routen; das Präfix kommt jetzt von URL::formatPathUsing statt
        // von LaravelLocalization::getLocalizedURL, aber es muss da sein.
        $response->assertSee("/de-DE/preise", false);
        $response->assertSee("/de-DE/hilfe/anonyme-token", false);

        // German copy, to prove the landing block is translated rather than
        // falling back — pt was missing from the keymanager entirely and eight
        // locales were still on the pre-2026-07 wording.
        $response->assertSeeText(trans("index.landing.title", [], "de"));
    }

    /**
     * The MetaGer app opens this page in a Custom Tab and appends `keystore`
     * (and optionally `variant`). Both links out of the page have to re-emit
     * them or the key the visitor is about to make never reaches the app, and
     * nothing anywhere says so.
     */
    public function testTheAppCallbackMarkersSurviveOntoBothActions(): void
    {
        $response = $this->get("/?keystore=playstore&variant=release")->assertOk();

        $response->assertSee("/schluessel-erstellen?keystore=playstore&amp;variant=release", false);
        $response->assertSee("keystore=playstore&amp;variant=release", false);
    }

    /**
     * Asserted against the builder rather than the rendered page, because the
     * page echoes the current URL in places that have nothing to do with this —
     * the settings tile carries `url=` with the whole request on it — so
     * "the string does not appear anywhere" is not the question being asked.
     *
     * @param array<string, string> $query
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("appCallbackCases")]
    public function testOnlyAUsableAppCallbackIsReEmitted(array $query, array $expected): void
    {
        $request = \Illuminate\Http\Request::create("/", "GET", $query);

        $this->assertSame($expected, KeymanagerLinks::appCallback($request));
    }

    /** @return array<string, array{0: array<string, string>, 1: array<string, string>}> */
    public static function appCallbackCases(): array
    {
        return [
            "both markers" => [
                ["keystore" => "playstore", "variant" => "release"],
                ["keystore" => "playstore", "variant" => "release"],
            ],
            // An app build from before `variant` existed sends only the keystore.
            "keystore alone" => [["keystore" => "fdroid"], ["keystore" => "fdroid"]],
            // `variant` only names which build to hand back to; on its own it
            // addresses nothing, and routes/key.js in the keymanager would
            // ignore it. Emitting it alone is a parameter that looks meaningful.
            "variant alone" => [["variant" => "release"], []],
            "blank keystore" => [["keystore" => ""], []],
            "whitespace keystore" => [["keystore" => "   "], []],
            "blank variant is dropped, keystore kept" => [
                ["keystore" => "playstore", "variant" => "  "],
                ["keystore" => "playstore"],
            ],
            "nothing at all" => [[], []],
        ];
    }

    /**
     * The link in mg-story.plugin.p used to be built with
     * `url('/help/anonymous-token')`, which was not a MetaGer route at all — it
     * had been a 404 on the startpage for as long as the string has existed.
     * The fix pointed it at `/keys/help/anonymous-token`; since the second
     * migration step that page is a MetaGer route, and the link names it.
     */
    public function testTheAnonymousTokenLinkInTheInstallBandIsNotA404(): void
    {
        $response = $this->get("/")->assertOk();

        $response->assertSee(url("/hilfe/anonyme-token"), false);
        $response->assertDontSee('href="/help/anonymous-token"', false);
        $response->assertDontSee("/keys/help/", false);
    }
}
