<?php
return [
    'lead' => 'Vi svarer hurtigst på e-mail.',
    'letter' => [
        '1' => 'Post',
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
            'signed' => 'Alle e-mails fra os er signeret med vores OpenPGP-nøgle, som også følger med i Autocrypt-headeren. Brug denne nøgle til at skrive krypteret til os:',
            'replies' => [
                'heading' => 'Krypterede svar',
                'how' => 'Vi svarer automatisk krypteret, så snart vi har din nøgle og stoler på den. Signér derfor din e-mail til os. Vi tager din nøgle fra e-mailen (vedhæftet eller i Autocrypt-headeren) eller fra keys.openpgp.org og bruger den af os selv, hvis',
                'trusted_when' => [
                    'keys.openpgp.org udleverer den for din adresse (din adresse er altså bekræftet dér), eller',
                    'din e-mail har en gyldig DKIM-signatur fra dit afsenderdomæne.',
                ],
                'otherwise' => 'Ellers bekræfter en af os den først manuelt. Derefter krypterer vi alt, hvad vi sender dig, ikke kun svar.',
                'tofu' => 'Vi stoler på den første nøgle, vi på denne måde får for din adresse (Trust on First Use). En ny nøgle for samme adresse tager vi ikke automatisk i brug: Indtil vi har tjekket den, krypterer vi fortsat med den tidligere.',
                'never_plain' => 'Vi svarer aldrig ukrypteret på en krypteret e-mail. Mangler vi din nøgle, kan vi ikke svare – signér derfor altid krypterede e-mails til os.',
            ],
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> eller på <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP-fingeraftryk: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'reason' => [
        'legend' => 'Hvad drejer det sig om?',
        'general' => 'Et spørgsmål eller en henvendelse',
        'removal' => 'Fjern et søgeresultat',
    ],
    'removal' => [
        'heading' => 'Få et søgeresultat fjernet',
        'how' => 'Anmodninger om fjernelse foregår online: Vi fører dig trin for trin gennem din anmodning og viser dig, hvor den virker bedst.',
        'only' => 'Vi kan ikke modtage sådanne anmodninger pr. e-mail, telefon eller post.',
        'button' => 'Anmod om fjernelse',
        'why' => [
            'heading' => 'Hvorfor søgemaskiner fjerner resultater',
            'text' => 'Søgemaskiner fjerner et resultat, når der er en juridisk grund til det. Fører et resultat for en persons navn til oplysninger, der er forkerte, forældede eller ikke længere relevante, kan personen kræve, at det ikke længere vises (retten til at blive glemt, GDPR art. 17). Offentlighedens interesse i oplysningerne tæller også med. Krænkelse af ophavsret og strafbart indhold kan også være en grund. Derfor kan der mangle et resultat på MetaGer, som du finder andre steder.',
        ],
    ],
    'phone' => [
        'heading' => 'Telefon',
        'hours' => 'Mandag til fredag kl. :from–:to (tysk tid)',
        'outside' => 'I det tidsrum træffer du os næsten altid, uden for det næsten aldrig. Skriv så hellere en e-mail til os.',
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
            'text' => 'Hvad vi gemmer, og hvad vi ikke gemmer',
        ],
    ],
];
