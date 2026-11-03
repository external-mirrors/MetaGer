<?php
return [
    'letter' => [
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Tyskland",
        '1' => 'Med brevpost',
        '2' => 'Vi föredrar digital kontakt. Om du anser att det är nödvändigt att kontakta oss postalt kan du skicka e-post till oss på',
    ],
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-post',
        'pgp' => 'Kryptering',
    ],
    'email' => [
        'text' => 'Du kan kontakta oss genom att skicka ett mail till <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Våra e-postmeddelanden är kryptografiskt signerade. Om du vill verifiera signaturen eller skicka ditt e-postmeddelande krypterat ska du använda följande offentliga nyckel. Om du vill få ett krypterat svar bifogar du din publika nyckel till ditt krypterade och signerade e-postmeddelande.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> eller på <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
