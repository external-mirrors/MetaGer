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
            // Both accessors matter: asset() yields a URL, content() reads the built file
            // (the widget pages inline their stylesheet into a copy-paste snippet). Either
            // one throws if the entry is missing from the manifest.
            preg_match_all(
                '/Vite::(?:asset|content)\(\s*[\'"]([^\'"]+)[\'"]/',
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
     * The theme costs one stylesheet, not two.
     *
     * It used to be two full compilations of the same 89 KB — the dark one
     * attached with media="(prefers-color-scheme:dark)", which lowers a
     * stylesheet's priority but still downloads it, so every visitor paid for
     * both. metager.less now carries both palettes as custom properties and
     * chooses between them itself.
     */
    public function testThemeCostsASingleStylesheet(): void
    {
        $response = $this->get("/about");

        $response->assertOk();

        $main = Vite::asset("resources/less/metager/metager.less");

        $this->assertSame(
            1,
            substr_count($response->getContent(), $main),
            "The main stylesheet is linked more than once. The light branch used to link it a " .
                "second time on top of the unconditional link at the top of the layout."
        );
    }

    /**
     * With no theme chosen there is no attribute, so the media query in the
     * stylesheet decides and the browser's own setting wins. This is what most
     * visitors get, and it has to keep working without JavaScript — which is
     * why the choice is an attribute the server renders rather than a class
     * some script adds.
     */
    public function testNoThemeIsPinnedWhenTheVisitorHasNotChosenOne(): void
    {
        $response = $this->get("/about");

        $response->assertOk();
        $response->assertDontSee("data-theme", false);
    }

    /**
     * A chosen theme has to beat the browser's, in both directions. Dark is the
     * easy half; light is the one worth pinning, because it only works if the
     * stylesheet's prefers-color-scheme block excludes data-theme="light".
     */
    #[DataProvider("chosenThemes")]
    public function testAChosenThemeIsPinnedOnTheDocument(string $setting, string $expected): void
    {
        $response = $this->get("/about?dark_mode=" . $setting);

        $response->assertOk();
        $response->assertSee('data-theme="' . $expected . '"', false);
    }

    public static function chosenThemes(): array
    {
        return [
            "dark" => ["dark", "dark"],
            "light" => ["light", "light"],
            // SearchSettings still accepts the numeric values older clients send.
            "legacy numeric light" => ["1", "light"],
            "legacy numeric dark" => ["2", "dark"],
        ];
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
     * Build output must be reached through the manifest, never by guessing its path.
     *
     * Vite emits hashed filenames, so a hard-coded public_path("css/…") cannot resolve —
     * and this is not hypothetical: the two widget pages read
     * public_path("css/widget/widget-template.css") directly to inline it, and HttpCache
     * hashed public_path("mix-manifest.json") to version its ETags. Neither went through
     * mix(), so neither showed up in a search for mix() call sites; the feature suite
     * caught them as 500s once the stale laravel-mix output was deleted.
     */
    public function testNothingReachesIntoTheBuildOutputByPath(): void
    {
        $offenders = [];

        foreach (self::phpFiles() as $file) {
            $contents = file_get_contents($file);

            if (preg_match('/public_path\(\s*[\'"][^\'"]*(mix-manifest|\.css|\.js)/', $contents)) {
                $offenders[] = str_replace(self::projectPath() . "/", "", $file);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These reach into the build output by path. Use Vite::asset() for a URL or "
                . "Vite::content() for the file's contents."
        );
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

    /**
     * Every stylesheet source is reachable: it is either a build entry or is
     * imported by one.
     *
     * Fourteen were not. Some were pages that moved out of this application
     * years ago — the key pages now live in the keymanager service — and some
     * were superseded and left behind, like a page-level count-dark.less next to
     * the pages/count/ pair that replaced it. None of them were built, so none
     * could be reached, and nothing said so.
     *
     * A file that is genuinely wanted but not used yet fails this: add it to
     * vite.config.js, or import it from a stylesheet that is already built.
     */
    public function testEveryStylesheetSourceIsReachable(): void
    {
        $sources = [];

        foreach (["resources/less", "resources/css"] as $root) {
            foreach ($this->stylesheetsUnder(self::projectPath($root)) as $file) {
                $sources[] = $file;
            }
        }

        $this->assertNotEmpty($sources, "No stylesheet sources found — the search is looking in the wrong place.");

        $entries = array_map(fn($entry) => self::projectPath($entry), array_keys(self::configuredEntries()));

        $imported = [];
        foreach ($sources as $file) {
            preg_match_all(
                '/@import\s+(?:\(.*?\)\s*)?[\'"]([^\'"]+)[\'"]/',
                file_get_contents($file),
                $matches
            );

            foreach ($matches[1] as $target) {
                $resolved = realpath(dirname($file) . "/" . $target);

                if ($resolved !== false) {
                    $imported[$resolved] = true;
                }
            }
        }

        $orphans = [];
        foreach ($sources as $file) {
            if (in_array($file, $entries, true) || isset($imported[$file])) {
                continue;
            }

            $orphans[] = str_replace(self::projectPath() . "/", "", $file);
        }

        $this->assertSame(
            [],
            $orphans,
            "Stylesheet sources that nothing builds and nothing imports:\n  " . implode("\n  ", $orphans)
        );
    }

    /**
     * @return list<string> absolute paths
     */
    private function stylesheetsUnder(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $found = [];

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ["less", "css"], true)) {
                $found[] = $file->getPathname();
            }
        }

        sort($found);

        return $found;
    }
}
