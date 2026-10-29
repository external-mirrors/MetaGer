<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * The privacy policy's error-tracking section says the same thing in every
 * language — in particular the sentence about the web extension, which is a
 * disclosure the extension's store listings point to. A locale missing it
 * does not fail: Laravel falls through to the fallback locale, and a Swedish
 * reader is told in German.
 *
 * A plain TestCase for the reason {@see AccountTranslationsTest} gives:
 * comparing arrays of strings needs no application.
 */
class PrivacyMonitoringTranslationsTest extends TestCase
{
    private const BASE_LOCALE = "en";

    public function testEveryLocaleCarriesTheWholeMonitoringSection(): void
    {
        $expected = $this->keys($this->monitoring(self::BASE_LOCALE));
        $problems = [];

        foreach ($this->locales() as $locale) {
            $translated = $this->keys($this->monitoring($locale));
            foreach (array_diff($expected, $translated) as $key) {
                $problems[] = "$locale is missing privacy.monitoring.$key";
            }
        }

        $this->assertSame([], $problems);
    }

    public function testTheExtensionSentenceIsRendered(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2) . "/resources/views/privacy.blade.php");

        $this->assertStringContainsString("@lang('privacy.monitoring.extension')", $view);
    }

    /** @return array<string, mixed> */
    private function monitoring(string $locale): array
    {
        return (require dirname(__DIR__, 2) . "/lang/$locale/privacy.php")["monitoring"] ?? [];
    }

    /** @return list<string> dotted keys of every string in the array */
    private function keys(array $strings, string $prefix = ""): array
    {
        $keys = [];
        foreach ($strings as $key => $value) {
            $keys = [...$keys, ...(is_array($value) ? $this->keys($value, "$prefix$key.") : ["$prefix$key"])];
        }
        return $keys;
    }

    /** @return list<string> real locale directories with a privacy.php; the regional ones are symlinks */
    private function locales(): array
    {
        $locales = [];
        foreach (glob(dirname(__DIR__, 2) . "/lang/*/privacy.php") as $path) {
            if (is_link(dirname($path))) {
                continue;
            }
            $locales[] = basename(dirname($path));
        }
        return $locales;
    }
}
