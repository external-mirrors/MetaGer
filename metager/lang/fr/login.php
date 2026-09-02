<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Se connecter à MetaGer',
    'lede' => 'Votre clé est votre compte. Elle porte votre solde de jetons, et elle est tout ce que nous connaissons de vous : ni nom, ni adresse électronique, ni mot de passe.',

    'key' => [
        'label' => 'Clé ou code de connexion',
        'hint' => '36 caractères. Depuis un appareil déjà connecté, le mot de passe à usage unique à six chiffres de la fenêtre de transfert convient aussi.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Se connecter',
    'or' => 'ou',

    'file' => [
        'button' => 'Choisir le fichier de sauvegarde',
        'hint' => 'Le fichier ou l\'image du code QR que vous avez enregistré lors de la création de votre clé.',
    ],

    'qr' => [
        'button' => 'Scanner le code QR',
        'hint' => 'Avec la caméra de cet appareil, depuis l\'écran d\'un autre par exemple.',
        'no_camera' => 'Pas de caméra disponible.',
        'invalid' => 'Ce code QR ne contient pas de clé.',
        'close' => 'Fermer',
    ],

    'create' => [
        'prompt' => 'Pas encore de clé ?',
        'action' => 'Créer une clé',
    ],

    'errors' => [
        'invalid_key' => 'Ce n\'est pas une clé valide. Une clé compte 36 caractères, un code de connexion six chiffres.',
        'invalid_login_code' => 'Ce code de connexion n\'est plus valable. Il dure quelques secondes et ne sert qu\'à une seule connexion — faites-en afficher un nouveau sur l\'appareil connecté. L\'abrégé affiché à côté de votre solde n\'est pas un code de connexion.',
        // Six caractères qui ne sont pas une clé. Presque toujours l\'abrégé
        // affiché à côté du solde — voir KeyIdenticon.
        'key_mark' => 'Ces six caractères sont l\'abrégé de votre clé — celui qui s\'affiche à côté de votre solde. Il nomme votre compte, il ne l\'ouvre pas. Pour vous connecter, il vous faut la clé complète de 36 caractères ou un code de connexion depuis un appareil déjà connecté.',
        'invalid_key_payment_id' => 'C\'est un numéro de paiement, pas une clé. Votre clé compte 36 caractères et ne commence pas par un Z.',
        'no_input' => 'Saisissez une clé ou choisissez un fichier de sauvegarde.',
        'file_unreadable' => 'Aucune clé n\'a pu être lue dans ce fichier. Il devrait contenir le code QR que vous avez enregistré lors de la création de votre clé.',
        // Der Keyserver hat nicht geantwortet, und zu viele Versuche von einer
        // Adresse. Beides sind Aussagen über uns und nicht über die Eingabe.
        'keyserver_unreachable' => 'Nous n\'avons pas pu vérifier la clé à l\'instant. Cela ne dit rien de votre clé — réessayez dans un moment.',
        'too_many_attempts' => 'Trop de tentatives depuis cette connexion. Attendez quelques minutes, puis réessayez.',
    ],

    'validation' => [
        'hex' => 'Une clé ne contient que les caractères 0–9, a–f et des tirets.',
        'uuid' => 'Ce n\'est pas une clé valide.',
        'login' => 'Ce n\'est ni une clé complète ni un code de connexion.',
    ],

    'empty_key' => [
        'message' => 'Cette clé n\'a pas de solde. Si c\'est normal, connectez-vous ; sinon, un caractère a peut-être été mal saisi.',
        'entered' => 'Clé saisie',
        'revalidate' => 'Vérifier la saisie',
        'confirm' => 'Se connecter quand même',
    ],

    'extension' => [
        'heading' => 'L\'extension MetaGer pour votre navigateur',
        'text' => 'Restez connecté même après avoir effacé les données de votre navigateur — et restez <a href=":tokenlink">anonyme de façon démontrable</a> tout en étant connecté.',
        'install' => 'Installer pour :browser',
        'install_generic' => "Installer l'extension",
    ],

    'no_cookies_notice' => "Votre navigateur ne conserve pas le cookie de connexion. MetaGer ne peut se souvenir de votre clé que tant que l'adresse de cette page la contient encore — ajoutez cette page à vos favoris, ou installez l'extension MetaGer pour rester connecté sans cookies.",
];
