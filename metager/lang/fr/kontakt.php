<?php
return [
    'headline' => [
        '1' => 'Contact',
        '2' => 'Courriel',
        'pgp' => 'Cryptage',
    ],
    'letter' => [
        '1' => 'Par courrier postal',
        '2' => 'Nous préférons les contacts numériques. Toutefois, si vous estimez qu\'il est nécessaire de nous contacter par voie postale, vous pouvez nous envoyer un courrier à l\'adresse suivante :',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Allemagne",
    ],
    'email' => [
        'text' => 'Vous pouvez nous contacter en envoyant un courrier à : <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Nos courriers électroniques sont signés de manière cryptographique. Si vous souhaitez vérifier la signature ou envoyer votre courrier crypté, veuillez utiliser la clé publique suivante. Si vous souhaitez recevoir une réponse cryptée, veuillez joindre votre clé publique à votre courrier crypté et signé.',
            'pubkey' => 'PGP Publickey : <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> ou sur <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Empreinte PGP : 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
