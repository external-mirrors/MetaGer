<?php
return [
    'lead' => 'Vi svarar snabbast på e-post.',
    'letter' => [
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Tyskland",
        '1' => 'Post',
    ],
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-post',
    ],
    'email' => [
        'write' => 'Skriv ett mejl',
        'pgp' => [
            'summary' => 'Skriv krypterat (PGP)',
            'signed' => 'Alla mejl från oss är signerade med vår OpenPGP-nyckel, som också följer med i Autocrypt-huvudet. Använd den här nyckeln för att skriva krypterat till oss:',
            'replies' => [
                'heading' => 'Krypterade svar',
                'how' => 'Vi svarar automatiskt krypterat så snart vi har din nyckel och litar på den. Signera därför ditt mejl till oss. Vi tar din nyckel från mejlet (som bilaga eller i Autocrypt-huvudet) eller från keys.openpgp.org och använder den på egen hand om',
                'trusted_when' => [
                    'keys.openpgp.org lämnar ut den för din adress (din adress är alltså bekräftad där), eller',
                    'ditt mejl har en giltig DKIM-signatur från din avsändardomän.',
                ],
                'otherwise' => 'Annars bekräftar någon av oss den först manuellt. Från och med då krypterar vi allt vi skickar till dig, inte bara svar.',
                'tofu' => 'Vi litar på den första nyckeln vi får på det här sättet för din adress (Trust on First Use). En ny nyckel för samma adress tar vi inte i bruk automatiskt: tills vi har kontrollerat den fortsätter vi att kryptera med den tidigare.',
                'never_plain' => 'Vi svarar aldrig okrypterat på ett krypterat mejl. Saknar vi din nyckel kan vi inte svara – signera därför alltid krypterade mejl till oss.',
            ],
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> eller på <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'hint' => 'Gäller det att få ett sökresultat borttaget? Läs först <a href=":link">vår guide</a>: MetaGer visar andra sökmotorers resultat, så ett resultat kan oftast bara tas bort där.',
    ],
    'phone' => [
        'heading' => 'Telefon',
    ],
    'topics' => [
        'heading' => 'Vanliga frågor',
        'key' => [
            'title' => 'MetaGer-nyckel och tokens',
            'text' => 'Hur nyckeln fungerar, påfyllning och saldo',
        ],
        'orders' => [
            'title' => 'Beställningar och fakturor',
            'text' => 'Hitta en beställning, kvitton och återbetalning',
        ],
        'donation' => [
            'title' => 'Donera',
            'text' => 'Stöd MetaGer med en gåva',
        ],
        'membership' => [
            'title' => 'Bli medlem',
            'text' => 'Gå med i SUMA-EV, föreningen bakom MetaGer',
        ],
        'help' => [
            'title' => 'Sökhjälp',
            'text' => 'Funktioner, inställningar och vanliga frågor',
        ],
        'privacy' => [
            'title' => 'Integritet',
            'text' => 'Vad vi sparar — och vad vi inte sparar',
        ],
    ],
];
