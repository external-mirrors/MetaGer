<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Facades\Route;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * MetaGer no longer serves advertising, and no longer rewrites results into
 * affiliate links. This keeps both gone.
 *
 * Three systems were involved and all three had stopped earning anything long
 * before they were removed:
 *
 *   - *Ad results.* Every engine still in config/foki.json declared
 *     `ads => false`, so the one branch that read the flag
 *     (DisabledReason::SERVES_ADVERTISEMENTS) could not fire, and no parser
 *     ever wrote to Searchengine::$ads. The ad list was built, blacklist-
 *     filtered against two config files and merged on every single search to
 *     arrive empty.
 *   - *The donation ad.* addDonationAdvertisement() began with a bare
 *     `return;`, and nothing called it either way.
 *   - *Affiliate links.* Admitad rewrote a matching result's link to an adgoal
 *     deeplink through /partner/r, which counted the click and redirected on.
 *     Nothing constructed the Admitad class, and its two config keys
 *     (metager.metager.adgoal.private_key, metager.metager.admitad.*) had
 *     already been deleted — so generatePassword() was signing with null.
 *
 * A test that asserts absence earns its place here for the same reason it does
 * in StresstestRemovedTest: these were routes and one-line schedule entries,
 * and the ad pipeline in particular ran on the hot path of every search. The
 * value of removing it is that it stays removed.
 *
 * The `ads` field of the JSON envelope is the deliberate exception below — it
 * stays, empty, until the schema version goes up.
 */
class AdvertisingRemovedTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    public function testTheAffiliateAndAdvertisingPagesAreGone(): void
    {
        // /partner/r counted a click and redirected to the affiliate shop; the
        // other two explained to users why MetaGer showed advertising.
        $this->get("/partner/r?affillink=https://example.org&link=https://example.org&password=x")->assertNotFound();
        $this->get("/partnershops")->assertNotFound();
        $this->get("/ad-info")->assertNotFound();
    }

    /**
     * Named routes are the other half of that: a blade calling route() on a
     * name nothing registers is a 500 at render time, not a 404.
     */
    public function testNothingCanStillLinkToTheAffiliateRedirect(): void
    {
        foreach (["adgoal-redirect", "partnershops"] as $name) {
            $this->assertNull(
                Route::getRoutes()->getByName($name),
                "The route [$name] is back."
            );
        }
    }

    /**
     * On the files rather than through class_exists(): the classmap in
     * vendor/composer is a build artifact that can still name a deleted class,
     * and asking for it there raises "failed to open stream" instead of
     * answering false. The file is the thing that has to be gone.
     */
    public function testTheAdvertisingAndAffiliateClassesAreGone(): void
    {
        foreach ([
            "app/Models/Admitad.php",
            "app/Http/Controllers/AdgoalController.php",
            "app/Console/Commands/LoadAffiliateBlacklist.php",
            "app/Console/Commands/StorePartnerCalls.php",
        ] as $file) {
            $this->assertFileDoesNotExist(base_path($file), "$file is back.");
        }
    }

    /**
     * Both commands ran every minute on every scheduler tick, one of them to
     * drain a Redis list nothing pushed to.
     */
    public function testTheAffiliateCommandsAreNoLongerScheduled(): void
    {
        $scheduled = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->map(fn($event) => $event->command ?? "")
            ->implode("\n");

        $this->assertStringNotContainsString("load:affiliate-blacklist", $scheduled);
        $this->assertStringNotContainsString("affilliates:store", $scheduled);
    }

    /**
     * Read and exploded in MetaGer::__construct on every request, to filter a
     * list that was always empty.
     */
    public function testTheAdvertisementBlacklistsAreGone(): void
    {
        $this->assertFileDoesNotExist(config_path("adBlacklistDomains.txt"));
        $this->assertFileDoesNotExist(config_path("adBlacklistUrl.txt"));
    }

    /**
     * The one piece kept on purpose. Removing a field from the envelope is the
     * breaking change API_SCHEMA_VERSION exists to track, so `ads` stays until
     * that version goes up — it is simply always empty now.
     *
     * One search per test method: a second one in the same PHP process returns
     * 500, because QueryTimer throws on a repeated event name (pinned in
     * OutputFormatsTest).
     */
    public function testTheJsonEnvelopeStillCarriesAnAlwaysEmptyAdsField(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $payload = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk()->json();

        $this->assertArrayHasKey("ads", $payload, "Dropping `ads` raises MetaGer::API_SCHEMA_VERSION; do both or neither.");
        $this->assertSame([], $payload["ads"]);
        $this->assertSame(1, $payload["version"]);
        $this->assertNotEmpty($payload["results"], "No result reached the output, so the empty ad list proves nothing.");
    }

    /**
     * The Atom feed carried advertising in a namespace of its own, injected
     * before the first entry and after every fifth one. Both the elements and
     * the namespace declaration go.
     */
    public function testTheAtomFeedNoLongerDeclaresAnAdvertisementNamespace(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $feed = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=atom10")->assertOk()->getContent();

        $this->assertStringNotContainsString("ad:advertisement", $feed);
        $this->assertStringNotContainsString("extensions/advertisement", $feed);
        $this->assertStringContainsString("<entry>", $feed, "The feed rendered no entries, so this proves nothing.");
    }

    /**
     * A partnershop result was marked with a badge linking to /partnershops and
     * an "ad free" call to action in the footer. Neither has a source of truth
     * any more — nothing sets Result::$partnershop to true.
     */
    public function testTheResultPageMarksNoResultAsAPartnershop(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $page = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web")->assertOk();

        $page->assertDontSee("/partnershops", false);
        $page->assertDontSee("result-open-key", false);
        $page->assertSee("result-open", false);

        // This is the one place a result page is rendered for a signed-in key,
        // so the account cues are checked here rather than paying for a second
        // search render. See Tests\Feature\Authentication\AccountVisibilityTest.
        $page->assertSee("resultpage-searchbar", false);
        $page->assertSee('id="account-pill"', false);
        $page->assertSee("account-pill--compact", false);
        $page->assertSee('class="sidebar-account"', false);

        // The key indicator is gone from the search bar. On this page it could
        // only ever have been green: every route here runs
        // AuthenticationValidation, whose unauthorised branches all redirect to
        // the startpage — so it was an indicator with one possible value.
        $page->assertDontSee('id="search-key"', false);
        $page->assertDontSee('id="key-link"', false);
    }

    /**
     * And the ad loader it was guarding is gone with it.
     *
     * scriptResultPage.js wrapped the Yahoo `selectTier` script in "return if
     * #key-link is authorized", which — per the test above — it always was. The
     * loader could not run, and it was the only reader of #key-link, which is
     * why the search bar's key markup counted as untouchable for so long.
     *
     * Asserted on the built bundle rather than the source: the source could stop
     * importing it while the bundle still shipped it.
     */
    public function testTheYahooAdvertisingLoaderIsNotInTheBuiltBundle(): void
    {
        $manifest = json_decode(file_get_contents(public_path("build/manifest.json")), true);
        $entry = $manifest["resources/js/scriptResultPage.js"]["file"] ?? null;
        $this->assertNotNull($entry, "scriptResultPage.js is not in the manifest; the assertion below would prove nothing.");

        $bundle = file_get_contents(public_path("build/" . $entry));

        $this->assertStringNotContainsString("selectTier", $bundle);
        $this->assertStringNotContainsString("s.yimg.com", $bundle);
    }
}
