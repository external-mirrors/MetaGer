<?php
return [
    'headline' => [
        '1' => 'Contact',
        '2' => 'Courriel',
        'pgp' => 'Cryptage',
    ],
    'form' => [
        '1' => 'Formulaire de contact anonyme',
        '2' => 'Vous pouvez nous envoyer un message anonyme en utilisant ce formulaire. Si vous choisissez de ne pas indiquer votre adresse électronique, vous ne recevrez bien entendu aucune réponse.',
        'name' => 'Nom',
        '5' => 'Votre adresse électronique (facultatif)',
        '6' => 'Votre message',
        '7' => 'Sujet',
        '8' => 'Envoyer',
        '9' => 'Jusqu\'à 5 pièces jointes (taille des fichiers < 5 MB)',
        'temperror' => 'Nous rencontrons actuellement des difficultés. Notre formulaire de contact sera bientôt rétabli.',
    ],
    'letter' => [
        '1' => 'Par courrier postal',
        '2' => 'Nous préférons les contacts numériques. Toutefois, si vous estimez qu\'il est nécessaire de nous contacter par voie postale, vous pouvez nous envoyer un courrier à l\'adresse suivante :',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Allemagne",
    ],
    'error' => [
        '1' => 'Nous sommes désolés, mais nous n\'avons malheureusement reçu aucune donnée concernant votre demande de contact. Le message n\'a pas été envoyé.',
        '2' => 'Une erreur s\'est produite lors de la transmission de votre message. Vous pouvez nous contacter directement sous :email',
    ],
    'success' => [
        '1' => 'Votre message a été transmis avec succès. Une première réponse automatique a été envoyée à :email.',
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
