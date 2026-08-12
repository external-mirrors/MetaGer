<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * `available` on `/meta/settings/schema`'s `foki` entries - added so headless
 * clients (the mobile app) can hide a fokus none of whose searchengines
 * support the requested language, the same way `index.blade.php`/
 * `foki.blade.php` already decide which fokus tabs the website itself shows.
 *
 * Exercised against the real engine/fokus config (`config/foki.json`,
 * `App\Searchengines::available_foki`) rather than a fake registry, since the
 * point of the field is what it says about *this* config, not the wiring in
 * the abstract - `science`'s BASE/tuhh/minism_science engines' language
 * restrictions are what make it a genuine positive/negative pair here.
 */
class SettingsSchemaAvailableFokiTest extends TestCase
{
    private function fokusEntry(string $lang, string $fokus): array
    {
        $json = $this->get("/meta/settings/schema?lang={$lang}")->json();
        foreach ($json['foki'] as $entry) {
            if ($entry['fokus'] === $fokus) {
                return $entry;
            }
        }
        $this->fail("fokus '{$fokus}' missing from schema response for lang={$lang}");
    }

    #[Test]
    public function marks_every_fokus_available_or_not_with_a_boolean(): void
    {
        $json = $this->get('/meta/settings/schema?lang=de')->json();

        $this->assertNotEmpty($json['foki']);
        foreach ($json['foki'] as $entry) {
            $this->assertArrayHasKey('available', $entry);
            $this->assertIsBool($entry['available']);
        }
    }

    #[Test]
    public function keeps_science_available_in_german_and_english(): void
    {
        $this->assertTrue($this->fokusEntry('de', 'science')['available']);
        $this->assertTrue($this->fokusEntry('en', 'science')['available']);
    }

    #[Test]
    public function hides_science_for_a_language_none_of_its_engines_support(): void
    {
        $this->assertFalse($this->fokusEntry('es', 'science')['available']);
    }

    #[Test]
    public function still_returns_the_fokus_settings_for_one_marked_unavailable(): void
    {
        // Additive, not a filter - a client that wants to manage a hidden
        // fokus's engines/filters anyway (e.g. it was already selected before
        // a language change) still gets the data to do that.
        $entry = $this->fokusEntry('es', 'science');

        $this->assertFalse($entry['available']);
        $this->assertNotEmpty($entry['engines']);
        $this->assertSame('science_blpage', $entry['blacklistSettingKey']);
    }
}
