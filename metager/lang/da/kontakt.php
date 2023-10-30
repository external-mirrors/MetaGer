<?php
return [
    'letter' => [
        '1' => 'Med brevpost',
        '2' => 'Vi foretrækker digital kontakt. Men hvis du finder det nødvendigt at kontakte os postalt, kan du sende os en mail på:',
        '3' => "SUMA-EV\r
Röselerstr. 3\r
30159 Hannover\r
Tyskland",
    ],
    'error' => [
        '1' => 'Vi beklager, men vi har desværre ikke modtaget nogen data med din kontaktanmodning. Beskeden blev ikke sendt.',
        '2' => 'Der opstod en fejl ved levering af din besked. Du kan kontakte os direkte under :email',
    ],
    'success' => [
        '1' => 'Din besked blev leveret med succes. Et første automatisk svar blev sendt til :email.',
    ],
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-mail',
        'pgp' => 'Kryptering',
    ],
    'form' => [
        '1' => 'Anonym kontaktformular',
        '2' => 'Du kan sende os en anonym besked ved at bruge denne formular. Hvis du vælger ikke at angive din e-mailadresse, vil du naturligvis ikke modtage noget svar.',
        'name' => 'Navn',
        '5' => 'Din e-mail-adresse (valgfri)',
        '6' => 'Din besked',
        '7' => 'Emne',
        '8' => 'Send',
        '9' => 'Op til 5 vedhæftede filer (filstørrelse < 5 MB)',
    ],
    'email' => [
        'text' => 'Du kan kontakte os ved at sende en mail til: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Vores e-mails er kryptografisk signerede. Hvis du vil verificere signaturen eller sende din mail krypteret, skal du bruge følgende offentlige nøgle. Hvis du ønsker at modtage et krypteret svar, bedes du vedhæfte din offentlige nøgle til din krypterede og signerede mail.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> eller på <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP-fingeraftryk: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
