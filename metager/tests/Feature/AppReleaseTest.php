<?php

namespace Tests\Feature;

use App\Landing\AppRelease;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * What /app/obtainium reads: the version each direct-APK channel currently
 * carries, and the URLs that install it.
 *
 * The version is GitLab's — each `manual-stable` / `manual-beta` release
 * describes the APK it points at (app-en docs/12) — and the page has to render
 * even when GitLab is briefly unreachable, so this pins the same two-entry
 * cache {@see KeyPriceTest} pins for the keymanager price: fresh for an hour,
 * stale for a month, then nothing (the page drops the version line and lets
 * Obtainium hash the APK instead).
 */
class AppReleaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function fakeRelease(string $channel, string $description): void
    {
        Http::fake([
            "*/projects/68/releases/manual-{$channel}" => Http::response(["description" => $description]),
        ]);
    }

    public function testItReadsTheVersionFromTheChannelRelease(): void
    {
        $this->fakeRelease("stable", "6.19.0");

        $this->assertSame("6.19.0", AppRelease::version("stable"));
    }

    public function testItAsksGitlabOnlyOncePerHour(): void
    {
        $this->fakeRelease("stable", "6.19.0");

        AppRelease::version("stable");
        AppRelease::version("stable");
        AppRelease::version("stable");

        Http::assertSentCount(1);
    }

    public function testStableAndBetaAreSeparateChannels(): void
    {
        Http::fake([
            "*/releases/manual-stable" => Http::response(["description" => "6.19.0"]),
            "*/releases/manual-beta" => Http::response(["description" => "6.20.1"]),
        ]);

        $this->assertSame("6.19.0", AppRelease::version("stable"));
        $this->assertSame("6.20.1", AppRelease::version("beta"));
    }

    public function testAnUnknownChannelIsTreatedAsStable(): void
    {
        $this->fakeRelease("stable", "6.19.0");

        $this->assertSame("6.19.0", AppRelease::version("nonsense"));
        $this->assertStringContainsString("manual-stable", AppRelease::apkUrl("nonsense"));
    }

    /** The fresh copy expires, GitLab is down, the page still shows a version. */
    public function testAnOutageFallsBackToTheLastKnownVersion(): void
    {
        $this->fakeRelease("stable", "6.19.0");
        AppRelease::version("stable");

        Cache::forget("app:release:stable");
        Http::fake(["*/releases/manual-stable" => Http::response("", 503)]);

        $this->assertSame("6.19.0", AppRelease::version("stable"));
    }

    /** A connection that never opens is an outage, not an exception. */
    public function testAConnectionFailureIsAnOutageAndNotAnError(): void
    {
        $this->fakeRelease("stable", "6.19.0");
        AppRelease::version("stable");
        Cache::forget("app:release:stable");

        Http::fake(fn() => throw new \Illuminate\Http\Client\ConnectionException("no route to host"));

        $this->assertSame("6.19.0", AppRelease::version("stable"));
    }

    /** Cold cache and an outage at once: no version at all, and the page copes. */
    public function testWithNothingCachedAndNothingReachableTheVersionIsNull(): void
    {
        Http::fake(["*/releases/manual-stable" => Http::response("", 500)]);

        $this->assertNull(AppRelease::version("stable"));
    }

    /**
     * A release description that is not a version is refused rather than
     * printed — the channel pointers have carried their own tag name there
     * before now (app-en docs/12).
     *
     * @param mixed $description
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("notVersions")]
    public function testADescriptionThatIsNotAVersionIsIgnored($description): void
    {
        Http::fake(["*/releases/manual-stable" => Http::response(["description" => $description])]);

        $this->assertNull(AppRelease::version("stable"));
    }

    /** @return array<string, array{0: mixed}> */
    public static function notVersions(): array
    {
        return [
            "the channel name" => ["manual-stable"],
            "a tag" => ["beta-6.19.1"],
            "empty" => [""],
            "null" => [null],
            "not a triple" => ["6.19"],
            "prefixed" => ["v6.19.0"],
        ];
    }

    public function testTheApkUrlsAreTheChannelPermalinks(): void
    {
        $this->assertSame(
            "https://gitlab.metager.de/metager/metager-app/-/releases/manual-stable/downloads/app-release_manual.apk",
            AppRelease::apkUrl("stable")
        );
        $this->assertSame(
            "https://gitlab.metager.de/metager/metager-app/-/releases/manual-beta/downloads/app-release_manual.apk",
            AppRelease::apkUrl("beta")
        );
    }

    public function testThePageUrlIsCanonicalAndLocaleFree(): void
    {
        $base = rtrim(config("app.url"), "/");

        $this->assertSame("{$base}/app/obtainium", AppRelease::pageUrl("stable"));
        $this->assertSame("{$base}/app/obtainium?channel=beta", AppRelease::pageUrl("beta"));
    }

    /**
     * The deep link is an Obtainium app-config import: a single JSON object,
     * url-encoded after `obtainium://app/`, with `additionalSettings` carried as
     * a nested JSON *string* (ImranR98/Obtainium app_json_migration.dart).
     */
    public function testTheDeepLinkIsAValidObtainiumAppConfig(): void
    {
        $link = AppRelease::obtainiumDeepLink("stable");

        $this->assertStringStartsWith("obtainium://app/", $link);

        $config = json_decode(rawurldecode(substr($link, strlen("obtainium://app/"))), true);

        $this->assertIsArray($config);
        foreach (["id", "url", "author", "name"] as $key) {
            $this->assertArrayHasKey($key, $config, "config is missing required key [{$key}]");
            $this->assertIsString($config[$key]);
        }
        $this->assertSame("de.metager.metagerapp.manual", $config["id"]);
        $this->assertStringContainsString("/app/obtainium", $config["url"]);

        $this->assertIsString($config["additionalSettings"]);
        $settings = json_decode($config["additionalSettings"], true);
        $this->assertIsArray($settings);
        $this->assertSame(AppRelease::VERSION_REGEX, $settings["versionExtractionRegEx"]);
        $this->assertSame("1", $settings["matchGroupToUse"]);
        $this->assertTrue($settings["versionExtractWholePage"]);
    }

    public function testTheBetaDeepLinkPointsAtTheBetaPageAndIsNamedForIt(): void
    {
        $link = AppRelease::obtainiumDeepLink("beta");
        $config = json_decode(rawurldecode(substr($link, strlen("obtainium://app/"))), true);

        $this->assertStringContainsString("channel=beta", $config["url"]);
        $this->assertStringContainsString("Beta", $config["name"]);
    }

    /** The regex Obtainium is handed actually captures a "MetaGer x.y.z" version. */
    public function testTheVersionRegexCapturesTheVersion(): void
    {
        $this->assertSame(
            1,
            preg_match('/' . AppRelease::VERSION_REGEX . '/', "MetaGer 6.19.0 — app-release_manual.apk", $m)
        );
        $this->assertSame("6.19.0", $m[1]);

        preg_match('/' . AppRelease::VERSION_REGEX . '/', "MetaGer (Beta) 6.20.1 — app-release_manual.apk", $m);
        $this->assertSame("6.20.1", $m[1]);
    }
}
