<?php
return [
    'title' => 'Votre adhésion à SUMA-EV',
    'non-de' => 'Malheureusement, nous ne pouvons actuellement accepter que les demandes d\'admission pour les pays germanophones. Vous pouvez nous soutenir en faisant un don à <a href=":donationlink"></a> .',
    'back' => 'Retour à la page d\'accueil',
    'data' => [
        'payment' => [
            'interval' => [
                'quarterly' => "trimestrielle",
                'monthly' => "mensuel",
                'six-monthly' => "Semestrielle",
                'annual' => "annuellement",
            ],
        ],
        'name' => 'Nom',
        'email' => 'Adresse électronique',
        'payment_method' => "Mode de paiement",
        'payment_methods' => [
            'directdebit' => "Prélèvement automatique",
            'card' => "Carte de crédit",
        ],
        'description' => 'Nous avons enregistré les données suivantes pour votre application :',
        'company' => "Nom de l'entreprise",
        'amount' => "Frais d'adhésion",
    ],
    'key' => [
        'later' => 'Le premier rechargement a lieu après le traitement de votre demande.',
        'now' => 'Il est déjà chargé et peut être utilisé immédiatement.',
        'description' => 'Pour utiliser MetaGer, la clé suivante est utilisée et rechargée par nos soins. Si vous étiez déjà connecté, votre clé existante a été utilisée.',
    ],
    'success' => 'Nous vous remercions d\'avoir soumis votre demande d\'adhésion. Nous la traiterons le plus rapidement possible. Vous recevrez ensuite un e-mail contenant des informations complémentaires à l\'adresse indiquée.',
    'application' => [
        'cancel' => [
            'application' => 'Supprimer la demande d\'adhésion',
            'update' => 'Modifications des rejets',
        ],
        'update_hint' => 'Les modifications demandées pour votre adhésion seront bientôt examinées/acceptées. Si vous êtes satisfait de l\'état affiché, vous pouvez quitter cette page. Sinon, vous pouvez apporter d\'autres modifications ou supprimer votre demande de modification en cliquant sur le bouton ci-dessous.',
        'description' => 'Nous vous remercions d\'envisager d\'adhérer à <a href="https://suma-ev.de/en/mitglieder/" target="_blank"></a> à notre association sans but lucratif. Afin de traiter votre demande, nous n\'avons besoin que de quelques informations, que vous pouvez remplir ici.',
        'payment_block' => 'Nous essaierons d\'autoriser le paiement de votre prochaine cotisation afin de valider votre mode de paiement, mais le paiement ne sera exécuté que s\'il est dû dans les deux semaines à venir et sera annulé dans le cas contraire.',
    ],
];
