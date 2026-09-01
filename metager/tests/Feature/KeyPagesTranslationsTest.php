<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * Die sieben aus dem Keymanager übernommenen Seiten sind überall übersetzt.
 *
 * Gleiche Bauart wie {@see AccountTranslationsTest} und aus demselben Grund:
 * eine fehlende Sprachdatei fällt nicht auf, sie fällt auf `fallback_locale`
 * zurück — und das ist hier `de`. Ein portugiesischer Besucher bekäme also
 * deutsche AGB, ohne dass irgendwo etwas rot wird.
 *
 * Diese Dateien sind neu und wurden vollständig geschrieben, also kann der Test
 * von Anfang an grün sein — anders als bei den gewachsenen Dateien, über die
 * {@see CatalanTranslationsTest} Buch führt.
 *
 * Portugiesisch ist der Sonderfall: der Keymanager hatte kein `pt`. Für
 * `login.php` bis `key-create.php` wurden alle sechs Dateien für pt aus de/en
 * neu geschrieben; `checkout.php` trägt für Bar und die Zustimmung zwar
 * wörtlich übernommenen Text aus den elf Sprachen, die der Keymanager hatte —
 * nur eben nicht aus einer zwölften, die es dort nie gab, und die deshalb wie
 * die anderen sechs Dateien neu übersetzt ist.
 */
class KeyPagesTranslationsTest extends TestCase
{
    private const BASE_LOCALE = "en";

    private const FILES = [
        "price.php",
        "agb.php",
        "help/key.php",
        "help/anonymous-token.php",
        // Die Anmeldeseite. Anders als die vier davor stand ihr Text im
        // Keymanager schon in elf Sprachen; übernommen ist er trotzdem nicht
        // wörtlich — lang/de/login.php sagt, was neu ist und warum.
        "login.php",
        // Die Seite zum Erstellen. Im Keymanager stand ihr Text als
        // `generate.*` in derselben login.json wie der der Anmeldung, in elf
        // Sprachen — und mit denselben Weblate-Schäden: „Copy key“ war in fünf
        // Sprachen zu einem Substantiv geworden („Chiave di copia“), im
        // Französischen sogar zur Tastatur-Taste („Touche de copie“). Neu
        // geschrieben statt übernommen.
        "key-create.php",
        // Aufladen — App\Http\Controllers\ChargeController. cash/consent sind
        // wortgleich aus checkout.json/order.json des Keymanagers übernommen
        // (siehe lang/de/checkout.php); page/manual sind neu.
        "checkout.php",
        // Bestellungen — App\Http\Controllers\OrderController. lookup/* und die
        // Zeilenbeschriftungen stammen aus order.json des Keymanagers (dessen
        // beschädigtes „Load orders" ausgenommen); show.heading/lookup_hint
        // sind neu, pt ist wie immer neu übersetzt.
        "orders.php",
    ];

