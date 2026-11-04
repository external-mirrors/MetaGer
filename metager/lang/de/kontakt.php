<?php
return [
    'lead' => 'Am schnellsten antworten wir per E-Mail.',
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-Mail',
    ],
    'letter' => [
        '1' => 'Post',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Germany",
    ],
    'email' => [
        'write' => 'E-Mail schreiben',
        'pgp' => [
            'summary' => 'Verschlüsselt schreiben (PGP)',
            'signed' => 'Jede E-Mail von uns ist mit unserem OpenPGP-Schlüssel signiert und trägt ihn auch im Autocrypt-Header. Mit diesem Schlüssel können Sie uns verschlüsselt schreiben:',
            'replies' => [
                'heading' => 'Verschlüsselte Antworten',
                'how' => 'Verschlüsselt antworten wir automatisch, sobald wir Ihren Schlüssel haben und ihm vertrauen. Dafür signieren Sie Ihre E-Mail an uns. Ihren Schlüssel nehmen wir aus der E-Mail (als Anhang oder Autocrypt-Header) oder von keys.openpgp.org. Wir verwenden ihn von selbst, wenn',
                'trusted_when' => [
                    'keys.openpgp.org ihn für Ihre Adresse ausgibt (Ihre Adresse dort also bestätigt ist), oder',
                    'Ihre E-Mail eine gültige DKIM-Signatur Ihrer Absender-Domain trägt.',
                ],
                'otherwise' => 'Sonst bestätigt ihn erst jemand von uns von Hand. Ab dann verschlüsseln wir alles, was wir Ihnen schicken – nicht nur Antworten.',
                'tofu' => 'Dem ersten Schlüssel, den wir so für Ihre Adresse erhalten, vertrauen wir (Trust on First Use). Einen neuen Schlüssel für dieselbe Adresse übernehmen wir nicht automatisch: Bis wir ihn geprüft haben, verschlüsseln wir weiter mit dem bisherigen.',
                'never_plain' => 'Auf eine verschlüsselte E-Mail antworten wir nie unverschlüsselt. Fehlt uns Ihr Schlüssel, können wir nicht antworten – signieren Sie verschlüsselte E-Mails an uns deshalb bitte immer.',
            ],
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> oder auf <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP-Fingerabdruck: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'hint' => 'Geht es um ein Suchergebnis, das entfernt werden soll? Bitte lesen Sie vorher <a href=":link">unsere Anleitung</a>: MetaGer zeigt die Ergebnisse anderer Suchmaschinen, entfernen lässt sich ein Ergebnis deshalb in der Regel nur dort.',
    ],
    'phone' => [
        'heading' => 'Telefon',
        'hours' => 'Montag bis Freitag, :from–:to Uhr (deutsche Zeit)',
        'outside' => 'In dieser Zeit erreichen Sie uns fast immer, außerhalb fast nie. Schreiben Sie uns dann besser eine E-Mail.',
    ],
    'topics' => [
        'heading' => 'Häufige Anliegen',
        'key' => [
            'title' => 'MetaGer-Schlüssel und Token',
            'text' => 'Wie der Schlüssel funktioniert, Aufladen und Guthaben',
        ],
        'orders' => [
            'title' => 'Bestellungen und Rechnungen',
            'text' => 'Eine Bestellung finden, Belege und Erstattung',
        ],
        'donation' => [
            'title' => 'Spenden',
            'text' => 'MetaGer mit einer Spende unterstützen',
        ],
        'membership' => [
            'title' => 'Mitglied werden',
            'text' => 'Im SUMA-EV mitmachen, dem Verein hinter MetaGer',
        ],
        'help' => [
            'title' => 'Hilfe zur Suche',
            'text' => 'Funktionen, Einstellungen und häufige Fragen',
        ],
        'privacy' => [
            'title' => 'Datenschutz',
            'text' => 'Was wir speichern und was nicht',
        ],
    ],
];
