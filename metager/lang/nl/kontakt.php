<?php
return [
    'letter' => [
        '1' => 'Per briefpost',
        '2' => 'We geven de voorkeur aan digitaal contact. Als je het echter nodig vindt om ons per post te bereiken, kun je ons mailen op:',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Duitsland",
    ],
    'error' => [
        '1' => 'Het spijt ons, maar helaas hebben we geen gegevens ontvangen bij uw contactverzoek. Het bericht is niet verzonden.',
        '2' => 'Er is een fout opgetreden bij het afleveren van uw bericht. U kunt rechtstreeks contact met ons opnemen onder :email',
    ],
    'success' => [
        '1' => 'Je bericht is succesvol afgeleverd. Een eerste automatisch antwoord werd verzonden naar :email.',
    ],
    'headline' => [
        '1' => 'Neem contact op met',
        '2' => 'E-mail',
        'pgp' => 'Encryptie',
    ],
    'form' => [
        '1' => 'Anoniem contactformulier',
        '2' => 'Je kunt ons een anoniem bericht sturen via dit formulier. Als je ervoor kiest om je e-mailadres niet in te vullen, ontvang je uiteraard geen antwoord.',
        'name' => 'Naam',
        '5' => 'Je e-mailadres (optioneel)',
        '6' => 'Uw bericht',
        '7' => 'Onderwerp',
        '8' => 'Stuur',
        '9' => 'Maximaal 5 bijlagen (bestandsgrootte < 5 MB)',
    ],
    'email' => [
        'text' => 'Je kunt ons bereiken door een mail te sturen naar: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Onze e-mails zijn cryptografisch ondertekend. Als je de handtekening wilt verifiëren of je e-mail versleuteld wilt versturen, gebruik dan de volgende publieke sleutel. Als je een versleuteld antwoord wilt ontvangen, voeg dan je publieke sleutel toe aan je versleutelde en ondertekende e-mail.',
            'pubkey' => 'PGP publieke sleutel: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> of op <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Vingerafdruk: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
