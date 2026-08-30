<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen.
 *
 * Aus pass/lang/<locale>/login.json des Keymanagers (`generate.*`) übernommen,
 * aber zum größeren Teil neu. Was von dort kommt, ist der Aufbau; was neu ist,
 * sind die Sätze, die erklären, worauf man sich einlässt:
 *
 *   - `lede`, `key.hint`, `continue_hint`. Die alte Seite sagte mit keinem
 *     Wort, was ein Schlüssel ist, was er kostet und warum man ihn aufheben
 *     muss. Sie zeigte eine Zeichenfolge und einen Knopf.
 *   - `save.*`. Vorher stand dort ein einzelner Satz über ein Lesezeichen. Die
 *     Anmeldeseite fragt nach „der Sicherungsdatei mit dem QR-Code, die Sie beim
 *     Einrichten gespeichert haben“ — angeboten hat sie beim Einrichten nie
 *     jemand.
 *   - `errors.*`. Die alte Seite konnte nicht scheitern, weil sie nichts fragen
 *     musste. Diese fragt den Keyserver nach einem Schlüssel, und das kann
 *     schiefgehen.
 *
 * Nicht mitgezogen ist `initialize.membership`: der Hinweis, dass Mitglieder
 * des SUMA-EV MetaGer ohne weitere Kosten verwenden können, stand in allen elf
 * Katalogen und in keiner Vorlage — er war seit Jahren unsichtbar. Wer ihn
 * zurückhaben will, hat mit /preise und der Mitgliedsseite zwei Orte, an denen
 * er hingehört.
 */
return [
    'heading' => 'Schlüssel erstellen',
    'lede' => 'Ihr Schlüssel ist Ihr Konto. Er trägt Ihr Token-Guthaben, und er ist alles, was wir von Ihnen kennen — kein Name, keine E-Mail-Adresse, kein Passwort. Das heißt auch: Wer ihn verliert, verliert das Guthaben darauf.',

    /**
     * Der Fall, den der Support am häufigsten zu hören bekommt: jemand verliert
     * sein Cookie, landet hier, erstellt einen zweiten Schlüssel und sucht dann
     * sein Guthaben. Deshalb steht das über und nicht unter der Karte.
     */
    'existing' => [
        'text' => 'Sie hatten schon einmal einen MetaGer-Schlüssel? Melden Sie sich damit an, statt einen neuen zu erstellen — ein neuer Schlüssel bekommt ein eigenes, getrenntes Guthaben, und das alte bleibt auf dem alten.',
        'action' => 'Mit vorhandenem Schlüssel anmelden',
    ],

    'offer' => [
        'text' => 'Ein Druck auf den Knopf, und Sie haben einen. Kein Formular, keine Anmeldedaten: MetaGer würfelt eine Zeichenfolge, die noch niemandem gehört.',
        'button' => 'Schlüssel jetzt erstellen',
    ],

    'working' => 'Einen Moment: Wir würfeln einen neuen Schlüssel für Sie …',

    'key' => [
        'label' => 'Ihr neuer Schlüssel',
        'hint' => '36 Zeichen. Mit ihnen melden Sie sich auf jedem weiteren Gerät an.',
    ],

    'copy' => [
        'action' => 'Schlüssel kopieren',
        'done' => 'Kopiert',
    ],

    'save' => [
        'heading' => 'Bewahren Sie ihn auf',
        'text' => 'Solange dieser Browser das Cookie behält, bleiben Sie angemeldet. Verliert er es — neues Gerät, gelöschte Browserdaten —, ist dieser Schlüssel der einzige Weg zurück.',

        'qr' => [
            'alt' => 'QR-Code, der zu Ihrem Schlüssel führt',
            'action' => 'Als Bild speichern',
            'hint' => 'Das Bild, nach dem das Anmeldeformular fragt. Sie können es dort später hochladen oder mit der Kamera abfotografieren.',
        ],

        'url' => [
            'label' => 'Lesezeichen',
            'action' => 'URL kopieren',
            'hint' => 'Ein Aufruf dieses URLs richtet den Schlüssel samt der Einstellungen dieses Browsers wieder ein.',
        ],

        'no_cookies' => 'Dieser Browser speichert keine Cookies für MetaGer. Ohne Cookie bleiben Sie nicht angemeldet — dann ist der URL oben der Weg, sich vor einer Suche anzumelden. Sie können ihn auch als Suchmaschine in Ihrem Browser hinterlegen.',
    ],

    'continue' => 'Weiter: Guthaben aufladen',
    'continue_hint' => 'Ein neuer Schlüssel hat noch kein Guthaben. Im nächsten Schritt wählen Sie ein Token-Paket.',

    /**
     * Wovon das Erstellen abgehalten wurde. Alle drei sind Aussagen über uns
     * und nicht über den Besucher — es gibt hier nichts einzugeben, an dem er
     * sich hätte vertun können.
     */
    'errors' => [
        'keyserver_unreachable' => 'Es ließ sich gerade kein Schlüssel erstellen. Das liegt an uns und nicht an Ihnen — versuchen Sie es gleich noch einmal.',
        'too_many_attempts' => 'Von diesem Anschluss wurden gerade sehr viele Schlüssel erstellt. Warten Sie ein paar Minuten und laden Sie die Seite dann neu.',
        'no_key' => 'Der Schlüssel ist auf dem Weg verlorengegangen — das passiert, wenn die Seite lange offen lag. Hier ist ein neuer.',
    ],
];
