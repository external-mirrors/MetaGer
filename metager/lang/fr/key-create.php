<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Créer une clé',
    'lede' => 'Votre clé est votre compte. Elle porte votre solde de jetons, et elle est tout ce que nous connaissons de vous : ni nom, ni adresse électronique, ni mot de passe. Cela veut dire aussi que, si vous la perdez, le solde qu\'elle porte est perdu.',

    'existing' => [
        'text' => 'Vous aviez déjà une clé MetaGer ? Connectez-vous avec elle plutôt que d\'en créer une nouvelle : une nouvelle clé reçoit son propre solde, distinct, et l\'ancien reste sur l\'ancienne clé.',
        'action' => 'Se connecter avec une clé existante',
    ],

    'offer' => [
        'text' => 'Une pression sur le bouton et vous en avez une. Aucun formulaire, aucun identifiant : MetaGer tire au sort une suite de caractères qui n\'appartient encore à personne.',
        'button' => 'Créer la clé maintenant',
    ],

    'working' => 'Un instant : nous tirons au sort une nouvelle clé pour vous …',

    /**
     * The mark that sits in the corner of every page from here on.
     *
     * Derived from the key and stored nowhere
     * ({@see \App\Authentication\KeyIdenticon}). It is here because a mark you
     * are meant to recognise has to be shown the first time — otherwise it is
     * just a coloured square the second time.
     */
    'identity' => 'C\'est ainsi que vous reconnaîtrez votre compte : à partir de maintenant, cette marque figure en haut à droite de chaque page.',

    'key' => [
        'label' => 'Votre nouvelle clé',
        'hint' => '36 caractères. Ce sont eux qui vous connectent sur tout autre appareil.',
    ],

    'copy' => [
        'action' => 'Copier la clé',
        'done' => 'Copiée',
    ],

    'save' => [
        'heading' => 'Gardez-la quelque part',
        'text' => 'Tant que ce navigateur conserve le cookie, vous restez connecté. S\'il le perd — nouvel appareil, données de navigation effacées —, cette clé est le seul chemin de retour.',

        'qr' => [
            'alt' => 'Code QR menant à votre clé',
            'action' => 'Enregistrer comme image',
            'hint' => 'L\'image que demande le formulaire de connexion. Vous pourrez l\'y téléverser plus tard ou la photographier avec l\'appareil photo.',
        ],

        'url' => [
            'label' => 'Signet',
            'action' => 'Copier l\'URL',
            'hint' => 'Ouvrir cette URL réinstalle la clé ainsi que les réglages de ce navigateur.',
        ],

        'no_cookies' => 'Ce navigateur n\'enregistre aucun cookie pour MetaGer. Sans cookie, vous ne restez pas connecté : l\'URL ci-dessus est alors le moyen de vous connecter avant une recherche. Vous pouvez aussi l\'ajouter comme moteur de recherche dans votre navigateur.',
    ],

    'continue' => 'Suite : créditer la clé',
    'continue_hint' => 'Une nouvelle clé n\'a pas encore de solde. À l\'étape suivante, vous choisissez un lot de jetons.',

    'errors' => [
        'keyserver_unreachable' => 'Aucune clé n\'a pu être créée à l\'instant. Cela vient de nous et non de vous — réessayez dans un moment.',
        'too_many_attempts' => 'Un très grand nombre de clés viennent d\'être créées depuis cette connexion. Attendez quelques minutes, puis rechargez la page.',
        'no_key' => 'La clé s\'est perdue en chemin — cela arrive quand la page est restée longtemps ouverte. En voici une nouvelle.',
    ],
];
