<?php
return [
    'headline' => [
        '1' => 'Contacte',
        '2' => 'Correu electrònic',
        'pgp' => 'Xifratge',
    ],
    'email' => [
        'text' => 'Podeu contactar amb nosaltres enviant un correu a: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Els nostres correus estan signats criptogràficament. Si voleu verificar la signatura o enviar-nos el correu xifrat, feu servir la clau pública següent. Si voleu rebre una resposta xifrada, adjunteu la vostra clau pública al correu xifrat i signat.',
            'pubkey' => 'Clau pública PGP: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> o a <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Empremta PGP: 5FA5 2398 C382 B498 B14A  B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'letter' => [
        '1' => 'Per correu postal',
        '2' => 'Preferim el contacte digital. Tanmateix, si considereu necessari contactar amb nosaltres per correu postal, podeu escriure a:',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nAlemanya",
    ],
];
