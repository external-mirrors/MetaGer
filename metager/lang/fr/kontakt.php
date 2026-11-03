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
    'intro' => 'Avant de nous écrire : beaucoup de questions trouvent déjà leur réponse ici — généralement plus vite que par e-mail.',
    'removal' => [
        'heading' => 'Vous souhaitez faire supprimer un résultat de recherche ?',
        'text' => 'Lisez d’abord notre guide. MetaGer affiche les résultats d’autres moteurs de recherche ; un résultat ne peut donc être supprimé que sur le site web et auprès de ces moteurs. Le guide explique où et comment.',
        'link' => 'Lire le guide',
    ],
    'topics' => [
        'heading' => 'Autres demandes fréquentes',
        'key' => [
            'title' => 'Clé MetaGer et jetons',
            'text' => 'Fonctionnement de la clé, recharge et solde',
        ],
        'orders' => [
            'title' => 'Commandes et factures',
            'text' => 'Retrouver une commande, justificatifs et remboursements',
        ],
        'donation' => [
            'title' => 'Faire un don',
            'text' => 'Soutenir MetaGer par un don',
        ],
        'membership' => [
            'title' => 'Devenir membre',
            'text' => 'Rejoindre SUMA-EV, l’association derrière MetaGer',
        ],
        'help' => [
            'title' => 'Aide à la recherche',
            'text' => 'Fonctions, paramètres et questions fréquentes',
        ],
        'privacy' => [
            'title' => 'Vie privée',
            'text' => 'Ce que nous enregistrons — et ce que nous n’enregistrons pas',
        ],
    ],
    'reach' => 'Comment nous joindre',
];
