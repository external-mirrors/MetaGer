<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * /entfernen says the same thing in every language.
 *
 * Someone asking how to get a result removed is often in a hurry and has legal
 * rights at stake, so a guide that silently falls through to another language,
 * or loses an address on the way, fails exactly the reader it is for. A locale
 * is real here when it has a kontakt.php: the contact page links to this guide
 * in all of them.
 *
 * A plain TestCase for the reason {@see AccountTranslationsTest} gives:
 * comparing arrays of strings needs no application.
 */
class RemovalTranslationsTest extends TestCase
{
    private const BASE_LOCALE = 'en';

    /** dotted key => what no translation may lose. */
    private const FACTS = [
        'website.tips.1' => ['17'],
        'website.tips.2' => ['404', '410', 'noindex'],
        'kind.personal_data.text' => ['17'],
        'providers.google' => ['Serper', 'Google'],
        'providers.brave' => ['privacy@brave.com', 'RTBF request'],
        'providers.polias' => ['hallo@polias.eu'],
        'providers.staan' => ['European Search Perspective'],
        'providers.suma' => ['TootNews', 'SUMA-EV', 'Mastodon'],
        'providers.website' => ['BASE'],
        'kind.illegal.text' => ['(URL'],
        'providers.nolm' => ['Nolm', 'SUMA-EV', 'Open Web Index'],
    ];

    public function test_every_locale_has_the_whole_guide(): void
    {
        $expected = array_keys($this->flatten(self::BASE_LOCALE));
        $problems = [];

        foreach ($this->locales() as $locale) {
            $keys = array_keys($this->flatten($locale));
            foreach (array_diff($expected, $keys) as $key) {
                $problems[] = "$locale is missing removal.$key";
            }
            foreach (array_diff($keys, $expected) as $key) {
                $problems[] = "$locale has an extra removal.$key";
            }
        }

        $this->assertSame([], $problems);
    }

    /** A string copied over untranslated is the same failure as a missing one. */
    public function test_no_locale_leaves_a_string_in_english_or_german(): void
    {
        $english = $this->flatten('en');
        $german = $this->flatten('de');
        $problems = [];

        foreach (array_diff($this->locales(), ['en', 'de']) as $locale) {
            foreach ($this->flatten($locale) as $key => $text) {
                if ($text === ($english[$key] ?? null) || $text === ($german[$key] ?? null)) {
                    $problems[] = "$locale removal.$key is not translated";
                }
            }
        }

        $this->assertSame([], $problems);
    }

    public function test_every_locale_keeps_the_placeholders(): void
    {
        $base = $this->flatten(self::BASE_LOCALE);
        $problems = [];

        foreach ($this->locales() as $locale) {
            foreach ($this->flatten($locale) as $key => $text) {
                if ($this->placeholders($text) !== $this->placeholders($base[$key] ?? '')) {
                    $problems[] = "$locale removal.$key changes its placeholders";
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /**
     * Each kind's text tells the reader which label to look for in the provider
     * cards. If the two drift apart the sentence points at nothing.
     */
    public function test_the_kinds_name_the_label_on_the_cards(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            $strings = $this->flatten($locale);
            foreach (['personal_data', 'outdated', 'legal'] as $kind) {
                $label = $strings["providers.kinds.$kind"] ?? '';
                if ($label === '' || ! str_contains($strings["kind.$kind.text"] ?? '', $label)) {
                    $problems[] = "$locale removal.kind.$kind.text does not quote “{$label}”";
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /** Addresses, names and the legal facts are what a reader acts on. */
    public function test_no_locale_loses_an_address_or_a_fact(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            $strings = $this->flatten($locale);
            foreach (self::FACTS as $key => $facts) {
                foreach ($facts as $fact) {
                    if (! str_contains($strings[$key] ?? '', $fact)) {
                        $problems[] = "$locale removal.$key has lost “{$fact}”";
                    }
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /** Search engines cut a description off at about 160 characters. */
    public function testTheDescriptionFitsASearchResult(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            $description = $this->flatten($locale)['meta_description'] ?? '';
            if ($description === '' || mb_strlen($description) > 160) {
                $problems[] = "$locale removal.meta_description has ".mb_strlen($description).' characters';
            }
        }

        $this->assertSame([], $problems);
    }

    /** @return list<string> */
    private function locales(): array
    {
        $locales = [];
        foreach (glob(dirname(__DIR__, 2).'/lang/*/kontakt.php') as $file) {
            if (! is_link(dirname($file))) {
                $locales[] = basename(dirname($file));
            }
        }

        return $locales;
    }

    /** @return array<string, string> dotted key => text; empty when the file is missing */
    private function flatten(string $locale): array
    {
        $file = dirname(__DIR__, 2)."/lang/$locale/removal.php";

        return is_file($file) ? $this->dots(require $file) : [];
    }

    /** @return array<string, string> */
    private function dots(array $strings, string $prefix = ''): array
    {
        $flat = [];
        foreach ($strings as $key => $value) {
            if (is_array($value)) {
                $flat += $this->dots($value, "$prefix$key.");
            } else {
                $flat["$prefix$key"] = $value;
            }
        }

        return $flat;
    }

    /** @return list<string> */
    private function placeholders(string $text): array
    {
        preg_match_all('/(?<![\w:]):[a-zA-Z_][a-zA-Z0-9_]*/', $text, $matches);
        sort($matches[0]);

        return $matches[0];
    }
}
