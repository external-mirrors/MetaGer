<?php

namespace App\Support;

use Illuminate\Support\Number;

/**
 * Beträge, wie der Keyserver sie liefert, in der Sprache der Anfrage.
 *
 * Der Keyserver antwortet mit Dezimalzeichenketten im englischen Format —
 * `"9.35"`, `"10.00"` —, und die Bestellseiten gaben sie so aus, wie sie
 * ankamen: `9.35 €` auf einer deutschen Seite, direkt neben einem `1.000
 * Token`, das {@see Number::format()} korrekt lokalisiert hatte. Zwei
 * Trennzeichenkonventionen in derselben Tabelle, und die eine davon ist in
 * der anderen die Tausendertrennung.
 *
 * Die Zeilentabelle setzt das Währungszeichen selbst nicht mehr (`euro()`
 * bringt es mit, in der Stellung, die die jeweilige Sprache vorsieht —
 * hinten im Deutschen, vorn im Englischen).
 *
 * Der Eingabewert ist bewusst `string|float|int`: alles, was der Keyserver
 * je als Betrag geschickt hat. Ein nicht-numerischer Wert kommt unverändert
 * zurück, statt zu `0,00 €` zu werden — eine Null zu zeigen, wo eine Zahl
 * fehlt, ist auf einer Rechnungsseite die schlechtere Antwort.
 */
final class Money
{
    /** Der Betrag mit Währungszeichen, in der Sprache der Anfrage. */
    public static function euro(string|float|int $value): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        return Number::currency((float) $value, "EUR", app()->getLocale());
    }
}
