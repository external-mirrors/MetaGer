<?php
return [
    'letter' => [
        '1' => 'Med brevpost',
        '2' => 'Vi svarer hurtigst på e-mail. Med post kan du skrive til os på:',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Tyskland",
    ],
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-mail',
    ],
    'email' => [
        'write' => 'Skriv en e-mail',
        'pgp' => [
            'summary' => 'Skriv krypteret (PGP)',
            'description' => 'Vores e-mails er kryptografisk signerede. Hvis du vil verificere signaturen eller sende din mail krypteret, skal du bruge følgende offentlige nøgle. Hvis du ønsker at modtage et krypteret svar, bedes du vedhæfte din offentlige nøgle til din krypterede og signerede mail.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> eller på <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP-fingeraftryk: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'hint' => 'Handler det om at få fjernet et søgeresultat? Læs først <a href=":link">vores vejledning</a>: MetaGer viser andre søgemaskiners resultater, så et resultat kan som regel kun fjernes dér.',
    ],
    'topics' => [
        'heading' => 'Almindelige spørgsmål',
        'key' => [
            'title' => 'MetaGer-nøgle og tokens',
            'text' => 'Sådan virker nøglen, optankning og saldo',
        ],
        'orders' => [
            'title' => 'Ordrer og fakturaer',
            'text' => 'Find en ordre, kvitteringer og refusion',
        ],
        'donation' => [
            'title' => 'Donér',
            'text' => 'Støt MetaGer med en donation',
        ],
        'membership' => [
            'title' => 'Bliv medlem',
            'text' => 'Bliv en del af SUMA-EV, foreningen bag MetaGer',
        ],
        'help' => [
            'title' => 'Hjælp til søgning',
            'text' => 'Funktioner, indstillinger og ofte stillede spørgsmål',
        ],
        'privacy' => [
            'title' => 'Privatlivets fred',
            'text' => 'Hvad vi gemmer — og hvad vi ikke gemmer',
        ],
    ],
];
