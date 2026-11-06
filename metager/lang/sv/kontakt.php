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
    'reason' => [
        'legend' => 'Vad gäller det?',
        'general' => 'En fråga eller ett ärende',
        'removal' => 'Ta bort ett sökresultat',
    ],
    'removal' => [
        'heading' => 'Få ett sökresultat borttaget',
        'how' => 'Begäranden om borttagning görs online: vi guidar dig steg för steg genom din begäran och visar var den gör mest nytta.',
        'only' => 'Vi kan inte ta emot sådana begäranden via e-post, telefon eller brev.',
        'button' => 'Begär borttagning',
        'why' => [
            'heading' => 'Varför sökmotorer tar bort resultat',
            'text' => 'Sökmotorer tar bort ett resultat när det finns ett rättsligt skäl. Leder ett resultat för en persons namn till uppgifter som är felaktiga, föråldrade eller inte längre relevanta kan personen kräva att det inte längre visas (rätten att bli bortglömd, art. 17 GDPR). Allmänhetens intresse av informationen räknas också. Upphovsrättsintrång och brottsligt innehåll kan också vara skäl. Därför kan ett resultat som du hittar på annat håll saknas på MetaGer.',
        ],
    ],
    'phone' => [
        'heading' => 'Telefon',
        'hours' => 'Måndag till fredag kl. :from–:to (tysk tid)',
        'outside' => 'Under de tiderna når du oss nästan alltid, utanför dem nästan aldrig. Skicka då hellre ett mejl till oss.',
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
            'text' => 'Vad vi sparar och vad vi inte sparar',
        ],
    ],
];
