<?php

namespace Tests\Feature;

use App\Landing\AppRelease;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * /app/obtainium is a deliberately plain update source for Obtainium: one APK
 * link for the chosen channel and the current version printed as "MetaGer
 * <x.y.z>", so Obtainium reads a real version rather than hashing 100 MB of APK.
 *
 * The shape of that version string and the regex the "Add to Obtainium" deep
 * link tells Obtainium to read it with are the same string
 * ({@see AppRelease::VERSION_REGEX}); if the page and the deep link ever drift
 * apart, every Obtainium user's update check breaks silently. This test is what
 * keeps them together.
 */
class AppObtainiumPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function fakeChannels(string $stable = "6.19.0", string $beta = "6.20.1"): void
    {
        Http::fake([
            "*/releases/manual-stable" => Http::response(["description" => $stable]),
            "*/releases/manual-beta" => Http::response(["description" => $beta]),
        ]);
    }

    public function testItRendersAsAnObtainiumSourceForTheStableChannel(): void
    {
        $this->fakeChannels();

        $response = $this->get("/app/obtainium");

        $response->assertOk();
        $response->assertSee("<title>" . e(trans("titles.app")) . "</title>", false);
        $response->assertSee(AppRelease::apkUrl("stable"), false);
        $response->assertSee("MetaGer 6.19.0", false);
        $response->assertSee("obtainium://app/", false);
        // The beta APK must not also be on the stable page — Obtainium takes the
        // last .apk link it finds.
        $response->assertDontSee(AppRelease::apkUrl("beta"), false);
    }

    public function testTheBetaChannelPageServesTheBetaApk(): void
    {
        $this->fakeChannels();

        $response = $this->get("/app/obtainium?channel=beta");

        $response->assertOk();
        $response->assertSee(AppRelease::apkUrl("beta"), false);
        $response->assertSee("MetaGer (Beta) 6.20.1", false);
        $response->assertDontSee(AppRelease::apkUrl("stable"), false);
    }

    /**
     * The exact property Obtainium depends on: applying the deep link's own
     * versionExtractionRegEx to the rendered page yields the channel's version.
     */
    public function testTheRenderedVersionMatchesTheDeepLinksExtractionRegex(): void
    {
        $this->fakeChannels(stable: "6.19.0");

        $body = $this->get("/app/obtainium")->getContent();

        $deepLink = AppRelease::obtainiumDeepLink("stable");
        $config = json_decode(rawurldecode(substr($deepLink, strlen("obtainium://app/"))), true);
        $regex = json_decode($config["additionalSettings"], true)["versionExtractionRegEx"];

        $this->assertSame(
            1,
            preg_match_all('/' . $regex . '/', $body, $matches),
            "the version regex should match the page exactly once"
        );
        $this->assertSame("6.19.0", end($matches[1]));
    }

    public function testItStillRendersWhenGitlabIsUnreachable(): void
    {
        Http::fake(["*/releases/manual-*" => Http::response("", 503)]);

        $response = $this->get("/app/obtainium");

        $response->assertOk();
        // No version to show, but the source is still usable.
        $response->assertSee(AppRelease::apkUrl("stable"), false);
        $response->assertSee("obtainium://app/", false);
        $response->assertDontSee("MetaGer 6.19.0", false);
    }

    public function testItShowsTheManualSetupSettings(): void
    {
        $this->fakeChannels();

        $response = $this->get("/app/obtainium");

        $response->assertSee(AppRelease::pageUrl("stable"), false);
        $response->assertSee("versionExtractionRegEx", false);
        $response->assertSee(e(AppRelease::VERSION_REGEX), false);
        $response->assertSee("versionExtractWholePage", false);
        $response->assertSee("de.metager.metagerapp.manual", false);
    }

    /**
     * The /app overview links here — and renders without touching GitLab. The
     * version lookup belongs to /app/obtainium alone; StaticPagesTest hits /app
     * with no HTTP fake, so a stray call here would reach gitlab.metager.de on
     * every base-suite run. `preventStrayRequests()` (setUp) is the assertion.
     */
    public function testTheAppOverviewLinksHereWithoutCallingGitlab(): void
    {
        $response = $this->get("/app");

        $response->assertOk();
        $response->assertSee(url("app/obtainium"), false);
    }
}
