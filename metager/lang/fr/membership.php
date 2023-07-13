<?php
return [
    'title' => 'Votre adhésion à SUMA-EV',
    'payment' => [
        'method' => [
            'directdebit' => [
                'accountholder' => [
                    'placeholder' => 'John Smith',
                    'label' => 'Titulaire du compte (si différent)',
                ],
                'label' => 'Prélèvement automatique SEPA',
            ],
            'banktransfer' => 'Virement bancaire',
            'title' => '4. Votre mode de paiement',
        ],
        'interval' => [
            'title' => '3. Votre intervalle de paiement',
            'annual' => 'annuel',
            'six-monthly' => 'semestrielle',
            'quarterly' => 'trimestrielle',
            'monthly' => 'mensuel',
        ],
    ],
    'description' => 'Nous vous remercions d\'envisager d\'adhérer à <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'></a> à notre association de soutien à but non lucratif. Afin de traiter votre demande, nous n\'avons besoin que de quelques informations, que vous pouvez remplir ici.',
    'submit' => 'Faire un don',
    'success' => 'Nous vous remercions de nous avoir envoyé votre candidature. Nous la traiterons dans les plus brefs délais. Vous recevrez ensuite un courrier contenant des informations complémentaires à l\'adresse indiquée.',
    'startpage' => 'Retour à la page d\'accueil',
    'contact' => [
        'title' => '1. Vos coordonnées',
        'name' => [
            'label' => 'Votre nom',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label' => 'Votre adresse électronique',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee' => [
        'title' => '2. Votre cotisation',
        'description' => 'Veuillez sélectionner ci-dessous le montant de votre cotisation (mensuelle).',
        'amount' => [
            'custom' => [
                'label' => 'Montant personnalisé',
                'placeholder' => '5,00€',
            ],
        ],
    ],
];
