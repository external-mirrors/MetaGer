<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Source-level checks on the stylesheets.
 *
 * These exist because the move from laravel-mix to Vite also changed the CSS
 * minifier, and lightningcss does not treat malformed declarations the way
 * postcss did. postcss passed them through, browsers rejected them, and the
 * page rendered as though they had never been written. lightningcss parses more
 * leniently and then emits something valid — so a declaration that was
 * *silently inert* for years can start taking effect during a build-tool swap,
 * with nothing in the diff to show for it.
 *
 * That is not hypothetical. `.result-description` carried `font-size: 1`, a
 * length with no unit. Every browser dropped it; lightningcss read it as 1px
 * and emitted `font-size:1px`, and every search result description on the site
 * became invisible.
 *
 * A single unit is all it takes to be valid, so these run against the sources
 * rather than the build output: the point is to keep the mistake out of the
 * LESS, not to describe what one particular minifier does with it.
 */
class StylesheetSourceTest extends TestCase
{
    /**
     * Properties that take a <length> and nothing that could be a bare number.
     *
     * line-height, z-index, opacity, flex, order and font-weight are all
     * legitimately unitless and are deliberately absent.
     */
    private const LENGTH_PROPERTIES = [
        "font-size",
        "width",
        "height",
        "min-width",
        "min-height",
        "max-width",
        "max-height",
        "margin",
        "margin-top",
        "margin-right",
        "margin-bottom",
        "margin-left",
        "padding",
        "padding-top",
        "padding-right",
        "padding-bottom",
        "padding-left",
        "top",
        "right",
        "bottom",
        "left",
        "border-radius",
        "border-width",
        "gap",
        "row-gap",
        "column-gap",
        "text-indent",
        "letter-spacing",
        "word-spacing",
        "outline-offset",
    ];

    /**
     * PHPUnit builds data providers before the application boots, so base_path()
     * is not available to them. tests/Feature/ is two levels below the project.
     */
    private static function projectPath(string $path = ""): string
    {
        return dirname(__DIR__, 2) . ($path === "" ? "" : "/" . $path);
    }

    /**
     * Every stylesheet source in the project.
     *
     * @return array<string, array{0: string}> relative path => [relative path]
     */
    public static function stylesheets(): array
    {
        $files = [];

        foreach (["resources/less", "resources/css"] as $root) {
            $directory = self::projectPath($root);

            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));

            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ["less", "css"], true)) {
                    continue;
                }

                $relative = substr($file->getPathname(), strlen(self::projectPath()) + 1);
                $files[$relative] = [$relative];
            }
        }

        ksort($files);

        return $files;
    }

    /**
     * A length needs a unit unless it is zero.
     *
     * Zero is exempt because `margin: 0` is valid CSS and universal. LESS
     * variables, mixin calls, `calc()` and anything else that is not a bare
     * number are left alone — this looks for literal numbers only.
     */
    #[DataProvider("stylesheets")]
    public function testLengthValuesCarryAUnit(string $stylesheet): void
    {
        $properties = implode("|", array_map("preg_quote", self::LENGTH_PROPERTIES));

        $lines = file(self::projectPath($stylesheet), FILE_IGNORE_NEW_LINES);
        $offenders = [];

        foreach ($lines as $number => $line) {
            if (!preg_match("/^\s*($properties)\s*:\s*(-?\d+(?:\.\d+)?)\s*(!important)?\s*;/i", $line, $match)) {
                continue;
            }

            // `0` is a valid length on its own; every other bare number is not.
            if ((float) $match[2] === 0.0) {
                continue;
            }

            $offenders[] = sprintf("  line %d: %s", $number + 1, trim($line));
        }

        $this->assertSame(
            [],
            $offenders,
            "$stylesheet declares a length without a unit:\n" .
                implode("\n", $offenders) .
                "\n\nBrowsers drop such a declaration, so it does nothing — but lightningcss " .
                "reads it as pixels and emits it, which changes the page. Add the unit that was " .
                "meant, or delete the declaration."
        );
    }
}
