<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Vite;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The asset pipeline is only correct if three lists agree:
 *
 *   1. the Vite::asset('resources/…') calls scattered across controllers,
 *      routes and blades;
 *   2. the `input` array in vite.config.js;
 *   3. the manifest Vite writes to public/build.
 *
 * Nothing in PHP or JavaScript enforces that. A page that asks for an entry the
 * build does not produce throws ViteException at render time — a 500 on exactly
 * one page, discovered by a user rather than by the build. That was the failure
 * mode under laravel-mix too: config/asso/*.css was requested by a controller
 * webpack.mix.js had never built.
 *
 * These tests close the loop. The vite.config.js checks need no build and so run
 * everywhere; the manifest check needs `npm run build` to have happened and is
 * skipped when it has not, which is why the CI test job now pulls the npm
 * artifact.
 */
class AssetPipelineTest extends TestCase
{
    /**
     * Directories whose PHP and Blade files may reference build entries.
     */
    private const SOURCE_ROOTS = ["app", "routes", "resources/views"];

    /**
     * PHPUnit builds data providers before the application boots, so base_path()
     * is not available to them. tests/Feature/ is two levels below the project.
     */
    private static function projectPath(string $path = ""): string
    {
        return dirname(__DIR__, 2) . ($path === "" ? "" : "/" . $path);
    }

    /**
     * Every asset requested from PHP, as source paths.
     *
     * @return array<string, array{0: string}> path => [path]
     */
    public static function referencedAssets(): array
    {
        $referenced = [];

        foreach (self::phpFiles() as $file) {
            preg_match_all(
                '/Vite::asset\(\s*[\'"]([^\'"]+)[\'"]/',
                file_get_contents($file),
                $matches
            );

            foreach ($matches[1] as $asset) {
                $referenced[$asset] = [$asset];
            }
        }

        ksort($referenced);

        return $referenced;
    }

    /**
     * Every entry point declared in vite.config.js.
     *
     * @return array<string, array{0: string}> path => [path]
     */
    public static function configuredEntries(): array
    {
        $config = file_get_contents(self::projectPath("vite.config.js"));

        // The input array is the only place resources/ paths appear in quotes;
        // everything else in that file is prose in comments.
        preg_match("/input:\s*\[(.*?)\]/s", $config, $block);

        self::assertNotEmpty($block, "vite.config.js has no input array.");

        preg_match_all('/[\'"](resources\/[^\'"]+)[\'"]/', $block[1], $matches);

        $entries = [];

        foreach ($matches[1] as $entry) {
            $entries[$entry] = [$entry];
        }

        ksort($entries);

        return $entries;
    }

    #[DataProvider("configuredEntries")]
    public function testEveryConfiguredEntryPointExistsOnDisk(string $entry): void
    {
        $this->assertFileExists(
            self::projectPath($entry),
            "vite.config.js builds [$entry], which is not in the repository."
        );
    }

    #[DataProvider("referencedAssets")]
    public function testEveryReferencedAssetIsBuilt(string $asset): void
    {
        $this->assertArrayHasKey(
            $asset,
            self::configuredEntries(),
            "[$asset] is requested through Vite::asset() but vite.config.js does not build it, "
                . "so every page using it would throw at render time."
        );
    }

    /**
     * The reverse direction: an entry nothing asks for is dead weight in the
     * build. Not fatal, so this reports rather than fails per-entry — but it is
     * how the six laravel-mix entries with no consumer (a verify page and a bot
     * admin page that no route reaches, plus aaresultpage/editLanguage/
     * scriptJoinPage) were found.
     */
    public function testNoEntryPointIsBuiltWithoutAConsumer(): void
    {
        $orphans = array_diff(
            array_keys(self::configuredEntries()),
            array_keys(self::referencedAssets())
        );

        $this->assertSame(
            [],
            array_values($orphans),
            "vite.config.js builds entries that nothing references."
        );
    }

    public function testManifestResolvesEveryReferencedAsset(): void
    {
        $manifest = public_path("build/manifest.json");

        if (!is_file($manifest)) {
            $this->markTestSkipped("No Vite manifest — run `npm run build` first.");
        }

        $entries = json_decode(file_get_contents($manifest), true);

        foreach (array_keys(self::referencedAssets()) as $asset) {
            $this->assertArrayHasKey(
                $asset,
                $entries,
                "[$asset] is missing from the built manifest."
            );

            $file = public_path("build/" . $entries[$asset]["file"]);

            $this->assertFileExists($file, "[$asset] maps to a file that was not emitted.");
        }
    }

    /**
     * The @vite directive cannot express this, which is why the pages pass URLs
     * around by hand instead: without a stored theme the dark stylesheet is
     * attached with a media query, so the browser picks a theme with no
     * JavaScript involved. Losing the media attribute would make every visitor
     * dark, and losing the link would make every visitor light — both silently.
     */
    public function testDarkThemeIsAppliedByMediaQueryRatherThanScript(): void
    {
        $response = $this->get("/about");

        $response->assertOk();

        $dark = Vite::asset("resources/less/metager/metager-dark.less");

        $response->assertSee('media="(prefers-color-scheme:dark)"', false);
        $response->assertSee($dark, false);
    }

    /**
     * The same application answers on metager.de, metager3.de and a .onion
     * address. mix() emitted root-relative URLs, so which host served the page
     * never mattered; Vite::asset() defaults to asset(), which pins the host
     * *and* the scheme of the request that rendered the page. AppServiceProvider
     * puts that back — a regression here would send onion visitors to the clear
     * web for their stylesheets, or emit http:// asset links into an https page.
     */
    public function testAssetUrlsAreRootRelativeRatherThanHostQualified(): void
    {
        $url = Vite::asset("resources/less/metager/metager.less");

        $this->assertStringStartsWith("/build/", $url, "Asset URL is not root-relative: $url");

        $response = $this->get("/about");

        $response->assertOk();
        $response->assertDontSee('href="http://localhost/build/', false);
    }

    /**
     * Guards against a half-finished migration leaving mix() calls behind: the
     * helper still exists in Laravel 13, so such a call fails only when the page
     * is rendered, and only in production where public/mix-manifest.json is gone.
     */
    public function testNothingStillAsksForALaravelMixAsset(): void
    {
        $offenders = [];

        foreach (self::phpFiles() as $file) {
            if (preg_match('/(?<![\w>$])mix\(\s*[\'"]/', file_get_contents($file))) {
                $offenders[] = str_replace(self::projectPath() . "/", "", $file);
            }
        }

        $this->assertSame([], $offenders, "mix() survived the Vite migration in these files.");
    }

    /**
     * @return array<int, string>
     */
    private static function phpFiles(): array
    {
        $files = [];

        foreach (self::SOURCE_ROOTS as $root) {
            $directory = new \RecursiveDirectoryIterator(self::projectPath($root));

            foreach (new \RecursiveIteratorIterator($directory) as $file) {
                if ($file->isFile() && $file->getExtension() === "php") {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
