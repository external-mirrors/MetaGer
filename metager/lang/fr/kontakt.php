<?php
return [
    'lead' => 'Nous répondons plus vite par courriel.',
    'headline' => [
        '1' => 'Contact',
        '2' => 'Courriel',
    ],
    'letter' => [
        '1' => 'Courrier',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Allemagne",
    ],
    'email' => [
        'write' => 'Écrire un courriel',
        'pgp' => [
            'summary' => 'Écrire chiffré (PGP)',
            'signed' => 'Chaque courriel que nous envoyons est signé avec notre clé OpenPGP, qui figure aussi dans son en-tête Autocrypt. Utilisez cette clé pour nous écrire de manière chiffrée :',
            'replies' => [
                'heading' => 'Réponses chiffrées',
                'how' => 'Nous répondons automatiquement de manière chiffrée dès que nous avons votre clé et que nous lui faisons confiance. Pour cela, signez votre courriel. Nous prenons votre clé dans le courriel (en pièce jointe ou dans l\'en-tête Autocrypt) ou sur keys.openpgp.org, et nous l\'utilisons d\'office si',
                'trusted_when' => [
                    'keys.openpgp.org la fournit pour votre adresse (votre adresse y est donc vérifiée), ou',
                    'votre courriel porte une signature DKIM valide de votre domaine d\'expédition.',
                ],
                'otherwise' => 'Sinon, un membre de notre équipe la confirme d\'abord manuellement. Ensuite, tout ce que nous vous envoyons est chiffré, pas seulement les réponses.',
                'tofu' => 'Nous faisons confiance à la première clé reçue ainsi pour votre adresse (Trust on First Use). Nous n\'adoptons pas automatiquement une nouvelle clé pour la même adresse : tant que nous ne l\'avons pas vérifiée, nous continuons à chiffrer avec l\'ancienne.',
                'never_plain' => 'Nous ne répondons jamais en clair à un courriel chiffré. Sans votre clé, nous ne pouvons pas répondre : signez donc toujours vos courriels chiffrés.',
            ],
            'pubkey' => 'PGP Publickey : <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> ou sur <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Empreinte PGP : 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'hint' => 'Il s\'agit de faire supprimer un résultat de recherche ? Lisez d\'abord <a href=":link">notre guide</a> : MetaGer affiche les résultats d\'autres moteurs de recherche, un résultat ne peut donc en général être supprimé que chez eux.',
    ],
    'phone' => [
        'heading' => 'Téléphone',
        'hours' => 'Du lundi au vendredi, de :from à :to (heure allemande)',
        'outside' => 'Pendant ces horaires, vous nous joindrez presque toujours ; en dehors, presque jamais. Écrivez-nous plutôt un e-mail.',
    ],
    'topics' => [
        'heading' => 'Questions fréquentes',
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
];
