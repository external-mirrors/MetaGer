<?php

/**
 * Die Anmeldeseite — /anmelden.
 *
 * Aus pass/lang/<locale>/login.json des Keymanagers übernommen, aber nicht eins
 * zu eins. Drei Gruppen sind neu:
 *
 *   - `lede`, `key.hint` und `create.prompt`. Die alte Seite stellte drei
 *     gleichrangige Eingabemöglichkeiten nebeneinander und sagte mit keinem Satz,
 *     was ein Schlüssel ist. Wer ohne Vorwissen darauf landete, sah ein
 *     Passwortfeld ohne Beschriftung.
 *   - `errors.*`. Diese Meldungen standen als englische Zeichenketten im Router
 *     des Keymanagers (routes/key.js) und waren damit in keiner Sprache
 *     übersetzt. Sie kommen jetzt als `key_error`-Code zurück und werden hier
 *     benannt.
 *   - `file.hint` und `qr.hint`. Beide Wege waren unbeschriftete Symbole.
 *
 * Nur `generate.*` ist nicht mitgezogen: das ist die Seite zum Erstellen eines
 * Schlüssels, die im Keymanager geblieben ist.
 */
return [
    'heading' => 'Bei MetaGer anmelden',
    'lede' => 'Ihr Schlüssel ist Ihr Konto. Er trägt Ihr Token-Guthaben, und er ist alles, was wir von Ihnen kennen — kein Name, keine E-Mail-Adresse, kein Passwort.',

    'key' => [
        'label' => 'Schlüssel oder Anmeldecode',
        'hint' => '36 Zeichen. Von einem bereits angemeldeten Gerät geht auch das sechsstellige Einmal-Passwort aus dem Übertragen-Dialog.',
        // Kein Übersetzungsgut: die Form eines Schlüssels ist in jeder Sprache
        // dieselbe. Steht als Platzhalter im Feld, damit sichtbar ist, was
        // erwartet wird, ohne dass die Beschriftung dafür herhalten muss.
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Anmelden',
    'or' => 'oder',

    'file' => [
        'button' => 'Sicherungsdatei wählen',
        'hint' => 'Die Datei oder das Bild mit dem QR-Code, das Sie beim Einrichten gespeichert haben.',
    ],

    'qr' => [
        'button' => 'QR-Code scannen',
        'hint' => 'Mit der Kamera dieses Geräts, etwa vom Bildschirm eines anderen.',
        'no_camera' => 'Keine Kamera verfügbar.',
        'invalid' => 'Der QR-Code enthält keinen Schlüssel.',
        'close' => 'Schließen',
    ],

    'create' => [
        'prompt' => 'Noch keinen Schlüssel?',
        'action' => 'Schlüssel einrichten',
    ],

    /**
     * Wovon die Anmeldung abgewiesen wurde. Der Keymanager schickt den Code als
     * `key_error` zurück (routes/key.js), diese Seite benennt ihn.
     */
    'errors' => [
        'invalid_key' => 'Das ist kein gültiger Schlüssel. Ein Schlüssel hat 36 Zeichen, ein Anmeldecode sechs Ziffern.',
        'invalid_login_code' => 'Dieser Anmeldecode gilt nicht mehr. Er ist nur wenige Sekunden gültig und nur für eine einzige Anmeldung — lassen Sie sich auf dem angemeldeten Gerät einen neuen anzeigen.',
        'invalid_key_payment_id' => 'Das ist eine Zahlungsnummer, kein Schlüssel. Ihr Schlüssel hat 36 Zeichen und keine Z am Anfang.',
        'no_input' => 'Bitte geben Sie einen Schlüssel ein oder wählen Sie eine Sicherungsdatei.',
        'file_unreadable' => 'Aus dieser Datei ließ sich kein Schlüssel lesen. Sie sollte den QR-Code enthalten, den Sie beim Einrichten gespeichert haben.',
    ],

    /**
     * Die clientseitigen Meldungen. Sie erscheinen beim Tippen, noch bevor
     * abgeschickt wird, und haben deshalb einen anderen Ton als die oben: sie
     * beschreiben die Eingabe, sie weisen nichts ab.
     */
    'validation' => [
        'hex' => 'Ein Schlüssel enthält nur die Zeichen 0–9, a–f und Bindestriche.',
        'uuid' => 'Das ist kein gültiger Schlüssel.',
        'login' => 'Das ist weder ein vollständiger Schlüssel noch ein Anmeldecode.',
    ],

    /**
     * Der Schlüssel stimmt, aber es ist nichts darauf. Fast immer ein Tippfehler
     * in einer Stelle, die zufällig wieder einen gültigen Schlüssel ergibt —
     * deshalb eine Rückfrage und keine Abweisung.
     */
    'empty_key' => [
        'message' => 'Auf diesem Schlüssel ist kein Guthaben. Wenn das so sein soll, melden Sie sich an — sonst hat sich vielleicht ein Zeichen vertippt.',
        'entered' => 'Eingegebener Schlüssel',
        'revalidate' => 'Eingabe prüfen',
        'confirm' => 'Trotzdem anmelden',
    ],

    'extension' => [
        'heading' => 'MetaGer-Erweiterung für Ihren Browser',
        'text' => 'Bleiben Sie auch nach dem Löschen der Browserdaten angemeldet — und trotz Anmeldung <a href=":tokenlink">beweisbar anonym</a>.',
        // Der Knopf nennt den Browser, in dem die Seite gerade steht —
        // App\Support\Browser weiß welcher. Kennt er keinen mit Erweiterung,
        // führt der Knopf auf die Plugin-Seite und heißt dann allgemein.
        'install' => 'Für :browser installieren',
        'install_generic' => 'Erweiterung installieren',
    ],
];
