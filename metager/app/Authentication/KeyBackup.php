<?php

namespace App\Authentication;

use App\SearchSettings;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Http\Request;

/**
 * Die beiden Wege zurück in ein Konto, die man aufbewahren kann.
 *
 * **Zwei URLs, und dass es zwei sind, ist der Punkt.**
 *
 * Das *Lesezeichen* richtet diesen Browser andernorts wieder ein: Schlüssel
 * plus die Sucheinstellungen, die hier gesetzt sind. Der *QR-Code* trägt nur
 * den Schlüssel — er wird abfotografiert, und ein QR-Code wächst mit dem, was
 * in ihm steht. Wer seine Suchmaschinen einzeln abgewählt hat, bekäme sonst ein
 * Bild, das kein Telefon mehr vom Bildschirm liest. Was er leisten muss, ist
 * der Weg zurück ins Konto, und dafür reicht der Schlüssel.
 *
 * Herausgezogen, als das Konto von /keys hierher zog: die Seite zum Erstellen
 * bot beides schon an ({@see \App\Http\Controllers\KeyCreationController}), und
 * das Konto ist die Stelle, an der jemand danach sucht, der es beim Erstellen
 * übersprungen hat. Zwei Abschriften desselben QR-Codes wären zwei Orte, an
 * denen die Fehlerkorrekturstufe künftig auseinanderlaufen kann.
 */
final class KeyBackup
{
    /**
     * Der URL, der diesen Schlüssel und die Einstellungen dieses Browsers
     * wieder einrichtet.
     *
     * `meta/settings/load-settings` ist eine MetaGer-Route und war es immer;
     * der Keymanager baute sie sich mit einer eigenen Liste von Cookie-Namen
     * zusammen, die neben der Liste stand, nach der die Route selbst filtert.
     * Jetzt fragt der Seitenaufbau dieselbe Stelle wie der Empfänger:
     * {@see SearchSettings::isValidSetting()}.
     */
    public static function settingsUrl(Request $request, string $key): string
    {
        $parameters = ["key" => $key];
        $settings = app(SearchSettings::class);

        foreach ($request->cookies->all() as $name => $value) {
            if ($name === "key" || !is_string($value) || $value === "") {
                continue;
            }
            if ($settings->isValidSetting($name, $value)) {
                $parameters[$name] = $value;
            }
        }

        return route("loadSettings", $parameters);
    }

    /**
     * Der QR-Code zum Schlüssel — als Bild im Dokument selbst.
     *
     * Ein `data:`-URI und keine eigene Route: eine Route müsste den Schlüssel
     * in ihrem Pfad oder ihrer Query tragen, und das ist genau der Umweg, den
     * dieser Umzug abschafft.
     *
     * Der Inhalt ist ein URL und nicht der Schlüssel für sich: die Anmeldeseite
     * nimmt eine Bilddatei entgegen und liest den Schlüssel aus dem
     * `key`-Parameter des URLs im QR-Code (`POST /api/json/key/resolve-image`
     * beim Keyserver). Ein QR-Code, in dem nur die UUID stünde, wäre dort
     * unlesbar.
     */
    public static function qrDataUri(string $key): string
    {
        return Builder::create()
            ->data(route("loadSettings", ["key" => $key]))
            // Hoch, wie beim Spenden-QR: dieses Bild wird abfotografiert,
            // ausgedruckt und in Ordnern aufbewahrt, und was es trägt, ist der
            // einzige Weg zurück in ein Konto.
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->build()
            ->getDataUri();
    }
}
