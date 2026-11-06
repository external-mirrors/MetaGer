<?php
return [
    'lead' => 'Per e-mail antwoorden we het snelst.',
    'letter' => [
        '1' => 'Post',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Duitsland",
    ],
    'headline' => [
        '1' => 'Neem contact op met',
        '2' => 'E-mail',
    ],
    'email' => [
        'write' => 'E-mail schrijven',
        'pgp' => [
            'summary' => 'Versleuteld schrijven (PGP)',
            'signed' => 'Elke e-mail van ons is ondertekend met onze OpenPGP-sleutel, die ook in de Autocrypt-header meegaat. Met deze sleutel kun je ons versleuteld schrijven:',
            'replies' => [
                'heading' => 'Versleutelde antwoorden',
                'how' => 'We antwoorden automatisch versleuteld zodra we je sleutel hebben en die vertrouwen. Onderteken daarvoor je e-mail aan ons. We halen je sleutel uit de e-mail (als bijlage of in de Autocrypt-header) of van keys.openpgp.org, en gebruiken hem uit onszelf als',
                'trusted_when' => [
                    'keys.openpgp.org hem voor je adres uitgeeft (je adres is daar dus bevestigd), of',
                    'je e-mail een geldige DKIM-handtekening van je afzenderdomein draagt.',
                ],
                'otherwise' => 'Anders bevestigt iemand van ons hem eerst handmatig. Vanaf dan versleutelen we alles wat we je sturen, niet alleen antwoorden.',
                'tofu' => 'We vertrouwen de eerste sleutel die we zo voor je adres krijgen (Trust on First Use). Een nieuwe sleutel voor hetzelfde adres nemen we niet automatisch over: tot we hem hebben gecontroleerd, versleutelen we met de vorige.',
                'never_plain' => 'Op een versleutelde e-mail antwoorden we nooit onversleuteld. Ontbreekt je sleutel, dan kunnen we niet antwoorden – onderteken versleutelde e-mails aan ons dus altijd.',
            ],
            'pubkey' => 'PGP publieke sleutel: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> of op <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Vingerafdruk: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'heading' => 'Verwijdering',
        'only' => 'U kunt ons alleen via ons formulier vragen zoekresultaten te verwijderen. Per e-mail, telefoon of post nemen we zulke verzoeken niet aan.',
        'form' => 'Naar het formulier',
        'guide' => 'Het formulier geldt voor onze eigen zoekindexen. MetaGer toont resultaten van andere zoekmachines; in <a href=":link">onze handleiding</a> staat waar een verwijdering daarvoor werkt.',
    ],
    'phone' => [
        'heading' => 'Telefoon',
        'hours' => 'Maandag tot en met vrijdag, :from–:to uur (Duitse tijd)',
        'outside' => 'Binnen deze tijden bereikt u ons bijna altijd, daarbuiten bijna nooit. Stuur ons dan liever een e-mail.',
    ],
    'topics' => [
        'heading' => 'Veelgestelde vragen',
        'key' => [
            'title' => 'MetaGer-sleutel en tokens',
            'text' => 'Hoe de sleutel werkt, opwaarderen en tegoed',
        ],
        'orders' => [
            'title' => 'Bestellingen en facturen',
            'text' => 'Een bestelling vinden, bewijzen en terugbetaling',
        ],
        'donation' => [
            'title' => 'Doneer',
            'text' => 'Steun MetaGer met een donatie',
        ],
        'membership' => [
            'title' => 'Lid worden',
            'text' => 'Word lid van SUMA-EV, de vereniging achter MetaGer',
        ],
        'help' => [
            'title' => 'Hulp bij zoeken',
            'text' => 'Functies, instellingen en veelgestelde vragen',
        ],
        'privacy' => [
            'title' => 'Privacy',
            'text' => 'Wat we opslaan en wat niet',
        ],
    ],
];
