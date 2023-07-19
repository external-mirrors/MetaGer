<?php
return [
    'title' => 'Votre adhésion à SUMA-EV',
    'payment' => [
        'method' => [
            'directdebit' => [
                'accountholder' => [
                    'label' => 'Titulaire du compte (si différent)',
                ],
            ],
            'banktransfer' => 'Virement bancaire',
        ],
        'interval' => [
            'title' => '3. Votre intervalle de paiement',
            'six-monthly' => 'semestrielle',
            'monthly' => 'mensuel',
        ],
    ],
    'submit' => 'Faire un don',
    'startpage' => 'Retour à la page d\'accueil',
    'contact' => [
        'name' => [
            'label' => 'Votre nom',
        ],
        'email' => [
            'label' => 'Votre adresse électronique',
        ],
    ],
    'fee' => [
        'title' => '2. Votre cotisation',
        'amount' => [
            'custom' => [
                'label' => 'Montant personnalisé',
            ],
        ],
    ],
];
