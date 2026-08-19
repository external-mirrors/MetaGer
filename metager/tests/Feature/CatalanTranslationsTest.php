<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * The Catalan translations are complete, and stay complete.
 *
 * `ca-ES` was added to `config/laravellocalization.php` with `lang/ca` holding
 * nothing but an empty `index.php`, so every string on the site fell through to
 * the fallback locale. Filling it in is only half the job: the failure mode of a
 * translation set is not a crash but a silently English page, which no other
 * test in the suite would notice.
 *
 * So this pins the two things that make `ca` a real locale rather than a
 * directory: it carries every file `en` carries, with the same keys, and every
 * `:placeholder` a string interpolates survives translation. A dropped
 * placeholder is the one translation bug that *does* reach the user as garbage —
 * `trans('metaGer.results.failedSitesearch')` renders the literal word `:site`
 * where the domain should be — and it is invisible until someone reads that
 * page in Catalan.
 *
 * `en` is the base by construction: it is the fallback locale, so any key it has
 * and `ca` lacks is a string a Catalan visitor sees in English. The reverse — a
 * key only `ca` has — is dead weight that will drift, so it fails too.
 *
 * Deliberately scoped to `ca`. The other locales are *not* complete against `en`
 * (da, fi, fr, it, nl, pl and sv are all missing the easy-language help, and
 * every locale but `en` carries a `keyboard-navigation.php` no view references
 * any more), and widening this test to them would mean either a red suite or a
 * pile of exemptions. Whoever finishes one of those can add it to the loop.
 *
 * Two implementation notes, both of which were mistakes first:
 *
 * It extends PHPUnit's `TestCase`, not `Tests\TestCase`, because comparing two
 * arrays of strings needs no application. Booting one per case cost more than
 * this test is worth.
 *
 * And it walks all 84 files inside four test methods rather than fanning out
 * over a data provider. The provider version read better and ran 170 cases whose
 * retained fixtures pushed `artisan test` past its 128 MB limit — the suite then
 * died in the QR-code writer, which has nothing to do with translations and is
 * exactly the kind of unrelated-looking failure that costs an afternoon.
 * Aggregating means one assertion per concern, listing every offending file.
 */
class CatalanTranslationsTest extends TestCase
{
    private const BASE_LOCALE = 'en';

    private const TRANSLATED_LOCALE = 'ca';

    /** Every `:placeholder` Laravel would replace in a translation string. */
    private const PLACEHOLDER_PATTERN = '/(?<![\w:]):[a-zA-Z_][a-zA-Z0-9_]*/';

    public function testCatalanHasEveryFileTheBaseLocaleHas(): void
    {
        $missing = array_diff($this->translationFiles(self::BASE_LOCALE), $this->translationFiles(self::TRANSLATED_LOCALE));

        $this->assertSame([], array_values($missing), 'Translation files missing from lang/ca');
    }

    public function testCatalanHasNoFilesTheBaseLocaleDoesNotHave(): void
    {
        $extra = array_diff($this->translationFiles(self::TRANSLATED_LOCALE), $this->translationFiles(self::BASE_LOCALE));

        $this->assertSame([], array_values($extra), 'Translation files in lang/ca with no lang/en counterpart');
    }

    public function testCatalanHasTheSameKeysAsTheBaseLocale(): void
    {
        $missing = [];
        $unknown = [];

        foreach ($this->translationFiles(self::BASE_LOCALE) as $file) {
            $base = array_keys($this->flatten($this->load(self::BASE_LOCALE, $file)));
            $translated = array_keys($this->flatten($this->load(self::TRANSLATED_LOCALE, $file)));

            foreach (array_diff($base, $translated) as $key) {
                $missing[] = "$file: $key";
            }
            foreach (array_diff($translated, $base) as $key) {
                $unknown[] = "$file: $key";
            }
        }

        $this->assertSame([], $missing, 'Keys missing from lang/ca');
        $this->assertSame([], $unknown, 'Keys in lang/ca with no lang/en counterpart');
    }

    public function testCatalanKeepsEveryPlaceholder(): void
    {
        $changed = [];

        foreach ($this->translationFiles(self::BASE_LOCALE) as $file) {
            $base = $this->flatten($this->load(self::BASE_LOCALE, $file));
            $translated = $this->flatten($this->load(self::TRANSLATED_LOCALE, $file));

            foreach ($base as $key => $value) {
                if (! is_string($value) || ! isset($translated[$key]) || ! is_string($translated[$key])) {
                    continue;
                }

                $expected = $this->placeholders($value);
                $actual = $this->placeholders($translated[$key]);

                if ($expected !== $actual) {
                    $changed[] = sprintf(
                        '%s: %s (expected %s, got %s)',
                        $file,
                        $key,
                        implode(' ', $expected) ?: 'none',
                        implode(' ', $actual) ?: 'none'
                    );
                }
            }
        }

        $this->assertSame([], $changed, 'Placeholders changed in lang/ca');
    }

    /**
     * The lang directory, resolved from this file rather than through `lang_path()`.
     *
     * Nothing boots the application here, so the helper does not exist — it fails
     * with "Call to undefined method Container::langPath()", which reads like a
     * Laravel bug and is not one.
     */
    private static function langDirectory(string $locale): string
    {
        return dirname(__DIR__, 2).'/lang/'.$locale;
    }

    /** @return list<string> paths relative to the locale directory, sorted */
    private function translationFiles(string $locale): array
    {
        $directory = self::langDirectory($locale);

        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $entry) {
            if ($entry->isFile() && $entry->getExtension() === 'php') {
                $files[] = substr($entry->getPathname(), strlen($directory) + 1);
            }
        }

        sort($files);

        return $files;
    }

    /** @return array<mixed> */
    private function load(string $locale, string $file): array
    {
        $path = self::langDirectory($locale).DIRECTORY_SEPARATOR.$file;
        $this->assertFileExists($path);

        $contents = require $path;
        $this->assertIsArray($contents, "lang/$locale/$file must return an array");

        return $contents;
    }

    /**
     * @param  array<mixed>  $translations
     * @return array<string, mixed> dot-notation key => leaf value
     */
    private function flatten(array $translations, string $prefix = ''): array
    {
        $flat = [];
        foreach ($translations as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            if (is_array($value)) {
                $flat += $this->flatten($value, $path);
            } else {
                $flat[$path] = $value;
            }
        }

        return $flat;
    }

    /** @return list<string> the placeholders in a string, sorted and deduplicated */
    private function placeholders(string $value): array
    {
        preg_match_all(self::PLACEHOLDER_PATTERN, $value, $matches);

        $found = array_values(array_unique($matches[0]));
        sort($found);

        return $found;
    }
}
