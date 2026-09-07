<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /app/metager is the "manual installation" download link on /app.
 *
 * It used to stream the APK through this app from a frozen GitLab raw path
 * (open-source/app-en@latest). That path is pinned at 5.1.12 forever by the
 * direct-APK updater contract (app-en docs/12), so new installs were getting a
 * years-old build; and streaming a ~100 MB React Native APK through a worker the
 * production FPM pool kills at 30s truncates slow downloads. It now redirects to
 * GitLab's stable-channel release permalink, which is the same pointer the
 * in-app updater and F-Droid follow.
 */
class AppDownloadTest extends TestCase
{
    private const STABLE_APK =
        "https://gitlab.metager.de/metager/metager-app/-/releases/manual-stable/downloads/app-release_manual.apk";

    public function testItRedirectsToTheStableChannelReleasePermalink(): void
    {
        $response = $this->get("/app/metager");

        $response->assertRedirect(self::STABLE_APK);
    }

    /** No session is on the web group, so the redirect must not depend on one. */
    public function testTheRedirectIsAPlainExternalRedirect(): void
    {
        $response = $this->get("/app/metager");

        $response->assertStatus(302);
        $response->assertHeader("Location", self::STABLE_APK);
    }

    /** The link on /app points at this route. */
    public function testTheAppPageLinksToIt(): void
    {
        $response = $this->get("/app");

        $response->assertOk();
        $response->assertSee(url("app/metager"), false);
    }
}
