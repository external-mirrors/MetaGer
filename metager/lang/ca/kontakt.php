<?php
return [
    'lead' => 'Responem més ràpid per correu electrònic.',
    'headline' => [
        '1' => 'Contacte',
        '2' => 'Correu electrònic',
    ],
    'email' => [
        'write' => 'Escriure un correu',
        'pgp' => [
            'summary' => 'Escriure xifrat (PGP)',
            'signed' => 'Tots els nostres correus van signats amb la nostra clau OpenPGP, que també s\'inclou a la capçalera Autocrypt. Feu servir aquesta clau per escriure\'ns xifrat:',
            'replies' => [
                'heading' => 'Respostes xifrades',
                'how' => 'Responem xifrat automàticament tan bon punt tenim la vostra clau i hi confiem. Per a això, signeu el vostre correu. Agafem la vostra clau del correu (com a adjunt o a la capçalera Autocrypt) o de keys.openpgp.org, i la fem servir pel nostre compte si',
                'trusted_when' => [
                    'keys.openpgp.org la proporciona per a la vostra adreça (és a dir, l\'adreça hi està verificada), o',
                    'el vostre correu porta una signatura DKIM vàlida del vostre domini remitent.',
                ],
                'otherwise' => 'Si no, algú del nostre equip la confirma abans a mà. A partir d\'aleshores xifrem tot el que us enviem, no només les respostes.',
                'tofu' => 'Confiem en la primera clau que rebem així per a la vostra adreça (Trust on First Use). No adoptem automàticament una clau nova per a la mateixa adreça: fins que no l\'hem revisada, continuem xifrant amb l\'anterior.',
                'never_plain' => 'Mai no responem sense xifrar a un correu xifrat. Si no tenim la vostra clau, no podem respondre; per això, signeu sempre els correus xifrats que ens envieu.',
            ],
            'pubkey' => 'Clau pública PGP: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> o a <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Empremta PGP: 5FA5 2398 C382 B498 B14A  B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'letter' => [
        '1' => 'Correu postal',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nAlemanya",
    ],
    'removal' => [
        'hint' => 'Es tracta d\'eliminar un resultat de cerca? Llegiu primer <a href=":link">la nostra guia</a>: MetaGer mostra resultats d\'altres cercadors, i per això normalment un resultat només es pot eliminar allà.',
    ],
    'phone' => [
        'heading' => 'Telèfon',
        'hours' => 'De dilluns a divendres, de :from a :to (hora alemanya)',
        'outside' => 'En aquest horari gairebé sempre us respondrem; fora d’aquest horari, gairebé mai. Llavors és millor que ens escriviu un correu electrònic.',
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
