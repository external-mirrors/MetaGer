<?php
return [
    'letter' => [
        '3' => "SUMA-EV\r
Röselerstr. 3\r
30159 Hannover\r
Tyskland",
        '1' => 'Med brevpost',
        '2' => 'Vi föredrar digital kontakt. Om du anser att det är nödvändigt att kontakta oss postalt kan du skicka e-post till oss på',
    ],
    'error' => [
        '1' => 'Vi beklagar, men vi har tyvärr inte fått några uppgifter om din kontaktförfrågan. Meddelandet skickades inte.',
        '2' => 'Det uppstod ett fel i leveransen av ditt meddelande. Du kan kontakta oss direkt under :email',
    ],
    'success' => [
        '1' => 'Ditt meddelande levererades framgångsrikt. Ett första automatiskt svar skickades till :email.',
    ],
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-post',
        'pgp' => 'Kryptering',
    ],
    'form' => [
        '1' => 'Anonymt kontaktformulär',
        '2' => 'Du kan skicka ett anonymt meddelande till oss genom att använda detta formulär. Om du väljer att inte ange din e-postadress kommer du naturligtvis inte att få något svar.',
        'name' => 'Namn',
        '5' => 'Din e-postadress (frivillig uppgift)',
        '6' => 'Ditt meddelande',
        '7' => 'Ämne',
        '8' => 'Skicka',
        '9' => 'Upp till 5 bilagor (filstorlek < 5 MB)',
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
