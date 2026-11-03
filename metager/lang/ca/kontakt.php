<?php
return [
    'headline' => [
        '1' => 'Contacte',
        '2' => 'Correu electrònic',
    ],
    'email' => [
        'write' => 'Escriure un correu',
        'pgp' => [
            'summary' => 'Escriure xifrat (PGP)',
            'description' => 'Els nostres correus estan signats criptogràficament. Si voleu verificar la signatura o enviar-nos el correu xifrat, feu servir la clau pública següent. Si voleu rebre una resposta xifrada, adjunteu la vostra clau pública al correu xifrat i signat.',
            'pubkey' => 'Clau pública PGP: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> o a <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Empremta PGP: 5FA5 2398 C382 B498 B14A  B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'letter' => [
        '1' => 'Per correu postal',
        '2' => 'Responem més ràpid per correu electrònic. Per correu postal, ens trobareu a:',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nAlemanya",
    ],
    'removal' => [
        'hint' => 'Es tracta d\'eliminar un resultat de cerca? Llegiu primer <a href=":link">la nostra guia</a>: MetaGer mostra resultats d\'altres cercadors, i per això normalment un resultat només es pot eliminar allà.',
    ],
    'topics' => [
        'heading' => 'Consultes habituals',
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
];
