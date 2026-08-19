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
    'form' => [
        '1' => 'Formulari de contacte anònim',
        '2' => 'Podeu enviar-nos un missatge anònim amb aquest formulari. Ara bé, si decidiu no incloure-hi la vostra adreça electrònica, evidentment no rebreu cap resposta.',
        'name' => 'Nom',
        '5' => 'La vostra adreça electrònica (opcional)',
        '6' => 'El vostre missatge',
        '7' => 'Assumpte',
        '8' => 'Envia',
        '9' => 'Fins a 5 fitxers adjunts (mida < 5 MB)',
        'temperror' => 'Actualment tenim dificultats tècniques. El formulari de contacte tornarà a estar disponible aviat.',
    ],
    'letter' => [
        '1' => 'Per correu postal',
        '2' => 'Preferim el contacte digital. Tanmateix, si considereu necessari contactar amb nosaltres per correu postal, podeu escriure a:',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nAlemanya",
    ],
    'error' => [
        '1' => 'Ho sentim, però malauradament no hem rebut cap dada amb la vostra petició de contacte. El missatge no s\'ha enviat.',
        '2' => 'S\'ha produït un error en enviar el vostre missatge. Podeu contactar directament amb nosaltres a :email',
    ],
    'success' => [
        '1' => 'El vostre missatge s\'ha enviat correctament. S\'ha enviat una primera resposta automàtica a :email.',
    ],
];
