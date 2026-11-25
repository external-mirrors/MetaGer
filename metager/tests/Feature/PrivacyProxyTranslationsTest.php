<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * The privacy policy's SafeBrowse section says in every language what a session's browser keeps
 * and what hibernation stores (safe-browse docs/ideas/hibernation.md). A locale missing a
 * sentence does not fail by itself: Laravel falls through to the fallback locale, and a reader
 * is told in German.
 *
 * A plain TestCase for the reason {@see PrivacyMonitoringTranslationsTest} gives.
 */
class PrivacyProxyTranslationsTest extends TestCase
{
    private const KEYS = ["title", "description", "session", "hibernation"];

    public function testEveryLocaleCarriesTheWholeSection(): void
    {
        $problems = [];
        foreach ($this->locales() as $locale) {
            $proxy = (require dirname(__DIR__, 2) . "/lang/$locale/privacy.php")["contexts"]["proxy"] ?? [];
            foreach (self::KEYS as $key) {
                if (!is_string($proxy[$key] ?? null) || trim($proxy[$key]) === "") {
                    $problems[] = "$locale is missing privacy.contexts.proxy.$key";
                }
            }
            // People know the service by its button, so the text names it by the button's own label.
            if (!str_contains($proxy["session"] ?? "", ":button")) {
                $problems[] = "$locale: privacy.contexts.proxy.session does not name the :button";
            }
        }
        $this->assertSame([], $problems);
    }

    public function testBothParagraphsAreRendered(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2) . "/resources/views/privacy.blade.php");
        $this->assertStringContainsString("@lang('privacy.contexts.proxy.session', ['button' => __('result.options.anonym')])", $view);
        $this->assertStringContainsString("@lang('privacy.contexts.proxy.hibernation')", $view);
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
