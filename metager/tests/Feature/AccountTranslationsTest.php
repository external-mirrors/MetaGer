<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * The account is translated everywhere, and stays translated.
 *
 * `lang/account.php` was written in German, English and Catalan and nowhere
 * else, which does not fail — Laravel falls through to `fallback_locale`, so a
 * Swedish visitor's account block quietly renders in German. That is the failure
 * mode of every translation gap and no other test notices it.
 *
 * Scoped to the three files the account touches rather than the whole locale, on
 * purpose. The other files are *not* complete against `en` — see
 * {@see CatalanTranslationsTest}, which explains why widening it means either a
 * red suite or a pile of exemptions — and a test that cannot be green teaches
 * nobody anything.
 *
 * `en` is the base by construction: it is the locale these strings were written
 * against. A key it has and a translation lacks is a string somebody reads in
 * the wrong language; a key only the translation has is dead weight that will
 * drift, so both directions fail.
 *
 * Only real directories are checked. `lang/de-DE` and its twenty siblings are
 * symlinks to their base language, so there is nothing separate to translate.
 */
class AccountTranslationsTest extends TestCase
{
    private const BASE_LOCALE = "en";

    /** The files that render the account: the pill, the menu block, the startpage funnel. */
    private const FILES = ["account.php", "index.php", "sidebar.php"];

    /** Every `:placeholder` Laravel would replace in a translation string. */
    private const PLACEHOLDER_PATTERN = '/(?<![\w:]):[a-zA-Z_][a-zA-Z0-9_]*/';

    public function testEveryLocaleTranslatesEveryAccountString(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            foreach (self::FILES as $file) {
                $path = $this->path($locale, $file);
                if (!is_file($path)) {
                    $problems[] = "$locale/$file is missing entirely";
                    continue;
                }

                $base = $this->keys($this->path(self::BASE_LOCALE, $file));
                $translated = $this->keys($path);

                foreach (array_diff($base, $translated) as $key) {
                    $problems[] = "$locale/$file is missing $key";
                }
                foreach (array_diff($translated, $base) as $key) {
                    $problems[] = "$locale/$file has $key, which " . self::BASE_LOCALE . " does not";
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /**
     * A dropped placeholder is the one translation bug that reaches the user as
     * garbage: the pill renders the literal ":charge" where the balance should
     * be. It is invisible until somebody reads that page in that language.
     */
    public function testEveryLocaleKeepsEveryPlaceholder(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            foreach (self::FILES as $file) {
                $path = $this->path($locale, $file);
                if (!is_file($path)) {
                    continue; // Already reported by the test above.
                }

                $base = $this->strings($this->path(self::BASE_LOCALE, $file));
                $translated = $this->strings($path);

                foreach ($base as $key => $string) {
                    if (!isset($translated[$key])) {
                        continue;
                    }
                    $expected = $this->placeholders($string);
                    $actual = $this->placeholders($translated[$key]);
                    if ($expected !== $actual) {
                        $problems[] = "$locale/$file $key: expected " . json_encode($expected)
                            . ", got " . json_encode($actual);
                    }
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /** The base locales: real directories, not the regional symlinks. */
    private function locales(): array
    {
        $locales = [];
        foreach (scandir($this->langPath()) as $entry) {
            $path = $this->langPath() . "/" . $entry;
            if ($entry[0] === "." || is_link($path) || !is_dir($path) || $entry === self::BASE_LOCALE) {
                continue;
            }
            $locales[] = $entry;
        }

        // A guard against this test passing because it checked nothing: the
        // symlink filter is the sort of thing that silently matches everything.
        $this->assertGreaterThan(5, count($locales), "found almost no locales to check");

        return $locales;
    }

    private function langPath(): string
    {
        return dirname(__DIR__, 2) . "/lang";
    }

    private function path(string $locale, string $file): string
    {
        return $this->langPath() . "/$locale/$file";
    }

    /** Every dotted key in a translation file. */
    private function keys(string $path): array
    {
        return array_keys($this->strings($path));
    }

    /** Every string in a translation file, by dotted key. */
    private function strings(string $path): array
    {
        return $this->flatten(require $path);
    }

    private function flatten(array $translations, string $prefix = ""): array
    {
        $flat = [];
        foreach ($translations as $key => $value) {
            $dotted = $prefix === "" ? (string) $key : "$prefix.$key";
            if (is_array($value)) {
                $flat += $this->flatten($value, $dotted);
            } else {
                $flat[$dotted] = $value;
            }
        }
        return $flat;
    }

    private function placeholders(string $string): array
    {
        preg_match_all(self::PLACEHOLDER_PATTERN, $string, $matches);
        $placeholders = $matches[0];
        sort($placeholders);
        return $placeholders;
    }
}
