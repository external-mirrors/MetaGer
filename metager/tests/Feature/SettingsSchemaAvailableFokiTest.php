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

    /**
     * @return array<string, bool> engine name => available
     */
    private function engineAvailability(string $lang, string $fokus): array
    {
        $engines = [];
        foreach ($this->fokusEntry($lang, $fokus)['engines'] as $engine) {
            $engines[$engine['name']] = $engine['available'];
        }

        return $engines;
    }

    #[Test]
    public function marks_an_engine_unavailable_for_a_language_its_index_does_not_cover(): void
    {
        // `tuhh` and `minism_science` are German-only indexes sitting in
        // `science` alongside `BASE`, which covers both. A search already
        // drops the ones that cannot serve the current locale
        // (`SearchengineConfiguration::applyLocale()`), so a client offering
        // one as a toggle for a language it cannot serve was offering
        // something that could never return a result.
        //
        // This used to be exercised with `onenewspage`/`onenewspagegermany`
        // (English-only / German-only, both in `nachrichten`), until both were
        // disabled outright at the config level (One News Page Ltd.'s feed
        // broke, see their CONFIG_OVERLOAD) and dropped from the schema
        // response entirely - see keeps_disabled_engines_out_of_the_schema.
        $german = $this->engineAvailability('de', 'science');
        $this->assertTrue($german['tuhh']);

        $english = $this->engineAvailability('en', 'science');
        $this->assertFalse($english['tuhh']);
    }

    #[Test]
    public function keeps_disabled_engines_out_of_the_schema(): void
    {
        // onenewspage/onenewspagegermany are disabled at the config level
        // (One News Page Ltd.'s feed API broke on both hosts) rather than
        // merely unavailable for a language, so - like Yandex, like any other
        // retired integration - they are never offered as a toggle at all.
        $german = $this->engineAvailability('de', 'nachrichten');
        $this->assertArrayNotHasKey('onenewspage', $german);
        $this->assertArrayNotHasKey('onenewspagegermany', $german);

        $english = $this->engineAvailability('en', 'nachrichten');
        $this->assertArrayNotHasKey('onenewspage', $english);
        $this->assertArrayNotHasKey('onenewspagegermany', $english);
    }

    #[Test]
    public function keeps_an_engine_available_when_it_serves_the_language(): void
    {
        // Brave and Serper are configured for every language MetaGer offers, so
        // they are the control: a bug that marked engines unavailable wholesale
        // would still pass the assertions above.
        foreach (['de', 'en', 'es'] as $lang) {
            $engines = $this->engineAvailability($lang, 'nachrichten');
            $this->assertTrue($engines['brave_news'], "brave_news for {$lang}");
            $this->assertTrue($engines['serper_news'], "serper_news for {$lang}");
        }
    }

    #[Test]
    public function a_fokus_is_available_exactly_when_one_of_its_engines_is(): void
    {
        // The two flags are computed from different code (`App\Searchengines`
        // for the fokus, the engine's own language config for the engine), and
        // they contradicting each other is what a client cannot make sense of.
        foreach (['de', 'en', 'es', 'fr'] as $lang) {
            foreach ($this->get("/meta/settings/schema?lang={$lang}")->json()['foki'] as $entry) {
                $anyEngine = in_array(true, array_column($entry['engines'], 'available'), true);
                $this->assertSame(
                    $entry['available'],
                    $anyEngine,
                    "fokus '{$entry['fokus']}' for lang={$lang}",
                );
            }
        }
    }
}
