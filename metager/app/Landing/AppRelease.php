<?php

namespace App\Landing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Which version the direct-APK ("manual") channels of the MetaGer app carry, and
 * the URLs that install them.
 *
 * The manual flavour is distributed as a signed APK from GitLab on two
 * continuously-moved release pointers, `manual-stable` and `manual-beta`
 * (app-en docs/12). Each release's description is the versionName of the APK it
 * currently points at — set together by the same publish step — so it is the one
 * field that says "what is on this channel" without downloading ~100 MB of APK.
 *
 * This backs /app/obtainium, whose whole job is to be a well-formed update
 * source for Obtainium. A page that 500s because GitLab is briefly unreachable
 * would be worse than one showing last hour's version, so — like {@see KeyPrice}
 * — there are two cache entries and no `Cache::remember`:
 *
 *   fresh (1 hour)   served without asking again
 *   stale (30 days)  the last answer we got, used when the request fails
 *
 * and below both, `null`: the page then omits the version line and Obtainium
 * falls back to its own APK hashing until the next successful check.
 */
final class AppRelease
{
    /**
     * The regex Obtainium is told to read the version out of /app/obtainium
     * with, and — asserted by AppObtainiumPageTest — the exact shape the page
     * prints it in. If the two drift, every Obtainium user's update check breaks
     * silently, so they are pinned to one another here.
     */
    public const VERSION_REGEX = 'MetaGer(?: \\(Beta\\))? ([0-9]+\\.[0-9]+\\.[0-9]+)';

    /**
     * GitLab project id of metager/metager-app. Frozen by the direct-APK updater
     * contract (app-en docs/12 §2, "Project ID 68 must not change").
     */
    private const PROJECT = 68;

    /** @var list<string> */
    private const CHANNELS = ["stable", "beta"];

    private const FRESH_MINUTES = 60;
    private const STALE_DAYS = 30;

    /** e.g. "6.19.0", or null when the channel's version can't be determined. */
    public static function version(string $channel = "stable"): ?string
    {
        $channel = self::channel($channel);

        $fresh = Cache::get("app:release:{$channel}");
        if (self::isVersion($fresh)) {
            return $fresh;
        }

        $fetched = self::fetch($channel);
        if ($fetched !== null) {
            Cache::put("app:release:{$channel}", $fetched, now()->addMinutes(self::FRESH_MINUTES));
            Cache::put("app:release:{$channel}:stale", $fetched, now()->addDays(self::STALE_DAYS));
            return $fetched;
        }

        $stale = Cache::get("app:release:{$channel}:stale");
        return self::isVersion($stale) ? $stale : null;
    }

    /** The APK the given channel installs. */
    public static function apkUrl(string $channel = "stable"): string
    {
        $channel = self::channel($channel);

        return "https://gitlab.metager.de/metager/metager-app"
            . "/-/releases/manual-{$channel}/downloads/app-release_manual.apk";
    }

    /** The canonical, locale-free /app/obtainium URL for a channel. */
    public static function pageUrl(string $channel = "stable"): string
    {
        $channel = self::channel($channel);
        $base = rtrim(config("app.url"), "/") . "/app/obtainium";

        return $channel === "beta" ? "{$base}?channel=beta" : $base;
    }

    /**
     * An `obtainium://` deep link that adds the channel as a fully configured
     * app in one tap — the source URL plus the three settings that make Obtainium
     * read a clean version off /app/obtainium rather than hashing the APK.
     *
     * `additionalSettings` is a JSON *string* nested inside the config object,
     * which is how Obtainium's own import format carries it
     * (ImranR98/Obtainium lib/providers/app_json_migration.dart).
     */
    public static function obtainiumDeepLink(string $channel = "stable"): string
    {
        $channel = self::channel($channel);

        $config = [
            "id" => "de.metager.metagerapp.manual",
            "url" => self::pageUrl($channel),
            "author" => "SUMA-EV",
            "name" => $channel === "beta" ? "MetaGer (Beta)" : "MetaGer",
            "additionalSettings" => json_encode([
                "versionExtractionRegEx" => self::VERSION_REGEX,
                "matchGroupToUse" => "1",
                "versionExtractWholePage" => true,
            ]),
        ];

        return "obtainium://app/" . rawurlencode(json_encode($config));
    }

    private static function fetch(string $channel): ?string
    {
        try {
            $response = Http::timeout(3)->get(
                "https://gitlab.metager.de/api/v4/projects/" . self::PROJECT . "/releases/manual-{$channel}"
            );
        } catch (\Throwable $e) {
            // A DNS failure, a timeout, a refused connection. Never fatal — the
            // caller is a page render and the fallbacks above are for this.
            Log::warning("app release [{$channel}] unreachable: " . $e->getMessage());
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $description = $response->json("description");

        return self::isVersion($description) ? $description : null;
    }

    private static function isVersion(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $value) === 1;
    }

    private static function channel(string $channel): string
    {
        return in_array($channel, self::CHANNELS, true) ? $channel : "stable";
    }
}
