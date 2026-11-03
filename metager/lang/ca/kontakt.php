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
    'intro' => 'Abans d’escriure: moltes preguntes ja tenen resposta aquí — normalment més ràpida que per correu.',
    'removal' => [
        'heading' => 'Voleu que s’elimini un resultat de cerca?',
        'text' => 'Llegiu primer la nostra guia. MetaGer mostra els resultats d’altres cercadors, de manera que un resultat només es pot eliminar al lloc web i en aquests cercadors. La guia explica on i com.',
        'link' => 'Llegiu la guia',
    ],
    'topics' => [
        'heading' => 'Altres consultes habituals',
        'key' => [
            'title' => 'Clau de MetaGer i tokens',
            'text' => 'Com funciona la clau, recàrrega i saldo',
        ],
        'orders' => [
            'title' => 'Comandes i factures',
            'text' => 'Trobeu una comanda, rebuts i reemborsaments',
        ],
        'donation' => [
            'title' => 'Feu un donatiu',
            'text' => 'Doneu suport a MetaGer amb un donatiu',
        ],
        'membership' => [
            'title' => 'Feu-vos-en membre',
            'text' => 'Uniu-vos a SUMA-EV, l’associació que hi ha darrere de MetaGer',
        ],
        'help' => [
            'title' => 'Ajuda per a la cerca',
            'text' => 'Funcions, configuració i preguntes freqüents',
        ],
        'privacy' => [
            'title' => 'Privadesa',
            'text' => 'Què desem — i què no',
        ],
    ],
    'reach' => 'Com contactar-nos',
];
