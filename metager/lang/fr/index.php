<?php
return [
    'key' => [
        'tooltip' => [
            'nokey' => 'Recherche sans publicité',
            'low' => 'Jeton bientôt épuisé. Rechargez maintenant.',
            'full' => 'Recherche sans publicité activée.',
            'empty' => 'Jeton épuisé. Rechargez maintenant.',
        ],
        'placeholder' => 'Saisissez votre clé MetaGer pour commencer la recherche.',
    ],
    'placeholder' => 'MetaGer : Rechercher et trouver avec protection de la vie privée',
    'searchbutton' => 'Démarrer MetaGer-Search',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Images',
        'nachrichten' => 'Actualités',
        'science' => 'Sciences',
        'produkte' => 'Produits',
        'maps' => 'Cartes',
    ],
    'plugin' => 'Installer MetaGer',
    'plugin-title' => 'Ajouter MetaGer à votre navigateur',
    'adfree' => 'Utiliser MetaGer sans publicité',
    'skip' => [
        'search' => 'Passer à la saisie de la requête de recherche',
        'navigation' => 'Passer à la navigation',
        'fokus' => 'Passer à la sélection de l\'axe de recherche',
    ],
    'lang' => 'quelle langue',
    'searchreset' => 'supprimer la recherche',
    'searchbar-replacement' => [
        'tagline' => 'Open-source. Sans publicité. Anonyme.',
        'message' => "Votre clé est votre accès – pas de compte, pas d'adresse e-mail. Votre crédit et vos réglages y sont attachés.",
        'first_time' => 'Première visite ?',
        'start' => 'Configurer une clé',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Bon retour.',
        'welcome_back_message' => 'Vous vous êtes déjà connecté sur cet appareil. Connectez-vous avec la même clé – votre crédit est toujours là.',
        'welcome_back_button' => 'Se reconnecter',
        'have_key' => 'Se connecter avec ma clé',
        'login' => 'Connexion',
        'key_error' => "La clé introduite n'était pas valide. Veuillez vérifier la saisie.",
        'login_code_error' => "Le code de connexion saisi n'était pas valide. Conseil : les codes de connexion ne sont valables que lorsqu'ils sont visibles sur un autre appareil !",
        'payment_id_error' => "Vous avez introduit un identifiant de paiement qui n'est pas une clé correcte. Votre clé comporte 36 caractères.",
        'new_key' => 'Pas encore de clé ?',
        'extension' => 'Restez connecté et anonyme grâce à notre extension web',
    ],
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'MetaGer : chercher et naviguer sur le web sans être observé',
        'description' => 'MetaGer respecte votre vie privée : sans publicité, sans pistage, sans journalisation. Et vous pouvez désormais visiter n\'importe quel site web de manière anonyme.',
        'advantages' => [
            'ads' => 'Sans publicité',
            'tracking' => 'Sans pistage',
            'logging' => 'Sans journalisation',
            'compromise' => 'Sans compromis',
        ],
        'calltoaction' => 'Comment ça marche',
        'benefits' => [
            'browsing' => [
                'heading' => 'Pas seulement une recherche anonyme – une navigation anonyme aussi',
                'description' => 'Avec votre clé MetaGer, vous pouvez aussi ouvrir n\'importe quel site web dans un navigateur privé qui s\'exécute en toute sécurité sur nos serveurs, et non sur votre appareil. Les sites ne peuvent pas savoir qui vous êtes ni d\'où vous naviguez, et tout est automatiquement effacé à la fin de votre session. Aucune installation, aucune configuration : il suffit d\'ouvrir et de commencer.',
                'fingerprinting' => 'Empreinte numérique',
                'tracking' => 'Pistage',
            ],
            'ads' => [
                'heading' => 'Sans publicité',
                'description' => 'La publicité et la vie privée font rarement bon ménage. C\'est pourquoi il n\'y a aucune publicité chez MetaGer, ce qui nous permet de protéger votre vie privée sans compromis.',
                'ads' => 'Publicité',
                'tracking' => 'Liens de pistage',
            ],
            'logging' => [
                'heading' => 'Sans journalisation',
                'description' => 'Chercher sur Internet laisse habituellement une traînée de données. Nous n\'avons besoin d\'en conserver aucune : notre moteur de recherche est conçu de telle sorte que la lutte contre le spam ne nécessite pas de journaux. Vous ne rencontrerez pas non plus le moindre captcha sur notre site, même en utilisant un VPN.',
                'logging' => 'Journalisation',
            ],
            'compromise' => [
                'heading' => 'Sans compromis',
                'description' => 'Au lieu d\'un compte lié à vos données personnelles, vous recevez simplement une clé générée aléatoirement, sans nom ni adresse e-mail. Choisissez parmi plusieurs <a href=":linkPaymentMethods">moyens de paiement</a>, dont le paiement en espèces, entièrement anonyme. Avec notre <a href=":linkApp">application Android</a> ou notre extension de navigateur, vous pouvez même prouver que vos recherches restent anonymes grâce aux <a href=":linkToken">jetons anonymes</a>.',
                'compromise' => 'Données personnelles',
            ],
            'efficiency' => [
                'heading' => 'Chercher plus efficacement',
                'description' => 'Trouvez plus vite ce que vous cherchez. Lorsque c\'est utile, nous intégrons des liens profonds clairs, des actualités pertinentes et des vidéos directement dans les résultats de recherche. Notre recherche d\'images s\'appuie également sur des sources supplémentaires.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Comment ça marche',
            'steps' => [
                [
                    'heading' => 'Obtenez votre clé gratuite',
                    'description' => 'Votre clé MetaGer est générée automatiquement. Aucune inscription, aucune donnée personnelle nécessaire. C\'est tout ce dont vous avez besoin pour utiliser MetaGer.',
                ],
                [
                    'heading' => 'Activez votre accès',
                    'description' => 'Un <a href=":linkCost">paiement</a> unique ajoute du crédit à votre clé, que nous appelons token. Cela active la recherche sans publicité ni pistage ainsi que la navigation anonyme, y compris toutes les fonctionnalités actuelles et futures de MetaGer. Environ 500 tokens (5 €) suffisent généralement pour près de 2 mois.',
                    'membership' => 'Remarque : les membres de notre association à but non lucratif <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> peuvent utiliser MetaGer sans frais supplémentaires. <a href=":linkMembership" target="_blank">Devenir membre</a>',
                ],
                [
                    'heading' => 'Utilisez MetaGer partout',
                    'description' => 'Utilisez la même clé sur autant d\'appareils que vous le souhaitez, ou partagez-la avec vos amis et votre famille. Ouvrez simplement MetaGer sur n\'importe quel appareil, saisissez votre clé, et vous pouvez chercher – ou naviguer anonymement.',
                ],
            ],
            'start' => 'Commencer',
            'login' => 'J\'ai déjà une clé',
        ],
    ],
];
