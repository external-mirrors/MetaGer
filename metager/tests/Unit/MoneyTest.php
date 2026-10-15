<?php

namespace Tests\Unit;

use App\Support\Money;
use Tests\TestCase;

/**
 * App\Support\Money — Beträge des Keyservers in der Sprache der Anfrage.
 *
 * Der Keyserver antwortet mit englisch formatierten Dezimalzeichenketten, und
 * die Bestellseiten gaben sie unverändert aus: „9.35 €" auf einer deutschen
 * Seite, direkt neben einem „1.000 Token", das lokalisiert war. Was hier
 * geprüft wird, ist genau dieser Unterschied — und der Randfall, bei dem
 * stillschweigend eine Null entstünde.
 */
class MoneyTest extends TestCase
{
    public function testGermanUsesACommaAndPutsTheSymbolLast(): void
    {
        $this->app->setLocale("de");

        // Geschütztes Leerzeichen vor dem Zeichen — so setzt es der
        // Intl-Formatter, und so soll die Währung nie allein umbrechen.
        $this->assertSame("9,35\u{00A0}€", Money::euro("9.35"));
        $this->assertSame("1.234,50\u{00A0}€", Money::euro("1234.5"));
    }

    public function testEnglishUsesAPointAndPutsTheSymbolFirst(): void
    {
        $this->app->setLocale("en");

        $this->assertSame("€9.35", Money::euro("9.35"));
    }

    /**
     * Eine Null zu zeigen, wo eine Zahl fehlt, ist auf einer Rechnungsseite
     * die schlechtere Antwort — `(float) "unbekannt"` wäre 0.0 und stünde
     * dann als „0,00 €" in der Zeile, als wäre nichts bezahlt worden.
     */
    public function testANonNumericValueComesBackUnchanged(): void
    {
        $this->app->setLocale("de");

        $this->assertSame("—", Money::euro("—"));
    }
}