    public function testEveryLocaleTranslatesEveryString(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            foreach (self::FILES as $file) {
                $path = $this->path($locale, $file);
                if (!is_file($path)) {
                    $problems[] = "$locale/$file fehlt ganz";
                    continue;
                }

                $base = $this->keys($this->path(self::BASE_LOCALE, $file));
                $translated = $this->keys($path);

                foreach (array_diff($base, $translated) as $key) {
                    $problems[] = "$locale/$file fehlt $key";
                }
                foreach (array_diff($translated, $base) as $key) {
                    $problems[] = "$locale/$file hat $key, was " . self::BASE_LOCALE . " nicht hat";
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /**
     * Platzhalter, die eine Übersetzung verliert, erreichen den Leser als
     * ":tokenlink" mitten im Satz — oder, schlimmer, als Link, der nirgendwohin
     * führt.
     *
     * Verglichen wird gegen die Platzhalter, die `en` an derselben Stelle
     * benutzt, statt nach jedem ":wort" zu suchen. Der finnische Vertragstext
     * ist voll von Genitiven wie "§:n" und "SUMA-EV:n" — die sind richtig so,
     * und eine naive Suche würde genau daran scheitern.
     */
    public function testEveryLocaleKeepsEveryPlaceholder(): void
    {
        $problems = [];

        foreach (self::FILES as $file) {
            $base = $this->flatten(require $this->path(self::BASE_LOCALE, $file));

            foreach ($this->locales() as $locale) {
                $path = $this->path($locale, $file);
                if (!is_file($path)) {
                    continue; // schon oben gemeldet
                }
                $translated = $this->flatten(require $path);

                foreach ($base as $key => $value) {
                    $expected = $this->placeholders($value);
                    if ($expected === []) {
                        continue;
                    }
                    $actual = $this->placeholders($translated[$key] ?? "");

                    foreach (array_diff($expected, $actual) as $missing) {
                        $problems[] = "$locale/$file: $key hat :$missing verloren";
                    }
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /**
     * Ein <a> ohne Text ist ein Link, den niemand anklicken kann.
     *
     * Weblate hat das beim Umzug der Startseite in acht Sprachen produziert und
     * auf der Preisseite noch einmal — dort in es, fr und pl. Die sind beim
     * Portieren repariert worden, und das soll so bleiben.
     */
    public function testNoTranslationHasAnEmptyLink(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            foreach (self::FILES as $file) {
                $path = $this->path($locale, $file);
                if (!is_file($path)) {
                    continue;
                }
                foreach ($this->flatten(require $path) as $key => $value) {
                    if (preg_match('#<a\b[^>]*>\s*</a>#', $value)) {
                        $problems[] = "$locale/$file: $key hat ein leeres <a></a>";
                    }
                    if (str_contains($value, 'href="#"')) {
                        $problems[] = "$locale/$file: $key verlinkt auf href=\"#\"";
                    }
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /**
     * Kein Text zeigt noch auf /keys. Die fünf Seiten sind umgezogen; ein
     * Verweis auf den alten Pfad funktioniert nur, solange die Weiterleitung
     * steht, und liest sich dann als Umweg.
     *
     * Ausgenommen ist, was vom Schlüsselvorgang noch dort liegt — /keys/c und
     * das Konto bleiben vorerst und werden bewusst verlinkt.
     */
    public function testNoTranslationPointsAtAMovedKeysPath(): void
    {
        $problems = [];

        foreach ($this->locales() as $locale) {
            foreach (self::FILES as $file) {
                $path = $this->path($locale, $file);
                if (!is_file($path)) {
                    continue;
                }
                foreach ($this->flatten(require $path) as $key => $value) {
                    foreach ([
                        "/keys/cost",
                        "/keys/agb",
                        "/keys/help/",
                        "/keys/key/enter",
                        "/keys/key/create",
                    ] as $moved) {
                        if (str_contains($value, $moved)) {
                            $problems[] = "$locale/$file: $key zeigt noch auf $moved";
                        }
                    }
                }
            }
        }

        $this->assertSame([], $problems);
    }

    /** @return list<string> */
    private function placeholders(string $value): array
    {
        preg_match_all('/(?<![\w:]):([a-zA-Z_][a-zA-Z0-9_]*)/', $value, $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * Nur echte Verzeichnisse. lang/de-DE und die zwanzig Geschwister sind
     * Symlinks auf ihre Basissprache.
     *
     * @return list<string>
     */
    private function locales(): array
    {
        $locales = [];
        foreach (glob($this->langPath() . "/*", GLOB_ONLYDIR) as $dir) {
            if (!is_link($dir)) {
                $locales[] = basename($dir);
            }
        }
        sort($locales);

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

    /** @return list<string> */
    private function keys(string $path): array
    {
        return array_keys($this->flatten(require $path));
    }

    /**
     * @param array<mixed> $values
     * @return array<string, string>
     */
    private function flatten(array $values, string $prefix = ""): array
    {
        $flat = [];
        foreach ($values as $key => $value) {
            $name = $prefix === "" ? (string) $key : "$prefix.$key";
            if (is_array($value)) {
                $flat += $this->flatten($value, $name);
            } else {
                $flat[$name] = (string) $value;
            }
        }

        return $flat;
    }
}
